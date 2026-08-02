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
                if (typeof data.summary_html === 'string') {
                    const summaryEl = document.getElementById('monthly-payments-summary');
                    if (summaryEl) {
                        summaryEl.innerHTML = data.summary_html;
                    }
                }
                syncMonthlyPaymentsChecklistState(document.getElementById(this.targetId));
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

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function paidStatusBadge(isPaid, root) {
    const paidLabel = root?.dataset.labelPaid || 'Paid';
    const unpaidLabel = root?.dataset.labelUnpaid || 'Unpaid';

    if (isPaid) {
        return `<span class="inline-flex items-center gap-1.5 rounded-full border border-success/25 bg-success-muted px-3 py-1 text-sm font-semibold text-success"><span class="h-1.5 w-1.5 shrink-0 rounded-full bg-success" aria-hidden="true"></span>${paidLabel}</span>`;
    }

    return `<span class="inline-flex items-center gap-1.5 rounded-full border border-warning/25 bg-warning-muted px-3 py-1 text-sm font-semibold text-warning"><span class="h-1.5 w-1.5 shrink-0 rounded-full bg-warning" aria-hidden="true"></span>${unpaidLabel}</span>`;
}

function applyPaymentRowState(row, isPaid, paidAt, root) {
    row.dataset.paid = isPaid ? '1' : '0';
    row.classList.toggle('bg-success-muted/30', isPaid);

    const statusCell = row.querySelector('[data-status-cell]');
    if (statusCell) {
        statusCell.innerHTML = paidStatusBadge(isPaid, root);
    }

    const paidAtCell = row.querySelector('[data-paid-at-cell]');
    if (paidAtCell) {
        paidAtCell.textContent = isPaid && paidAt ? paidAt : '—';
    }

    const toggle = row.querySelector('[data-paid-toggle]');
    if (toggle instanceof HTMLInputElement) {
        toggle.checked = isPaid;
    }

    const label = row.querySelector('[data-toggle-label]');
    if (label) {
        label.textContent = isPaid
            ? (root?.dataset.labelMarkUnpaid || 'Mark unpaid')
            : (root?.dataset.labelMarkPaid || 'Mark paid');
    }
}

function syncMonthlyPaymentsChecklistState(scope = document) {
    const root = scope?.querySelector?.('[data-monthly-payments-checklist]')
        || (scope instanceof HTMLElement && scope.matches('[data-monthly-payments-checklist]') ? scope : null)
        || document.querySelector('[data-monthly-payments-checklist]');

    if (! root) {
        return;
    }

    const toggles = Array.from(root.querySelectorAll('[data-paid-toggle]'));
    const master = root.querySelector('[data-check-all-paid]');
    if (! (master instanceof HTMLInputElement) || toggles.length === 0) {
        return;
    }

    const checkedCount = toggles.filter((el) => el instanceof HTMLInputElement && el.checked).length;
    master.checked = checkedCount === toggles.length;
    master.indeterminate = checkedCount > 0 && checkedCount < toggles.length;
}

function applyMonthlyPaymentsSummaryHtml(html) {
    const summaryEl = document.getElementById('monthly-payments-summary');
    if (summaryEl && typeof html === 'string') {
        summaryEl.innerHTML = html;
    }
}

async function refreshMonthlyPaymentsFragment(root) {
    const indexUrl = root?.dataset.indexUrl;
    if (! indexUrl) {
        return;
    }

    const url = buildMergedRequestUrl(indexUrl, {
        set: {
            month: root.dataset.month || null,
        },
    });

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
    const fragment = document.getElementById('monthly-payments-table-fragment');
    if (fragment && typeof data.html === 'string') {
        fragment.innerHTML = data.html;
    }
    if (typeof data.summary_html === 'string') {
        applyMonthlyPaymentsSummaryHtml(data.summary_html);
    }
    syncMonthlyPaymentsChecklistState(fragment);
}

async function patchMonthlyPayment(url, body) {
    const { data } = await window.axios.patch(url, body, {
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
    });

    return data;
}

