// js/admin-requests.js — behaviour for app/view/admin/store-requests.php

const byId = id => document.getElementById(id);

function elem(tag, className, text) {
    const n = document.createElement(tag);
    if (className) n.className = className;
    if (text !== undefined) n.textContent = text;
    return n;
}

const Modal = {
    _returnFocus: null,
    open(id) {
        this._returnFocus = document.activeElement;
        byId(id).classList.add('open');
    },
    close(id) {
        byId(id).classList.remove('open');
        if (this._returnFocus && document.contains(this._returnFocus)) {
            this._returnFocus.focus();
        }
    },
    closeAll() {
        document.querySelectorAll('.admin-modal.open')
            .forEach(m => this.close(m.id));
    }
};

function initStoreRequestsPage() {
    const listBody = byId('requestsBody');
    const searchEl = byId('searchInput');
    const filterMonEl = byId('filterMonth');
    const filterTypEl = byId('filterType');
    const sortFieldEl = byId('sortField');
    const sortBtn = byId('sortBtn');

    let sortAsc  = true;
    let activeId = null;

    // --- Row accessors --------------------------------------------------
    function rowData(tr) {
        const tds = tr.querySelectorAll('td');
        return {
            stall:  tds[0] ? tds[0].textContent.trim() : '',
            vendor: tds[1] ? tds[1].textContent.trim() : '',
            date:   tds[2] ? tds[2].textContent.trim() : '',
            type:   tds[3] ? tds[3].textContent.trim() : ''
        };
    }

    // --- Filter + sort --------------------------------------------------
    function applyFilters() {
        const q = searchEl.value.trim().toLowerCase();
        const month = filterMonEl.value;    // "YYYY-MM" or ""
        const type = filterTypEl.value;    // "Add Stall" | "Remove Stall" | ""
        const field = sortFieldEl.value;    // "stall" | "vendor" | "date"

        const rows = [...listBody.querySelectorAll('tr.request-row')];

        rows.forEach(tr => {
            const d = rowData(tr);
            let show = true;

            if (q && !d.stall.toLowerCase().includes(q) && !d.vendor.toLowerCase().includes(q)) {
                show = false;
            }
            if (type && d.type !== type) {
                show = false;
            }
            // data-date is "YYYY-MM-DD"
            if (month) {
                const raw = tr.dataset.date || '';
                if (!raw.startsWith(month)) show = false;
            }

            tr.style.display = show ? '' : 'none';
        });

        rows.sort((a, b) => {
            const ca = rowData(a)[field] || '';
            const cb = rowData(b)[field] || '';
            return sortAsc
                ? ca.localeCompare(cb, undefined, { sensitivity: 'base' })
                : cb.localeCompare(ca, undefined, { sensitivity: 'base' });
        });
        rows.forEach(tr => listBody.appendChild(tr));
    }

    // --- Row click → request modal --------------------------------------
    listBody.addEventListener('click', e => {
        const tr = e.target.closest('tr.request-row');
        if (!tr) return;

        activeId = tr.dataset.id;
        byId('requestModalTitle').textContent = tr.dataset.stall || '';
        byId('mOwner').textContent = tr.dataset.vendorName || '—';
        byId('mContact').textContent = tr.dataset.vendorEmail || '—';
        byId('mLocation').textContent = tr.dataset.location || '—';
        byId('mHours').textContent = tr.dataset.hours || '—';

        Modal.open('requestModal');
    });

    // --- Approve / Deny → submit hidden form ----------------------------
    function submitAction(action) {
        if (!activeId) return;
        byId('actionRequestId').value = activeId;
        byId('actionAction').value    = action;
        byId('actionForm').submit();
    }

    byId('approveBtn').addEventListener('click', () => {
        if (confirm('Approve this request?')) submitAction('approve');
    });
    byId('denyBtn').addEventListener('click', () => {
        if (confirm('Deny this request?')) submitAction('deny');
    });

    // --- Toolbar --------------------------------------------------------
    searchEl.addEventListener('input', applyFilters);
    filterMonEl.addEventListener('change', applyFilters);
    filterTypEl.addEventListener('change', applyFilters);
    sortFieldEl.addEventListener('change', applyFilters);

    sortBtn.addEventListener('click', () => {
        sortAsc = !sortAsc;
        sortBtn.replaceChildren(
            elem('i', 'bi ' + (sortAsc ? 'bi-sort-down-alt' : 'bi-sort-up-alt'))
        );
        applyFilters();
    });

    // --- Modal close wiring ---------------------------------------------
    document.querySelectorAll('[data-close]').forEach(btn =>
        btn.addEventListener('click', () => Modal.closeAll())
    );
    document.querySelectorAll('.admin-modal').forEach(m =>
        m.addEventListener('click', e => {
            if (e.target === e.currentTarget) Modal.closeAll();
        })
    );
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') Modal.closeAll();
    });

    // --- Boot -----------------------------------------------------------
    applyFilters();
}

if (document.body.dataset.page === 'store-requests') initStoreRequestsPage();