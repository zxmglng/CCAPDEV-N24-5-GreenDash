// js/admin-users.js — behaviour for app/view/admin/manage-users.php

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

function initManageUsersPage() {
    const listBody    = byId('usersBody');
    const searchEl    = byId('searchInput');
    const filterRolEl = byId('filterRole');
    const sortFieldEl = byId('sortField');
    const sortBtn     = byId('sortBtn');

    let sortAsc    = true;
    let activeUser = null;   // { id, name, email, role }

    // --- Row accessors --------------------------------------------------
    function rowData(tr) {
        const tds = tr.querySelectorAll('td');
        return {
            name:  tds[0] ? tds[0].textContent.trim() : '',
            email: tds[1] ? tds[1].textContent.trim() : '',
            role:  tds[2] ? tds[2].textContent.trim() : ''
        };
    }

    // --- Filter + sort --------------------------------------------------
    function applyFilters() {
        const q     = searchEl.value.trim().toLowerCase();
        const role  = filterRolEl.value;    // "" | "admin" | "vendor" | "user"
        const field = sortFieldEl.value;    // "name" | "email" | "role"

        const rows = [...listBody.querySelectorAll('tr.user-row')];

        rows.forEach(tr => {
            const d = rowData(tr);
            let show = true;
            if (q && !d.name.toLowerCase().includes(q) && !d.email.toLowerCase().includes(q)) show = false;
            if (role && d.role.toLowerCase() !== role) show = false;
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
        const tr = e.target.closest('tr.user-row');
        if (!tr) return;

        activeUser = {
            id:    tr.dataset.id,
            name:  tr.dataset.name,
            email: tr.dataset.email,
            role:  tr.dataset.role     // lowercase: "admin" | "vendor" | "user"
        };

        byId('userModalTitle').textContent = activeUser.name;
        byId('mEmail').textContent         = activeUser.email;
        byId('mRole').textContent          = activeUser.role.charAt(0).toUpperCase() + activeUser.role.slice(1);
        byId('mStatus').textContent        = 'Active';

        Modal.open('userModal');
    });

    // --- Edit from details modal ----------------------------------------
    byId('editUserBtn').addEventListener('click', () => {
        if (!activeUser) return;

        const parts = activeUser.name.split(' ');
        const first = parts.shift() || '';
        const last  = parts.join(' ');

        byId('editId').value        = activeUser.id;
        byId('editFirstName').value = first;
        byId('editLastName').value  = last;
        byId('editEmail').value     = activeUser.email;
        // "user" in data-role maps back to the "student" enum value
        byId('editRole').value      = activeUser.role === 'user' ? 'student' : activeUser.role;

        Modal.close('userModal');
        Modal.open('editUserModal');
    });

    // --- Add modal ------------------------------------------------------
    byId('addUserBtn').addEventListener('click', () => {
        byId('addUserForm').reset();
        byId('addUserError').textContent = '';
        Modal.open('addUserModal');
    });

    // --- Remove modal (picker is server-rendered) -----------------------
    byId('removeModeBtn').addEventListener('click', () => Modal.open('removeUserModal'));

    byId('removeUserList').addEventListener('change', e => {
        if (!e.target.matches('input[type="radio"]')) return;
        byId('removeUserList').querySelectorAll('.picker-item')
            .forEach(el => el.classList.remove('selected'));
        e.target.closest('.picker-item').classList.add('selected');
        byId('confirmRemoveUserBtn').disabled = false;
    });

    byId('confirmRemoveUserBtn').addEventListener('click', () => {
        const checked = byId('removeUserList').querySelector('input:checked');
        if (!checked) return;
        if (!confirm('Remove this user?')) return;

        byId('deleteId').value = checked.value;
        byId('deleteForm').submit();
    });

    // --- Toolbar --------------------------------------------------------
    searchEl.addEventListener('input', applyFilters);
    filterRolEl.addEventListener('change', applyFilters);
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
    if (document.body.dataset.flash === 'add') Modal.open('addUserModal');
}

if (document.body.dataset.page === 'manage-users') initManageUsersPage();