document.addEventListener('change', async (event) => {
    const target = event.target;
    if (! (target instanceof HTMLInputElement)) {
        return;
    }

    const root = target.closest('[data-monthly-payments-checklist]');
    if (! root) {
        return;
    }

    if (target.matches('[data-paid-toggle]')) {
        const row = target.closest('[data-payment-row]');
        if (! row) {
            return;
        }

        const previous = row.dataset.paid === '1';
        const nextPaid = target.checked;
        target.disabled = true;
        root.classList.add('opacity-80', 'pointer-events-none');

        try {
            const data = await patchMonthlyPayment(root.dataset.updateUrl, {
                user_id: Number(target.value),
                month: root.dataset.month,
                paid: nextPaid,
            });

            applyPaymentRowState(row, Boolean(data.is_paid), data.paid_at, root);
            if (data.summary_html) {
                applyMonthlyPaymentsSummaryHtml(data.summary_html);
            }
            syncMonthlyPaymentsChecklistState(root);
        } catch (error) {
            console.error(error);
            target.checked = previous;
            applyPaymentRowState(row, previous, row.querySelector('[data-paid-at-cell]')?.textContent || null, root);
        } finally {
            target.disabled = false;
            root.classList.remove('opacity-80', 'pointer-events-none');
        }

        return;
    }

    if (target.matches('[data-check-all-paid]')) {
        const toggles = Array.from(root.querySelectorAll('[data-paid-toggle]'));
        const userIds = toggles
            .filter((el) => el instanceof HTMLInputElement)
            .map((el) => Number(el.value))
            .filter((id) => Number.isFinite(id) && id > 0);

        if (userIds.length === 0) {
            return;
        }

        const paid = target.checked;
        target.disabled = true;
        root.classList.add('opacity-80', 'pointer-events-none');

        try {
            await patchMonthlyPayment(root.dataset.bulkUrl, {
                month: root.dataset.month,
                paid,
                user_ids: userIds,
            });
            await refreshMonthlyPaymentsFragment(root);
        } catch (error) {
            console.error(error);
            syncMonthlyPaymentsChecklistState(root);
        } finally {
            target.disabled = false;
            root.classList.remove('opacity-80', 'pointer-events-none');
        }
    }
});

document.addEventListener('DOMContentLoaded', () => {
    syncMonthlyPaymentsChecklistState(document);
});

/** @type {{ mode: 'single'|'bulk', investmentIds: number[], root: HTMLElement|null }} */
let withdrawProfitState = {
    mode: 'single',
    investmentIds: [],
    root: null,
};

function withdrawRootFrom(el) {
    return el?.closest?.('[data-profit-withdrawals]') || document.querySelector('[data-profit-withdrawals]');
}

function selectedWithdrawIds(root) {
    return Array.from(root.querySelectorAll('[data-withdraw-check]:checked'))
        .map((el) => Number(el.value))
        .filter((id) => Number.isFinite(id) && id > 0);
}

function syncWithdrawSelection(root) {
    if (!root) {
        return;
    }

    const checks = Array.from(root.querySelectorAll('[data-withdraw-check]'));
    const selected = checks.filter((el) => el instanceof HTMLInputElement && el.checked);
    const bar = root.querySelector('[data-withdraw-bulk-bar]');
    const countLabel = root.querySelector('[data-withdraw-selected-count]');
    const checkAll = root.querySelector('[data-withdraw-check-all]');

    if (bar instanceof HTMLElement) {
        bar.classList.toggle('hidden', selected.length === 0);
        bar.classList.toggle('flex', selected.length > 0);
    }

    if (countLabel) {
        const template = root.dataset.labelSelected || 'Selected: :count';
        countLabel.textContent = template.replace(':count', String(selected.length));
    }

    if (checkAll instanceof HTMLInputElement) {
        checkAll.checked = checks.length > 0 && selected.length === checks.length;
        checkAll.indeterminate = selected.length > 0 && selected.length < checks.length;
    }
}

function openWithdrawModal() {
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'withdraw-profit' }));
}

