import './bootstrap';
import './dashboard-charts';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';

import Alpine from 'alpinejs';

/**
 * Merge a route URL (which may already include query params) with current location
 * and additional search/pagination parameters.
 */
function buildMergedRequestUrl(baseUrl, options = {}) {
    const {
        windowSearch = window.location.search,
        set = {},
        deleteKeys = [],
        ajaxFragment = null,
        includeAjaxFragment = false,
    } = options;

    const url = new URL(baseUrl, window.location.origin);
    const windowParams = new URLSearchParams(windowSearch);

    windowParams.forEach((value, key) => {
        if (key !== 'ajax_fragment') {
            url.searchParams.set(key, value);
        }
    });

    deleteKeys.forEach((key) => url.searchParams.delete(key));

    Object.entries(set).forEach(([key, value]) => {
        if (value === null || value === undefined || value === '') {
            url.searchParams.delete(key);
        } else {
            url.searchParams.set(key, String(value));
        }
    });

    if (includeAjaxFragment && ajaxFragment) {
        url.searchParams.set('ajax_fragment', ajaxFragment);
    } else {
        url.searchParams.delete('ajax_fragment');
    }

    return url;
}

const SEARCHABLE_SELECT_MARKER = 'searchableSelectReady';

function initSearchableSelects(root = document) {
    const selects = root.querySelectorAll('select');
    selects.forEach((select) => {
        if (!(select instanceof HTMLSelectElement)) {
            return;
        }

        if (select.dataset.searchable !== 'true') {
            return;
        }

        if (select.dataset[SEARCHABLE_SELECT_MARKER] === 'true') {
            return;
        }

        new TomSelect(select, {
            create: false,
            allowEmptyOption: ! select.multiple,
            maxOptions: 500,
            searchField: ['text'],
            placeholder: select.getAttribute('placeholder') || 'Search...',
            plugins: select.multiple ? ['remove_button'] : [],
            sortField: [
                { field: '$score' },
                { field: '$order' },
            ],
        });

        select.dataset[SEARCHABLE_SELECT_MARKER] = 'true';
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initSearchableSelects(document);
});

const searchableSelectObserver = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
            if (!(node instanceof HTMLElement)) {
                return;
            }

            if (node.matches('select')) {
                initSearchableSelects(node.parentElement || document);
                return;
            }

            if (node.querySelector('select')) {
                initSearchableSelects(node);
            }
        });
    });
});

searchableSelectObserver.observe(document.body, {
    childList: true,
    subtree: true,
});

