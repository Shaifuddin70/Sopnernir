import './bootstrap';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';

import Alpine from 'alpinejs';

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
            allowEmptyOption: true,
            maxOptions: 500,
            searchField: ['text'],
            placeholder: select.getAttribute('placeholder') || 'Search...',
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
        debounceMs: typeof config.debounceMs === 'number' ? config.debounceMs : 350,
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

        buildFetchParams() {
            const params = new URLSearchParams(window.location.search);
            this.resetPageKeys.forEach((k) => params.delete(k));
            const v = this.value.trim();
            if (v) {
                params.set(this.param, v);
            } else {
                params.delete(this.param);
            }
            if (this.ajaxFragment) {
                params.set('ajax_fragment', this.ajaxFragment);
            }

            return params;
        },

        buildBarParams() {
            const params = new URLSearchParams(window.location.search);
            this.resetPageKeys.forEach((k) => params.delete(k));
            const v = this.value.trim();
            if (v) {
                params.set(this.param, v);
            } else {
                params.delete(this.param);
            }
            params.delete('ajax_fragment');

            return params;
        },

        async runFetch() {
            const fetchParams = this.buildFetchParams();
            this.loading = true;
            this.error = false;
            try {
                const res = await fetch(`${this.fetchUrl}?${fetchParams.toString()}`, {
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
                const barParams = this.buildBarParams();
                const q = barParams.toString();
                const next = window.location.pathname + (q ? `?${q}` : '');
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

            const params = new URLSearchParams(linkUrl.search);
            if (this.ajaxFragment) {
                params.set('ajax_fragment', this.ajaxFragment);
            }

            const ajaxUrl = `${fetchBase.pathname}?${params.toString()}`;

            this.loading = true;
            this.error = false;
            try {
                const res = await fetch(ajaxUrl, {
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
                const barParams = new URLSearchParams(linkUrl.search);
                barParams.delete('ajax_fragment');
                const barQs = barParams.toString();
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
            const p = new URLSearchParams(window.location.search);
            p.set(this.param, this.value);
            p.delete(this.resetPageKey);
            p.delete('ajax_fragment');

            return p;
        },

        async apply() {
            const path = new URL(this.fetchUrl, window.location.origin).pathname;

            if (! this.targetId) {
                const bar = this.buildBarParams();
                const q = bar.toString();
                window.location.assign(path + (q ? `?${q}` : ''));

                return;
            }

            const params = new URLSearchParams(window.location.search);
            params.set(this.param, this.value);
            params.delete(this.resetPageKey);
            if (this.ajaxFragment) {
                params.set('ajax_fragment', this.ajaxFragment);
            }

            const ajaxUrl = `${path}?${params.toString()}`;

            this.loading = true;
            this.error = false;
            try {
                const res = await fetch(ajaxUrl, {
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
                const barQs = bar.toString();
                const next = path + (barQs ? `?${barQs}` : '');
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
