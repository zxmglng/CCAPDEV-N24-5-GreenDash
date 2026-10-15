// HELPERS
const byId = id => document.getElementById(id);

function elem(tag, className, text) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
}

const byText = (a, b) => a.localeCompare(b, undefined, { sensitivity: 'base' });

// MODAL
const Modal = {
    _returnFocus: null,
    open(id) {
        this._returnFocus = document.activeElement;
        byId(id).classList.add('open');
    },
    close(id) {
        byId(id).classList.remove('open');
        if (this._returnFocus && document.contains(this._returnFocus)) this._returnFocus.focus();
    },
    closeAll() {
        document.querySelectorAll('.admin-modal.open').forEach(m => this.close(m.id));
    }
};

// DATA (temporary — replaced by a backend later)
const stores = [
    { id: 1, stall: 'Starbucks', vendor: 'Jonny Craig', location: 'Green Court', hours: 'Mon-Fri 9:00-23:00', status: 'open' },
    { id: 2, stall: 'Potato Corner', vendor: 'Maria Reyes', location: 'Bloemen', hours: 'Mon-Sun 9:00-20:00', status: 'closed' },
    { id: 3, stall: 'Stall 2', vendor: 'Alex Tan', location: 'Agno', hours: 'Mon-Fri 12:00-17:00', status: 'open' }
];

