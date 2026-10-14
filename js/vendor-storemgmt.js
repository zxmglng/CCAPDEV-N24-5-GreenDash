// HELPERS
const byId = id => document.getElementById(id);

// create an element; text is set with textContent so user input is never parsed as HTML
function elem(tag, className, text) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
}

const peso = n => '\u20B1' + Number(n).toLocaleString('en-PH', { maximumFractionDigits: 2 });

const byText = (a, b) => a.localeCompare(b, undefined, { sensitivity: 'base' });

// CLASSES 
// used as temporary values for display only
class Category {
    constructor(id, name) {
        this.id = id;
        this.name = name;
    }
}

class MenuItem {
    constructor({ id, name, price, description = '', categoryIds = [], photo = null, available = true }) {
        this.id = id;
        this.name = name;
        this.price = price;
        this.description = description;
        this.categoryIds = categoryIds;
        this.photo = photo;           // image URL or data: URL, null = no photo
        this.available = available;
    }
}

class Store {
    constructor({ key, name, location, address, hours, logo, open }) {
        this.key = key;
        this.name = name;
        this.location = location;
        this.address = address;
        this.hours = hours;
        this.logo = logo;
        this.open = open;
        this.categories = [];
        this.items = [];
        this._nextId = 1;
    }

    newId() { return this._nextId++; }

    addCategory(name) {
        const cat = new Category(this.newId(), name);
        this.categories.push(cat);
        return cat;
    }

    addItem(fields) {
        const item = new MenuItem({ id: this.newId(), ...fields });
        this.items.push(item);
        return item;
    }

    categoryNameTaken(name, exceptId = null) {
        return this.categories.some(c => c.id !== exceptId && c.name.toLowerCase() === name.toLowerCase());
    }

    itemsIn(categoryId) {
        return this.items.filter(i => i.categoryIds.includes(categoryId));
    }

    // items only in this category are also deleted
    deleteCategory(id) {
        this.categories = this.categories.filter(c => c.id !== id);
        this.items.forEach(i => { i.categoryIds = i.categoryIds.filter(cid => cid !== id); });
        this.items = this.items.filter(i => i.categoryIds.length > 0);
    }

    deleteItem(id) {
        this.items = this.items.filter(i => i.id !== id);
    }
}

function buildStores() {
    // STARBUCKS
    const sbux = new Store({
        key: 'starbucks', name: 'Starbucks', location: 'Green Court',
        address: 'Green Court (Fidel A. Reyes Street)', hours: '9:00 AM - 11:00 PM',
        logo: '../../assets/stalls/starbucks.png', open: true
    });
    const popular = sbux.addCategory('Popular');
    const drinks  = sbux.addCategory('Featured Drinks');
    const food    = sbux.addCategory('Featured Food');
    sbux.addItem({ name: 'Java Chip Frappuccino', price: 230, categoryIds: [popular.id, drinks.id],
        photo: '../../assets/starbucksMenu/javachip.png',
        description: 'Mocha sauce and Frappuccino chips blended with coffee and milk, finished with whipped cream.' });
    sbux.addItem({ name: 'Caramel Macchiato', price: 215, categoryIds: [popular.id, drinks.id],
        description: 'Freshly steamed milk with vanilla-flavored syrup, marked with espresso and caramel drizzle.' });
    sbux.addItem({ name: 'Pure Matcha Latte', price: 225, categoryIds: [popular.id, drinks.id],
        description: 'Smooth matcha green tea blended with steamed milk.' });
    sbux.addItem({ name: 'Butter Croissant', price: 120, categoryIds: [food.id],
        description: 'Flaky, buttery croissant, served warm.' });

    // POTATO CORNER
    const potcor = new Store({
        key: 'potatocorner', name: 'Potato Corner', location: 'Bloemen',
        address: 'Bloemen', hours: '9:00 AM - 8:00 PM',
        logo: '../../assets/stalls/potcor.png', open: false
    });
    const pPopular = potcor.addCategory('Popular');
    const fries    = potcor.addCategory('Fries');
    potcor.addItem({ name: 'Cheese Fries', price: 75, categoryIds: [pPopular.id, fries.id],
        description: 'Crispy fries dusted with cheese powder.' });
    potcor.addItem({ name: 'BBQ Fries', price: 95, categoryIds: [fries.id],
        description: 'Crispy fries with a smoky barbecue flavor.' });
    potcor.addItem({ name: 'Sour Cream Fries', price: 55, categoryIds: [pPopular.id, fries.id],
        description: 'Crispy fries with tangy sour cream seasoning.' });

    return { [sbux.key]: sbux, [potcor.key]: potcor };
}

const STORES = buildStores();

