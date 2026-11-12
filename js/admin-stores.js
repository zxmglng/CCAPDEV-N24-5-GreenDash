// js/admin-stores.js — behaviour for app/view/admin/manage-stores.php

const byId = id => document.getElementById(id);

function elem(tag, className, text) {
    const n = document.createElement(tag);
    if (className) n.className = className;
    if (text !== undefined) n.textContent = text;
    return n;
}

// --- Modal helper -------------------------------------------------------
const Modal = {
    _returnFocus: null,
    open(id) {
        this._returnFocus = document.activeElement;
        byId(id).classList.add('open');
    },
    close(id) {
        byId(id).classList.remove('open');
        const storeError = byId('addStoreError');
        
        if (storeError) storeError.textContent = '';
        if (this._returnFocus && document.contains(this._returnFocus)) {
            this._returnFocus.focus();
        }
    },
    closeAll() {
        document.querySelectorAll('.admin-modal.open')
            .forEach(m => this.close(m.id));
    }
};

// --- Day-hours helpers --------------------------------------------------
function setDayEnabled(prefix, day, enabled) {
    const cb = byId(prefix + '-day-' + day);
    const opens = byId(prefix + '-opens-' + day);
    const closes = byId(prefix + '-closes-' + day);
    if (!cb || !opens || !closes) return;

    cb.checked = enabled;
    opens.disabled = !enabled;
    closes.disabled = !enabled;

    if (!enabled) {
        opens.value = '';
        closes.value = '';
    }
}

function wireDayCheckboxes(prefix) {
    for (let i = 0; i < 7; i++) {
        const cb = byId(prefix + '-day-' + i);
        const opens = byId(prefix + '-opens-' + i);
        const closes = byId(prefix + '-closes-' + i);
        if (!cb || !opens || !closes) continue;

        cb.addEventListener('change', () => {
            opens.disabled = !cb.checked;
            closes.disabled = !cb.checked;
            if (!cb.checked) {
                opens.value = '';
                closes.value = '';
            }
        });
    }
}