function setWithdrawError(message) {
    const errorEl = document.querySelector('[data-withdraw-error]');
    if (!errorEl) {
        return;
    }
    if (message) {
        errorEl.textContent = message;
        errorEl.classList.remove('hidden');
    } else {
        errorEl.textContent = '';
        errorEl.classList.add('hidden');
    }
}

function renderWithdrawPreview(data) {
    const preview = document.querySelector('[data-withdraw-preview]');
    const submit = document.querySelector('[data-withdraw-submit]');
    if (!preview) {
        return;
    }

    const rows = Array.isArray(data.rows) ? data.rows : [];
    const withdrawable = rows.filter((row) => row.can_withdraw);
    const total = data.total_available || '0.00';

    if (withdrawable.length === 0) {
        preview.innerHTML = `<p class="text-foreground-muted">${withdrawProfitState.root?.dataset.labelNone || 'No withdrawable profit for the selection.'}</p>`;
        if (submit instanceof HTMLButtonElement) {
            submit.disabled = true;
        }
        return;
    }

    const list = withdrawable
        .map((row) => `<li class="flex justify-between gap-3"><span class="truncate">${row.title}</span><span class="tabular-nums font-semibold text-success">${row.available}</span></li>`)
        .join('');

    preview.innerHTML = `
        <div class="space-y-2">
            <div class="flex justify-between gap-3 font-medium">
                <span>Total available</span>
                <span class="tabular-nums text-success">${total}</span>
            </div>
            <ul class="space-y-1 border-t border-line pt-2 text-foreground">${list}</ul>
        </div>
    `;

    if (submit instanceof HTMLButtonElement) {
        submit.disabled = false;
    }
}

async function refreshWithdrawPreview() {
    const root = withdrawProfitState.root;
    const dateInput = document.querySelector('[data-withdraw-through-date]');
    const submit = document.querySelector('[data-withdraw-submit]');

    if (!root || !(dateInput instanceof HTMLInputElement) || withdrawProfitState.investmentIds.length === 0) {
        return;
    }

    setWithdrawError('');
    if (submit instanceof HTMLButtonElement) {
        submit.disabled = true;
    }

    const url = new URL(root.dataset.previewUrl, window.location.origin);
    withdrawProfitState.investmentIds.forEach((id) => url.searchParams.append('investment_ids[]', String(id)));
    url.searchParams.set('through_date', dateInput.value);

    try {
        const res = await fetch(url.toString(), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
        const data = await res.json();
        if (!res.ok) {
            const message = data?.errors?.through_date?.[0]
                || data?.message
                || root.dataset.labelPreviewError
                || 'Could not calculate withdrawable profit.';
            setWithdrawError(message);
            renderWithdrawPreview({ rows: [], total_available: '0.00' });
            return;
        }
        renderWithdrawPreview(data);
    } catch (error) {
        console.error(error);
        setWithdrawError(root.dataset.labelPreviewError || 'Could not calculate withdrawable profit.');
        renderWithdrawPreview({ rows: [], total_available: '0.00' });
    }
}

function beginWithdraw({ mode, investmentIds, root, title }) {
    withdrawProfitState = { mode, investmentIds, root };

    const form = document.querySelector('[data-withdraw-profit-form]');
    const titleEl = document.querySelector('[data-withdraw-modal-title]');
    const subtitleEl = document.querySelector('[data-withdraw-modal-subtitle]');
    const dateInput = document.querySelector('[data-withdraw-through-date]');
    const notes = document.querySelector('[data-withdraw-notes]');
    const bulkDate = root?.querySelector?.('[data-bulk-through-date]');

    if (titleEl) {
        titleEl.textContent = mode === 'bulk' ? 'Withdraw selected profits' : 'Withdraw profit';
    }
    if (subtitleEl) {
        subtitleEl.textContent = mode === 'bulk'
            ? `${investmentIds.length} investment(s)`
            : (title || '');
    }
    if (dateInput instanceof HTMLInputElement) {
        dateInput.value = (bulkDate instanceof HTMLInputElement && bulkDate.value)
            ? bulkDate.value
            : (dateInput.getAttribute('value') || dateInput.value);
    }
    if (notes instanceof HTMLTextAreaElement) {
        notes.value = '';
    }
    if (form instanceof HTMLFormElement) {
        if (mode === 'single' && investmentIds.length === 1) {
            form.action = (root.dataset.storeUrlTemplate || '').replace('__ID__', String(investmentIds[0]));
        } else {
            form.action = root.dataset.bulkUrl || '#';
        }
    }

    setWithdrawError('');
    openWithdrawModal();
    refreshWithdrawPreview();
}

document.addEventListener('change', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) {
        return;
    }

    const root = withdrawRootFrom(target);
    if (!root) {
        return;
    }

    if (target.matches('[data-withdraw-check], [data-withdraw-check-all]')) {
        if (target.matches('[data-withdraw-check-all]') && target instanceof HTMLInputElement) {
            root.querySelectorAll('[data-withdraw-check]').forEach((el) => {
                if (el instanceof HTMLInputElement) {
                    el.checked = target.checked;
                }
            });
        }
        syncWithdrawSelection(root);
    }

    if (target.matches('[data-withdraw-through-date]')) {
        refreshWithdrawPreview();
    }
});