document.addEventListener('alpine:init', () => {
    Alpine.data('liveSearchAjax', (config) => ({
        fetchUrl: String(config.fetchUrl),
        targetId: String(config.targetId),
        param: config.param ? String(config.param) : 'search',
        ajaxFragment: config.ajaxFragment ? String(config.ajaxFragment) : null,
        resetPageKeys: Array.isArray(config.resetPageKeys) ? config.resetPageKeys : [],
        debounceMs: typeof config.debounceMs === 'number' ? config.debounceMs : 250,
        initialValue: config.initialValue ?? '',
        value: '',
        timer: null,
        loading: false,
        error: false,

        init() {
            this.value = this.initialValue ?? '';
        },

        onInput() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.runFetch(), this.debounceMs);
        },

        requestUrl(includeAjaxFragment = true) {
            const value = this.value.trim();

            return buildMergedRequestUrl(this.fetchUrl, {
                deleteKeys: this.resetPageKeys,
                set: {
                    [this.param]: value || null,
                },
                ajaxFragment: this.ajaxFragment,
                includeAjaxFragment,
            });
        },

        async runFetch() {
            const url = this.requestUrl(true);
            this.loading = true;
            this.error = false;
            try {
                const res = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                    credentials: 'same-origin',
                });
                if (! res.ok) {
                    throw new Error(`HTTP ${res.status}`);
                }
                const data = await res.json();
                const el = document.getElementById(this.targetId);
                if (el && typeof data.html === 'string') {
                    el.innerHTML = data.html;
                }
                const barUrl = this.requestUrl(false);
                const next = barUrl.pathname + barUrl.search;
                const cur = window.location.pathname + (window.location.search || '');
                if (next !== cur) {
                    history.replaceState(null, '', next);
                }
            } catch (e) {
                console.error(e);
                this.error = true;
            } finally {
                this.loading = false;
            }
        },

        clear() {
            this.value = '';
            this.runFetch();
        },
    }));

    Alpine.data('ajaxTableRegion', (config) => ({
        fetchUrl: String(config.fetchUrl),
        targetId: String(config.targetId),
        ajaxFragment: config.ajaxFragment != null && config.ajaxFragment !== ''
            ? String(config.ajaxFragment)
            : null,
        loading: false,
        error: false,

        async onNavClick(event) {
            if (event.defaultPrevented) {
                return;
            }
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) {
                return;
            }
            const root = event.target instanceof Element ? event.target : event.target.parentElement;
            if (! root) {
                return;
            }
            const a = root.closest('a[href]');
            if (! a || ! a.getAttribute('href')) {
                return;
            }
            const fragmentRoot = document.getElementById(this.targetId);
            if (! fragmentRoot || ! fragmentRoot.contains(a)) {
                return;
            }
            const nav = a.closest('nav[role="navigation"]');
            if (! nav || ! fragmentRoot.contains(nav)) {
                return;
            }

            const linkUrl = new URL(a.href, window.location.origin);
            const fetchBase = new URL(this.fetchUrl, window.location.origin);
            if (linkUrl.pathname !== fetchBase.pathname) {
                return;
            }

            event.preventDefault();

            const linkParams = new URLSearchParams(linkUrl.search);
            const ajaxUrl = buildMergedRequestUrl(this.fetchUrl, {
                windowSearch: `?${linkParams.toString()}`,
                ajaxFragment: this.ajaxFragment,
                includeAjaxFragment: Boolean(this.ajaxFragment),
            });

            this.loading = true;
            this.error = false;
            try {
                const res = await fetch(ajaxUrl.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                    credentials: 'same-origin',
                });
                if (! res.ok) {
                    throw new Error(`HTTP ${res.status}`);
                }
                const data = await res.json();
                const el = document.getElementById(this.targetId);
                if (el && typeof data.html === 'string') {
                    el.innerHTML = data.html;
                }
                linkParams.delete('ajax_fragment');
                const barQs = linkParams.toString();
                const next = linkUrl.pathname + (barQs ? `?${barQs}` : '');
                const cur = window.location.pathname + (window.location.search || '');
                if (next !== cur) {
                    history.replaceState(null, '', next);
                }
            } catch (e) {
                console.error(e);
                this.error = true;
            } finally {
                this.loading = false;
            }
        },
    }));

    Alpine.data('paginationPerPage', (config) => ({
        fetchUrl: String(config.fetchUrl),
        targetId: String(config.targetId),
        param: String(config.param),
        resetPageKey: String(config.resetPageKey || 'page'),
        ajaxFragment: config.ajaxFragment != null && config.ajaxFragment !== ''
            ? String(config.ajaxFragment)
            : null,
        value: String(config.initialValue ?? '15'),
        loading: false,
        error: false,

        init() {
            this.value = String(config.initialValue ?? '15');
        },

        buildBarParams() {
            return buildMergedRequestUrl(this.fetchUrl, {
                deleteKeys: [this.resetPageKey],
                set: {
                    [this.param]: this.value,
                },
            });
        },

        async apply() {
            const path = new URL(this.fetchUrl, window.location.origin).pathname;

            if (! this.targetId) {
                const bar = this.buildBarParams();
                window.location.assign(bar.pathname + bar.search);

                return;
            }

            const ajaxUrl = buildMergedRequestUrl(this.fetchUrl, {
                deleteKeys: [this.resetPageKey],
                set: {
                    [this.param]: this.value,
                },
                ajaxFragment: this.ajaxFragment,
                includeAjaxFragment: Boolean(this.ajaxFragment),
            });

            this.loading = true;
            this.error = false;
            try {
                const res = await fetch(ajaxUrl.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                    credentials: 'same-origin',
                });
                if (! res.ok) {
                    throw new Error(`HTTP ${res.status}`);
                }
                const data = await res.json();
                const el = document.getElementById(this.targetId);
                if (el && typeof data.html === 'string') {
                    el.innerHTML = data.html;
                }
                const bar = this.buildBarParams();
                const next = bar.pathname + bar.search;
                const cur = window.location.pathname + (window.location.search || '');
                if (next !== cur) {
                    history.replaceState(null, '', next);
                }
            } catch (e) {
                console.error(e);
                this.error = true;
            } finally {
                this.loading = false;
            }
        },
    }));
});

window.Alpine = Alpine;

Alpine.start();
