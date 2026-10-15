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
const users = [
    { id: 1, name: 'Jonny Craig', email: 'jonny.business@gmail.com', role: 'Vendor' },
    { id: 2, name: 'Maria Clara', email: 'maria@gmail.com', role: 'Vendor' },
    { id: 3, name: 'Lucio Tan', email: 'alex@gmail.com', role: 'Vendor' },
    { id: 4, name: 'Admin User', email: 'admin@greendash.com', role: 'Admin' },
    { id: 4, name: 'James Gomez', email: 'james@usermail.com', role: 'User' },
    { id: 4, name: 'Antoine Lao', email: 'lao@usermail.com', role: 'User' }
];

// MANAGE USERS PAGE
function initManageUsersPage() {
    // STATE
    let query = '';
    let filterRole = '';
    let sortField = 'name';
    let sortAsc = true;

    // ELEMENTS
    const listBody = byId('usersBody');
    const searchEl = byId('searchInput');
    const filterRolEl = byId('filterRole');
    const sortFieldEl = byId('sortField');
    const sortBtn = byId('sortBtn');

    // HELPERS
    function nextId() {
        let max = 0;
        for (const user of users) {
            if (user.id > max) max = user.id;
        }
        return max + 1;
    }

    // RENDER
    function render() {
        const q = query.trim().toLowerCase();

        let list = users.filter(u => {
            if (filterRole && u.role !== filterRole) return false;
            if (q && !u.name.toLowerCase().includes(q) && !u.email.toLowerCase().includes(q)) return false;
            return true;
        });

        list = [...list].sort((a, b) => {
            const cmp = byText(String(a[sortField]), String(b[sortField]));
            return sortAsc ? cmp : -cmp;
        });

        listBody.replaceChildren();

        if (list.length === 0) {
            listBody.appendChild(buildEmptyRow('No users found.', 3));
            return;
        }

        list.forEach(user => listBody.appendChild(buildRow(user)));
    }

    function buildRow(user) {
        const tr = document.createElement('tr');
        tr.append(
            elem('td', null, user.name),
            elem('td', null, user.email),
            elem('td', null, user.role)
        );
        tr.addEventListener('click', () => openUserModal(user));
        return tr;
    }

    function buildEmptyRow(text, colSpan) {
        const tr = elem('tr', 'empty-row');
        const td = elem('td', null, text);
        td.colSpan = colSpan;
        tr.appendChild(td);
        return tr;
    }

    // INFO MODAL
    function openUserModal(user) {
        byId('userModalTitle').textContent = user.name;
        byId('mEmail').textContent = user.email;
        byId('mRole').textContent = user.role;
        byId('mStatus').textContent = user.role;
        Modal.open('userModal');
    }

    // ADD MODAL
    function openAddModal() {
        byId('addUserForm').reset();
        byId('addUserError').textContent = '';
        Modal.open('addUserModal');
    }

    byId('addUserForm').addEventListener('submit', e => {
        e.preventDefault();

        const name = byId('newName').value.trim();
        const email = byId('newEmail').value.trim();
        const role = byId('newRole').value;

        if (!name || !email) {
            byId('addUserError').textContent = 'Please fill in all fields.';
            return;
        }
        if (!/^\S+@\S+\.\S+$/.test(email)) {
            byId('addUserError').textContent = 'Please enter a valid email address.';
            return;
        }
        if (users.some(u => u.email.toLowerCase() === email.toLowerCase())) {
            byId('addUserError').textContent = 'A user with that email already exists.';
            return;
        }

        users.push({ id: nextId(), name, email, role });
        Modal.close('addUserModal');
        render();
    });

    // REMOVE MODAL
    function openRemoveModal() {
        renderRemovePicker();
        Modal.open('removeUserModal');
    }

    function renderRemovePicker() {
        const listEl = byId('removeUserList');
        listEl.replaceChildren();

        if (users.length === 0) {
            listEl.appendChild(elem('p', 'picker-empty', 'No users to remove.'));
            byId('confirmRemoveUserBtn').disabled = true;
            return;
        }

        users.forEach(user => {
            const label = elem('label', 'picker-item');
            const input = elem('input');
            input.type = 'radio';
            input.name = 'removeUserPick';
            input.value = String(user.id);

            label.append(input, elem('span', null, user.name + ' - ' + user.email));
            listEl.appendChild(label);
        });

        byId('confirmRemoveUserBtn').disabled = true;
    }

    byId('removeUserList').addEventListener('change', e => {
        if (!e.target.matches('input[type="radio"]')) return;
        byId('removeUserList').querySelectorAll('.picker-item').forEach(el => el.classList.remove('selected'));
        
        e.target.closest('.picker-item').classList.add('selected');
        byId('confirmRemoveUserBtn').disabled = false;
    });

    byId('confirmRemoveUserBtn').addEventListener('click', () => {
        const checked = byId('removeUserList').querySelector('input:checked');
        if (!checked) return;

        const id = Number(checked.value);
        const i = users.findIndex(u => u.id === id);
        if (i === -1) return;

        if (!confirm('Remove "' + users[i].name + '"?')) return;

        users.splice(i, 1);
        Modal.close('removeUserModal');
        render();
    });

    // TOOLBAR
    searchEl.addEventListener('input', () => {
        query = searchEl.value;
        render();
    });

    filterRolEl.addEventListener('change', () => {
        filterRole = filterRolEl.value;
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

    byId('addUserBtn').addEventListener('click', openAddModal);
    byId('removeModeBtn').addEventListener('click', openRemoveModal);

    byId('saveUserBtn').addEventListener('click', () => {
        alert('TODO: save user');
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

if (document.body.dataset.page === 'manage-users') initManageUsersPage();