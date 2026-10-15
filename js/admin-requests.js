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

// DATA
const requests = [
    { id: 1, stall: 'Milk Tea House', vendor: 'Jane Lim', contact: 'jane@gmail.com | 0917 123 4567', location: 'Agno', hours: 'Mon-Fri 12:00-17:00', date: '2026-05-01', type: 'Add Stall' },
    { id: 2, stall: 'Burger Joint', vendor: 'Mark Cruz', contact: 'mark@gmail.com | 0918 987 6543', location: 'Green Court', hours: 'Mon-Sun 10:00-22:00', date: '2026-05-02', type: 'Add Stall' },
    { id: 3, stall: 'Old Noodle Bar', vendor: 'Sara Tan', contact: 'sara@gmail.com | 0919 555 0101', location: 'Bloemen', hours: 'Mon-Fri 11:00-19:00', date: '2026-05-03', type: 'Remove Stall' }
];

// STORE REQUESTS PAGE
function initStoreRequestsPage() {
    // STATE
    let query = '';
    let filterMonth = '';   // "YYYY-MM" or ""
    let filterType = '';
    let sortField = 'stall';
    let sortAsc = true;
    let activeRequest = null;

    // ELEMENTS
    const listBody = byId('requestsBody');
    const searchEl = byId('searchInput');
    const filterMonEl = byId('filterMonth');
    const filterTypEl = byId('filterType');
    const sortFieldEl = byId('sortField');
    const sortBtn = byId('sortBtn');

    // RENDER
    function render() {
        const q = query.trim().toLowerCase();

        let list = requests.filter(r => {
            if (filterMonth && !r.date.startsWith(filterMonth)) return false;
            if (filterType && r.type !== filterType) return false;
            if (q &&
                !r.stall.toLowerCase().includes(q) &&
                !r.vendor.toLowerCase().includes(q) &&
                !r.type.toLowerCase().includes(q) &&
                !r.date.includes(q)) return false;
            return true;
        });

        list = [...list].sort((a, b) => {
            const cmp = byText(String(a[sortField]), String(b[sortField]));
            return sortAsc ? cmp : -cmp;
        });

        listBody.replaceChildren();

        if (list.length === 0) {
            listBody.appendChild(buildEmptyRow('No requests found.', 5));
            return;
        }

        list.forEach(req => listBody.appendChild(buildRow(req)));
    }

    function buildRow(req) {
        const tr = document.createElement('tr');

        const actionsTd = elem('td', 'col-actions');
        const isAdd = req.type === 'Add Stall';
        const btn = elem('button',
            'admin-btn ' + (isAdd ? 'primary' : 'danger') + ' small',
            req.type
        );
        btn.type = 'button';
        btn.addEventListener('click', e => {
            e.stopPropagation();
            openRequestModal(req);
        });
        actionsTd.appendChild(btn);

        tr.append(
            elem('td', null, req.stall),
            elem('td', null, req.vendor),
            elem('td', null, req.date),
            elem('td', null, req.type),
            actionsTd
        );

        tr.addEventListener('click', () => openRequestModal(req));
        return tr;
    }

    function buildEmptyRow(text, colSpan) {
        const tr = elem('tr', 'empty-row');
        const td = elem('td', null, text);
        td.colSpan = colSpan;
        tr.appendChild(td);
        return tr;
    }

    // MODAL
    function openRequestModal(req) {
        activeRequest = req;
        byId('requestModalTitle').textContent = req.stall;
        byId('mOwner').textContent = req.vendor;
        byId('mContact').textContent  = req.contact;
        byId('mLocation').textContent = req.location;
        byId('mHours').textContent = req.hours;
        Modal.open('requestModal');
    }

    // ACTIONS
    function removeActiveRequest() {
        const i = requests.indexOf(activeRequest);
        if (i > -1) requests.splice(i, 1);
        Modal.close('requestModal');
        activeRequest = null;
        render();
    }

    byId('approveBtn').addEventListener('click', () => {
        if (!activeRequest) return;
        if (!confirm('Approve "' + activeRequest.stall + '"?')) return;

        // simulate the task (add or delete from the stores directory)
        if (activeRequest.type === 'Add Stall') {
            alert('Approved: "' + activeRequest.stall + '" has been added to the stores directory.');
        } else {
            alert('Approved: "' + activeRequest.stall + '" has been removed from the stores directory.');
        }

        removeActiveRequest();
    });

    byId('denyBtn').addEventListener('click', () => {
        if (!activeRequest) return;
        if (!confirm('Deny "' + activeRequest.stall + '"?')) return;
        removeActiveRequest();
    });

    // TOOLBAR
    searchEl.addEventListener('input', () => {
        query = searchEl.value;
        render();
    });

    filterMonEl.addEventListener('change', () => {
        filterMonth = filterMonEl.value;
        render();
    });

    filterTypEl.addEventListener('change', () => {
        filterType = filterTypEl.value;
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

    render();
}

if (document.body.dataset.page === 'store-requests') initStoreRequestsPage();