function initManageStoresPage() {
    const listBody    = byId('storesBody');
    const searchEl    = byId('searchInput');
    const filterLocEl = byId('filterLocation');
    const filterStaEl = byId('filterStatus');
    const sortFieldEl = byId('sortField');
    const sortBtn     = byId('sortBtn');

    let sortAsc = true;
    let activeStoreId = null;

    // --- Row accessors --------------------------------------------------
    function rowData(tr) {
        const tds = tr.querySelectorAll('td');
        return {
            stall:    tds[0] ? tds[0].textContent.trim() : '',
            vendor:   tds[1] ? tds[1].textContent.trim() : '',
            location: tds[2] ? tds[2].textContent.trim() : '',
            status:   tr.querySelector('.status')?.classList.contains('open')
                        ? 'open' : 'closed'
        };
    }

    // --- Location filter (built from the server-rendered rows) ----------
    function populateLocations() {
        const seen = new Set();
        listBody.querySelectorAll('tr.stall-row').forEach(tr => {
            const loc = rowData(tr).location;
            if (loc) seen.add(loc);
        });

        const prev = filterLocEl.value;
        filterLocEl.replaceChildren();

        const all = elem('option', null, 'All locations');
        all.value = '';
        filterLocEl.appendChild(all);

        [...seen].sort((a, b) => a.localeCompare(b))
            .forEach(loc => {
                const opt = elem('option', null, loc);
                opt.value = loc;
                filterLocEl.appendChild(opt);
            });

        if (seen.has(prev)) filterLocEl.value = prev;
        else filterLocEl.value = '';
    }

    // --- Filter + sort --------------------------------------------------
    function applyFilters() {
        const q      = searchEl.value.trim().toLowerCase();
        const loc    = filterLocEl.value;
        const status = filterStaEl.value;
        const field  = sortFieldEl.value;

        const rows = [...listBody.querySelectorAll('tr.stall-row')];

        rows.forEach(tr => {
            const d = rowData(tr);
            let show = true;
            if (q && !d.stall.toLowerCase().includes(q) && !d.vendor.toLowerCase().includes(q)) show = false;
            if (loc && d.location !== loc) show = false;
            if (status && d.status !== status) show = false;
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

    // --- Row click → details modal --------------------------------------
    listBody.addEventListener('click', e => {
        const tr = e.target.closest('tr.stall-row');
        if (!tr) return;

        activeStoreId = tr.dataset.id;

        const d = rowData(tr);
        byId('storeModalTitle').textContent = tr.dataset.name || d.stall;
        byId('mOwner').textContent          = d.vendor || '—';
        byId('mLocation').textContent       = d.location || '—';
        byId('mHours').textContent          = tr.dataset.hours || '—';
        Modal.open('storeModal');
    });

    // --- Add modal ------------------------------------------------------
    function openAddModal() {
        byId('addStoreForm').reset();
        byId('addStoreError').textContent = '';
        for (let i = 0; i < 7; i++) setDayEnabled('add', i, false);
        Modal.open('addStoreModal');
    }
    byId('addStoreBtn').addEventListener('click', openAddModal);

    // --- Remove modal (picker is server-rendered) -----------------------
    byId('removeModeBtn').addEventListener('click', () => Modal.open('removeStoreModal'));

    byId('removeStoreList').addEventListener('change', e => {
        if (!e.target.matches('input[type="radio"]')) return;
        byId('removeStoreList').querySelectorAll('.picker-item')
            .forEach(el => el.classList.remove('selected'));
        e.target.closest('.picker-item').classList.add('selected');
        byId('confirmRemoveStoreBtn').disabled = false;
    });

    byId('confirmRemoveStoreBtn').addEventListener('click', () => {
        const checked = byId('removeStoreList').querySelector('input:checked');
        if (!checked) return;
        if (!confirm('Remove this stall?')) return;

        byId('deleteStoreId').value = checked.value;
        byId('deleteStoreForm').submit();
    });

    // --- Edit modal -------------------------------------
    byId('editStoreBtn').addEventListener('click', () => {
        if (!activeStoreId) return;

        const tr = listBody.querySelector(`tr[data-id="${activeStoreId}"]`);
        if (!tr) return;

        byId('editStoreId').value = activeStoreId;
        byId('editStall').value = tr.dataset.name;
        byId('editVendor').value = tr.dataset.vendorId || '';
        byId('editLocation').value = tr.querySelectorAll('td')[2].textContent.trim();

        const isOpen = tr.querySelector('.status').classList.contains('open') ? '1' : '0';
        byId('editStatus').value = isOpen;

        for (let i = 0; i < 7; i++) setDayEnabled('edit', i, false);
        
        const hours = JSON.parse(tr.dataset.hoursJson || '[]');
        hours.forEach(h => {
            const day = parseInt(h.day_of_week, 10);
            if (isNaN(day) || day < 0 || day > 6) return;
            setDayEnabled('edit', day, true);
            byId('edit-opens-' + day).value = h.opens_at.substring(0, 5);
            byId('edit-closes-' + day).value = h.closes_at.substring(0, 5);
        });

        Modal.close('storeModal');
        Modal.open('editStoreModal');
    });

    // --- Toolbar --------------------------------------------------------
    searchEl.addEventListener('input', applyFilters);
    filterLocEl.addEventListener('change', applyFilters);
    filterStaEl.addEventListener('change', applyFilters);
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

    // --- Wire day checkbox toggles --------------------------------------
    wireDayCheckboxes('add');
    wireDayCheckboxes('edit');

    // --- Boot -----------------------------------------------------------
    populateLocations();
    applyFilters();

    if (document.body.dataset.flash === 'add') {
        Modal.open('addStoreModal');
    }
}

if (document.body.dataset.page === 'manage-stores') initManageStoresPage();