// MANAGE STORES PAGE
function initManageStoresPage() {
    // STATE
    let query = '';
    let filterLocation = '';
    let filterStatus = '';
    let sortField = 'stall';
    let sortAsc = true;

    // ELEMENTS
    const listBody = byId('storesBody');
    const searchEl = byId('searchInput');
    const filterLocEl = byId('filterLocation');
    const filterStaEl = byId('filterStatus');
    const sortFieldEl = byId('sortField');
    const sortBtn = byId('sortBtn');

    // HELPERS
    function nextId() {
        let max = 0;
        for (const store of stores) {
            if (store.id > max) max = store.id;
        }
        return max + 1;
    }

    // RENDER
    function render() {
        const q = query.trim().toLowerCase();

        let list = stores.filter(s => {
            if (filterLocation && s.location !== filterLocation) return false;
            if (filterStatus && s.status !== filterStatus) return false;
            if (q && !s.stall.toLowerCase().includes(q) && !s.vendor.toLowerCase().includes(q)) return false;
            return true;
        });

        list = [...list].sort((a, b) => {
            const cmp = byText(String(a[sortField]), String(b[sortField]));
            return sortAsc ? cmp : -cmp;
        });

        listBody.replaceChildren();

        if (list.length === 0) {
            listBody.appendChild(buildEmptyRow('No stalls found.', 5));
            return;
        }

        list.forEach(store => listBody.appendChild(buildRow(store)));
    }

    function buildRow(store) {
        const tr = document.createElement('tr');
        tr.append(
            elem('td', null, store.stall),
            elem('td', null, store.vendor),
            elem('td', null, store.location),
            elem('td', null, store.hours),
            buildStatusCell(store.status)
        );
        tr.addEventListener('click', () => openStoreModal(store));
        return tr;
    }

    function buildStatusCell(status) {
        const td = document.createElement('td');
        td.appendChild(elem('span', 'status ' + status, status.toUpperCase()));
        return td;
    }

    function buildEmptyRow(text, colSpan) {
        const tr = elem('tr', 'empty-row');
        const td = elem('td', null, text);
        td.colSpan = colSpan;
        tr.appendChild(td);
        return tr;
    }

    // LOCATION FILTER (rebuilt whenever stores change)
    function renderLocationOptions() {
        const prev = filterLocation;
        const locations = [...new Set(stores.map(s => s.location))].sort(byText);

        filterLocEl.replaceChildren();
        const all = elem('option', null, 'All locations');
        all.value = '';
        filterLocEl.appendChild(all);

        locations.forEach(loc => {
            const opt = elem('option', null, loc);
            opt.value = loc;
            filterLocEl.appendChild(opt);
        });

        if (locations.includes(prev)) {
            filterLocEl.value = prev;
            filterLocation = prev;
        } else {
            filterLocEl.value = '';
            filterLocation = '';
        }
    }

    // INFO MODAL
    function openStoreModal(store) {
        byId('storeModalTitle').textContent = store.stall;
        byId('mOwner').textContent = store.vendor;
        byId('mLocation').textContent = store.location;
        byId('mHours').textContent = store.hours;
        Modal.open('storeModal');
    }

    // ADD MODAL
    function openAddModal() {
        byId('addStoreForm').reset();
        byId('addStoreError').textContent = '';
        Modal.open('addStoreModal');
    }

    byId('addStoreForm').addEventListener('submit', e => {
        e.preventDefault();

        const stall = byId('newStall').value.trim();
        const vendor = byId('newVendor').value.trim();
        const location = byId('newLocation').value.trim();
        const hours = byId('newHours').value.trim();
        const status = byId('newStatus').value;

        if (!stall || !vendor || !location || !hours) {
            byId('addStoreError').textContent = 'Please fill in all fields.';
            return;
        }

        stores.push({ id: nextId(), stall, vendor, location, hours, status });
        Modal.close('addStoreModal');
        renderLocationOptions();
        render();
    });

    // REMOVE MODAL
    function openRemoveModal() {
        renderRemovePicker();
        Modal.open('removeStoreModal');
    }

    function renderRemovePicker() {
        const listEl = byId('removeStoreList');
        listEl.replaceChildren();

        if (stores.length === 0) {
            listEl.appendChild(elem('p', 'picker-empty', 'No stalls to remove.'));
            byId('confirmRemoveStoreBtn').disabled = true;
            return;
        }

        stores.forEach(store => {
            const label = elem('label', 'picker-item');
            const input = elem('input');
            input.type = 'radio';
            input.name = 'removeStorePick';
            input.value = String(store.id);

            label.append(input, elem('span', null, store.stall + ' - ' + store.location));
            listEl.appendChild(label);
        });

        byId('confirmRemoveStoreBtn').disabled = true;
    }

    byId('removeStoreList').addEventListener('change', e => {
        if (!e.target.matches('input[type="radio"]')) return;
        byId('removeStoreList').querySelectorAll('.picker-item').forEach(el => el.classList.remove('selected'));
        
        e.target.closest('.picker-item').classList.add('selected');
        byId('confirmRemoveStoreBtn').disabled = false;
    });

    byId('confirmRemoveStoreBtn').addEventListener('click', () => {
        const checked = byId('removeStoreList').querySelector('input:checked');
        if (!checked) return;

        const id = Number(checked.value);
        const i = stores.findIndex(s => s.id === id);
        if (i === -1) return;

        if (!confirm('Remove "' + stores[i].stall + '"?')) return;

        stores.splice(i, 1);
        Modal.close('removeStoreModal');
        renderLocationOptions();
        render();
    });

    // TOOLBAR
    searchEl.addEventListener('input', () => {
        query = searchEl.value;
        render();
    });

    filterLocEl.addEventListener('change', () => {
        filterLocation = filterLocEl.value;
        render();
    });

    filterStaEl.addEventListener('change', () => {
        filterStatus = filterStaEl.value;
        render();
    });

    sortFieldEl.addEventListener('change', () => {
        sortField = sortFieldEl.value;
        render();
    });

    sortBtn.addEventListener('click', () => {
        sortAsc = !sortAsc;
        sortBtn.replaceChildren(
            elem('i', 'bi ' + (sortAsc ? 'bi-sort-down-alt' : 'bi-sort-up-alt'))
        );
        render();
    });

    byId('addStoreBtn').addEventListener('click', openAddModal);
    byId('removeModeBtn').addEventListener('click', openRemoveModal);

    byId('editStoreBtn').addEventListener('click', () => {
        alert('TODO: open Edit Stall form');
    });

    // MODAL CLOSE WIRING
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

    renderLocationOptions();
    render();
}

if (document.body.dataset.page === 'manage-stores') initManageStoresPage();