// MANAGE STORE PAGE
const Modal = {
    _returnFocus: null,
    open(id) {
        this._returnFocus = document.activeElement;
        const modal = byId(id);
        modal.classList.add('open');
        const first = modal.querySelector('input:not([type=file]):not([type=checkbox]), textarea');
        if (first) first.focus();
    },
    close(id) {
        byId(id).classList.remove('open');
        if (this._returnFocus && document.contains(this._returnFocus)) this._returnFocus.focus();
    },
    closeAll() {
        document.querySelectorAll('.vmodal.open').forEach(m => this.close(m.id));
    }
};

function initManageStorePage() {
    const params = new URLSearchParams(window.location.search);
    const store = STORES[params.get('store')] || STORES.starbucks;

    // STATE
    let selectedCategoryId = store.categories.length ? store.categories[0].id : null;
    let categorySortAsc = true;
    const ITEM_SORTS = [
        { key: 'name-asc',   label: 'name (A to Z)',      icon: 'bi-sort-alpha-down',   fn: (a, b) => byText(a.name, b.name) },
        { key: 'name-desc',  label: 'name (Z to A)',      icon: 'bi-sort-alpha-up',     fn: (a, b) => byText(b.name, a.name) },
        { key: 'price-asc',  label: 'price (low to high)', icon: 'bi-sort-numeric-down', fn: (a, b) => a.price - b.price },
        { key: 'price-desc', label: 'price (high to low)', icon: 'bi-sort-numeric-up',   fn: (a, b) => b.price - a.price }
    ];
    let itemSortIndex = 0;

    // ELEMENTS
    const statusPill = byId('storeStatus');
    const toggleOpenBtn = byId('toggleOpenBtn');
    const categoryList = byId('categoryList');
    const categorySearch = byId('categorySearch');
    const categorySortBtn = byId('categorySort');
    const itemList = byId('itemList');
    const itemSearch = byId('itemSearch');
    const itemSortBtn = byId('itemSort');
    const itemsNote = byId('itemsNote');
    const addItemBtn = byId('addItemBtn');

    // HEADER
    document.title = 'GreenDash - ' + store.name + ' (' + store.location + ')';
    byId('storeLogo').src = store.logo;
    byId('storeLogo').alt = store.name + ' logo';
    byId('storeName').textContent = store.name;
    byId('storeAddress').textContent = store.address;
    byId('storeHours').textContent = store.hours;

    function renderStatus() {
        statusPill.textContent = store.open ? 'OPEN' : 'CLOSED';
        statusPill.className = 'status ' + (store.open ? 'open' : 'closed');
        toggleOpenBtn.textContent = store.open ? 'Mark as closed' : 'Mark as open';
        toggleOpenBtn.setAttribute('aria-pressed', String(!store.open));
    }
    toggleOpenBtn.addEventListener('click', () => {
        store.open = !store.open;
        renderStatus();
    });

    // CATEGORIES
    function renderCategories() {
        const q = categorySearch.value.trim().toLowerCase();
        let list = store.categories.filter(c => c.name.toLowerCase().includes(q));
        list = [...list].sort((a, b) => categorySortAsc ? byText(a.name, b.name) : byText(b.name, a.name));

        categorySortBtn.innerHTML = '';
        categorySortBtn.appendChild(elem('i', 'bi ' + (categorySortAsc ? 'bi-sort-alpha-down' : 'bi-sort-alpha-up')));
        categorySortBtn.title = 'Sorted by name (' + (categorySortAsc ? 'A to Z' : 'Z to A') + ')';

        categoryList.replaceChildren();

        if (list.length === 0) {
            categoryList.appendChild(elem('li', 'empty-note',
                store.categories.length === 0 ? 'No categories yet. Add one to get started.' : 'No categories match your search.'));
            return;
        }

        list.forEach(cat => {
            const selected = cat.id === selectedCategoryId;
            const li = elem('li', 'list-row' + (selected ? ' selected' : ''));

            const main = elem('button', 'row-main');
            main.type = 'button';
            main.setAttribute('aria-pressed', String(selected));
            main.appendChild(elem('span', 'row-name', cat.name));
            main.addEventListener('click', () => {
                selectedCategoryId = cat.id;
                renderCategories();
                renderItems();
            });

            const edit = elem('button', 'row-edit');
            edit.type = 'button';
            edit.setAttribute('aria-label', 'Edit category ' + cat.name);
            edit.appendChild(elem('i', 'bi bi-pencil'));
            edit.addEventListener('click', () => openCategoryModal(cat));

            li.append(main, edit);
            categoryList.appendChild(li);
        });
    }

    categorySearch.addEventListener('input', renderCategories);
    categorySortBtn.addEventListener('click', () => {
        categorySortAsc = !categorySortAsc;
        renderCategories();
    });

    // ITEMS
    function renderItems() {
        const sort = ITEM_SORTS[itemSortIndex];
        const cat = store.categories.find(c => c.id === selectedCategoryId);

        itemSortBtn.innerHTML = '';
        itemSortBtn.appendChild(elem('i', 'bi ' + sort.icon));
        itemSortBtn.title = 'Sorted by ' + sort.label;
        addItemBtn.disabled = !cat;
        itemList.replaceChildren();

        if (!cat) {
            itemsNote.textContent = 'Add a category first, then you can add items to it.';
            return;
        }
        itemsNote.textContent = 'Showing items under ' + cat.name + ' \u2022 sorted by ' + sort.label;

        const q = itemSearch.value.trim().toLowerCase();
        const list = store.itemsIn(cat.id)
            .filter(i => i.name.toLowerCase().includes(q))
            .sort(sort.fn);

        if (list.length === 0) {
            itemList.appendChild(elem('li', 'empty-note',
                q ? 'No items match your search.' : 'No items in this category yet.'));
            return;
        }

        list.forEach(item => {
            const li = elem('li', 'list-row');
            const main = elem('button', 'row-main item-main');
            main.type = 'button';
            main.setAttribute('aria-label', 'Edit ' + item.name);

            const text = elem('span', 'row-text');
            text.appendChild(elem('span', 'row-name', item.name));
            text.appendChild(elem('span', 'row-meta', peso(item.price)));
            if (!item.available) text.appendChild(elem('span', 'tag-unavailable', 'Unavailable'));

            main.append(text, elem('i', 'bi bi-pencil row-icon'));
            main.addEventListener('click', () => openItemModal(item));

            li.appendChild(main);
            itemList.appendChild(li);
        });
    }

    itemSearch.addEventListener('input', renderItems);
    itemSortBtn.addEventListener('click', () => {
        itemSortIndex = (itemSortIndex + 1) % ITEM_SORTS.length;
        renderItems();
    });

    // CATEGORY MODAL
    const categoryForm = byId('categoryForm');
    const categoryNameInput = byId('categoryName');
    const categoryError = byId('categoryError');
    const deleteCategoryBtn = byId('deleteCategoryBtn');
    let editingCategory = null;

    function openCategoryModal(cat = null) {
        editingCategory = cat;
        categoryError.textContent = '';
        categoryNameInput.value = cat ? cat.name : '';
        byId('categoryModalTitle').textContent = cat ? 'Edit category' : 'Add category';
        deleteCategoryBtn.hidden = !cat;
        Modal.open('category-modal');
    }

    byId('addCategoryBtn').addEventListener('click', () => openCategoryModal());

    categoryForm.addEventListener('submit', e => {
        e.preventDefault();
        const name = categoryNameInput.value.trim();
        if (!name) { categoryError.textContent = 'Please enter a category name.'; return; }
        if (store.categoryNameTaken(name, editingCategory ? editingCategory.id : null)) {
            categoryError.textContent = 'You already have a category with that name.';
            return;
        }
        if (editingCategory) {
            editingCategory.name = name;
        } else {
            selectedCategoryId = store.addCategory(name).id;   // jump to the new category
        }
        Modal.close('category-modal');
        renderCategories();
        renderItems();
    });

    deleteCategoryBtn.addEventListener('click', () => {
        if (!editingCategory) return;
        const only = store.itemsIn(editingCategory.id).filter(i => i.categoryIds.length === 1).length;
        const warn = only ? ' ' + only + ' item(s) that only belong to it will be deleted too.' : '';
        if (!window.confirm('Delete the category "' + editingCategory.name + '"?' + warn)) return;

        store.deleteCategory(editingCategory.id);
        if (selectedCategoryId === editingCategory.id) {
            selectedCategoryId = store.categories.length ? store.categories[0].id : null;
        }
        Modal.close('category-modal');
        renderCategories();
        renderItems();
    });

    // ITEM MODAL
    const itemForm = byId('itemForm');
    const itemNameInput = byId('itemName');
    const itemPriceInput = byId('itemPrice');
    const itemDescInput = byId('itemDescription');
    const itemCats = byId('itemCategoryChecks');
    const itemError = byId('itemError');
    const photoBox = byId('itemPhotoPreview');
    const photoInput = byId('itemPhotoInput');
    const photoBtn = byId('itemPhotoBtn');
    const removePhotoBtn = byId('itemPhotoRemove');
    const deleteItemBtn = byId('deleteItemBtn');
    const availBtn = byId('toggleAvailableBtn');

    const MAX_PHOTO_BYTES = 5 * 1024 * 1024;
    let editingItem = null;
    let pendingPhoto = null;
    let pendingAvailable = true;

    function renderPhoto() {
        photoBox.replaceChildren();
        if (pendingPhoto) {
            const img = elem('img');
            img.src = pendingPhoto;
            img.alt = itemNameInput.value.trim() || 'Item photo';
            photoBox.appendChild(img);
        } else {
            photoBox.appendChild(elem('i', 'bi bi-image'));
        }
        photoBtn.textContent = pendingPhoto ? 'Change photo' : 'Upload photo';
        removePhotoBtn.hidden = !pendingPhoto;
    }

    function renderAvailability() {
        availBtn.textContent = pendingAvailable ? 'Mark as unavailable' : 'Mark as available';
    }

    function openItemModal(item = null) {
        editingItem = item;
        itemForm.reset();
        itemError.textContent = '';
        photoInput.value = '';
        pendingPhoto = item ? item.photo : null;
        pendingAvailable = item ? item.available : true;

        byId('itemModalTitle').textContent = item ? 'Edit item' : 'Add item';
        itemNameInput.value = item ? item.name : '';
        itemPriceInput.value = item ? item.price : '';
        itemDescInput.value = item ? item.description : '';

        itemCats.replaceChildren();
        store.categories.forEach(cat => {
            const wrap = elem('div', 'form-check');
            const box = elem('input', 'form-check-input');
            box.type = 'checkbox';
            box.id = 'item-cat-' + cat.id;
            box.value = cat.id;
            box.checked = item ? item.categoryIds.includes(cat.id) : cat.id === selectedCategoryId;
            const label = elem('label', 'form-check-label', cat.name);
            label.htmlFor = box.id;
            wrap.append(box, label);
            itemCats.appendChild(wrap);
        });

        deleteItemBtn.hidden = !item;
        availBtn.hidden = !item;
        renderAvailability();
        renderPhoto();
        Modal.open('item-modal');
    }

    addItemBtn.addEventListener('click', () => openItemModal());

    photoBtn.addEventListener('click', () => photoInput.click());
    removePhotoBtn.addEventListener('click', () => {
        pendingPhoto = null;
        photoInput.value = '';
        renderPhoto();
    });
    photoInput.addEventListener('change', () => {
        const file = photoInput.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            itemError.textContent = 'Please choose an image file (PNG, JPG, WEBP...).';
            photoInput.value = '';
            return;
        }
        if (file.size > MAX_PHOTO_BYTES) {
            itemError.textContent = 'That image is too big. Please choose one under 5 MB.';
            photoInput.value = '';
            return;
        }
        itemError.textContent = '';
        const reader = new FileReader();
        reader.onload = () => { pendingPhoto = reader.result; renderPhoto(); };
        reader.readAsDataURL(file);
    });

    availBtn.addEventListener('click', () => {
        pendingAvailable = !pendingAvailable;
        renderAvailability();
    });

    itemForm.addEventListener('submit', e => {
        e.preventDefault();
        const name = itemNameInput.value.trim();
        const price = parseFloat(itemPriceInput.value);
        const categoryIds = [...itemCats.querySelectorAll('input:checked')].map(b => Number(b.value));

        if (!name) { itemError.textContent = 'Please enter an item name.'; return; }
        if (Number.isNaN(price) || price < 0) { itemError.textContent = 'Please enter a valid price (0 or more).'; return; }
        if (categoryIds.length === 0) { itemError.textContent = 'Pick at least one category for this item.'; return; }

        const fields = {
            name, price, categoryIds,
            description: itemDescInput.value.trim(),
            photo: pendingPhoto,
            available: pendingAvailable
        };
        if (editingItem) Object.assign(editingItem, fields);
        else store.addItem(fields);

        // if the item no longer shows in the current category, keep the view on existing category
        if (!categoryIds.includes(selectedCategoryId) && !editingItem) selectedCategoryId = categoryIds[0];

        Modal.close('item-modal');
        renderCategories();
        renderItems();
    });

    deleteItemBtn.addEventListener('click', () => {
        if (!editingItem) return;
        if (!window.confirm('Delete "' + editingItem.name + '"? This cannot be undone.')) return;
        store.deleteItem(editingItem.id);
        Modal.close('item-modal');
        renderItems();
    });

    // close modals
    document.querySelectorAll('[data-close]').forEach(btn => {
        btn.addEventListener('click', () => Modal.closeAll());
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') Modal.closeAll(); });

    renderStatus();
    renderCategories();
    renderItems();
}

if (document.body.dataset.page === 'manage-store') initManageStorePage();