document.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) {
        return;
    }

    const rowOpen = target.closest('[data-row-withdraw-open]');
    if (rowOpen instanceof HTMLElement) {
        const root = withdrawRootFrom(rowOpen);
        if (!root) {
            return;
        }
        const id = Number(rowOpen.dataset.investmentId);
        if (!Number.isFinite(id) || id <= 0) {
            return;
        }
        beginWithdraw({
            mode: 'single',
            investmentIds: [id],
            root,
            title: rowOpen.dataset.investmentTitle || '',
        });
        return;
    }

    const bulkOpen = target.closest('[data-bulk-withdraw-open]');
    if (bulkOpen instanceof HTMLElement) {
        const root = withdrawRootFrom(bulkOpen);
        if (!root) {
            return;
        }
        const ids = selectedWithdrawIds(root);
        if (ids.length === 0) {
            return;
        }
        beginWithdraw({
            mode: 'bulk',
            investmentIds: ids,
            root,
            title: '',
        });
    }
});

document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.matches('[data-withdraw-profit-form]')) {
        return;
    }

    event.preventDefault();
    const root = withdrawProfitState.root || withdrawRootFrom(form);
    if (!root || withdrawProfitState.investmentIds.length === 0) {
        return;
    }

    const dateInput = form.querySelector('[data-withdraw-through-date]');
    const notes = form.querySelector('[data-withdraw-notes]');
    const submit = form.querySelector('[data-withdraw-submit]');
    const throughDate = dateInput instanceof HTMLInputElement ? dateInput.value : '';
    const notesValue = notes instanceof HTMLTextAreaElement ? notes.value : '';

    setWithdrawError('');
    if (submit instanceof HTMLButtonElement) {
        submit.disabled = true;
    }

    const body = {
        through_date: throughDate,
        notes: notesValue || null,
    };

    let url = form.action;
    if (withdrawProfitState.mode === 'bulk') {
        body.investment_ids = withdrawProfitState.investmentIds;
        url = root.dataset.bulkUrl;
    }

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify(body),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            const message = data?.errors?.through_date?.[0]
                || data?.errors?.investment_ids?.[0]
                || data?.message
                || 'Withdrawal failed.';
            setWithdrawError(message);
            if (submit instanceof HTMLButtonElement) {
                submit.disabled = false;
            }
            return;
        }

        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'withdraw-profit' }));
        window.location.reload();
    } catch (error) {
        console.error(error);
        setWithdrawError('Withdrawal failed.');
        if (submit instanceof HTMLButtonElement) {
            submit.disabled = false;
        }
    }
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-profit-withdrawals]').forEach((root) => syncWithdrawSelection(root));
});

window.Alpine = Alpine;

Alpine.start();
