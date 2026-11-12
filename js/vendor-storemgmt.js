// js/vendor-storemgmt.js — real backend wiring for manage-store.php

(function () {
    if (document.body.dataset.page !== 'manage-store') return;

    const SCRIPT_BASE = new URL('../', document.currentScript.src);
    const stallId = Number(document.body.dataset.stallId || 0);
    if (!stallId) return;

    // ---- STATE ---------------------------------------------------------
    const state = {
        categories: [],
        items: [],
        open: document.body.dataset.stallOpen === '1',
        selectedCategoryId: null,
        csrfToken: null,
        categorySortAsc: true,
        itemSortIndex: 0,
        editingCategory: null,
        editingItem: null,
        pendingPhoto: null,
        pendingAvailable: true,
        editingOptionGroups: [],
        optionCsrfToken: null,
    };

    const ITEM_SORTS = [
        { key: 'name-asc',   label: 'name (A to Z)',      icon: 'bi-sort-alpha-down',   fn: (a, b) => byText(a.name, b.name) },
        { key: 'name-desc',  label: 'name (Z to A)',      icon: 'bi-sort-alpha-up',     fn: (a, b) => byText(b.name, a.name) },
        { key: 'price-asc',  label: 'price (low to high)', icon: 'bi-sort-numeric-down', fn: (a, b) => a.price - b.price },
        { key: 'price-desc', label: 'price (high to low)', icon: 'bi-sort-numeric-up',   fn: (a, b) => b.price - a.price },
    ];

    // ---- ELEMENT HELPERS -----------------------------------------------
    const byId = id => document.getElementById(id);

    function elem(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    }

    const byText = (a, b) => a.localeCompare(b, undefined, { sensitivity: 'base' });
    const peso = n => '\u20B1' + Number(n).toLocaleString('en-PH', { maximumFractionDigits: 2 });

    // ---- MODAL HELPERS -------------------------------------------------
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
        },
    };

    // ---- API HELPERS ---------------------------------------------------
    async function menuRequest(body) {
        const res = await fetch(new URL('controller-vendor/menu.php', SCRIPT_BASE), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({...body, stallId, csrfToken: state.csrfToken}),
        });
        let data;
        try {
            data = await res.json();
        } catch {
            throw new Error('The server returned an unexpected response.');
        }
        if (!res.ok || !data.success) throw new Error(data.message || 'Request failed.');
        return data;
    }

    async function uploadPhoto(file) {
        const form = new FormData();
        form.append('image', file);
        form.append('csrfToken', state.csrfToken);
        const res = await fetch(new URL('controller-vendor/upload-image.php', SCRIPT_BASE), {
            method: 'POST',
            credentials: 'same-origin',
            body: form,
        });
        let data;
        try {
            data = await res.json();
        } catch {
            throw new Error('The server returned an unexpected response while uploading.');
        }
        if (!res.ok || !data.success) throw new Error(data.message || 'Upload failed.');
        return data.path;
    }

    async function saveOptionGroups(productId, groups) {
        if (!productId) return;
        const res = await fetch(new URL('controller-vendor/product-options.php', SCRIPT_BASE), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({csrfToken: state.csrfToken, productId, groups}),
        });
        let data;
        try {
            data = await res.json();
        } catch {
            throw new Error('The server returned an unexpected response while saving customizations.');
        }
        if (!res.ok || data.success !== true) throw new Error(data.message || 'Unable to save product customizations.');
        return data.groups;
    }

    // ---- DISPLAY HELPERS -----------------------------------------------
    function productImageUrl(path) {
        if (!path) return null;
        if (/^(https?:|data:|\/)/i.test(path)) return path;
        return new URL(path, SCRIPT_BASE).href;
    }

    function toItem(p) {
        return {
            databaseProductId: Number(p.id),
            name: p.name,
            price: Number(p.price),
            description: p.description || '',
            categoryId: Number(p.category_id),
            photo: p.image_path || null,
            available: Boolean(Number(p.is_available)),
            optionGroups: p.optionGroups || [],
        };
    }

    // ---- BOOT ----------------------------------------------------------
    async function load() {
        try {
            const res = await fetch(new URL('controller-vendor/menu.php?stallId=' + stallId, SCRIPT_BASE), {
                credentials: 'same-origin',
                cache: 'no-store',
            });
            const data = await res.json();
            if (!res.ok || !data.success) throw new Error(data.message || 'Unable to load menu.');
            state.csrfToken = data.csrfToken;
            state.categories = data.categories.map(c => ({ id: Number(c.id), name: c.name }));
            state.items = data.products.map(toItem);
            state.selectedCategoryId = state.categories.length ? state.categories[0].id : null;
            initUi();
        } catch (err) {
            const note = document.getElementById('itemsNote');
            if (note) note.textContent = 'Unable to load the menu: ' + err.message;
        }
    }

    // ---- MAIN UI WIRING ------------------------------------------------
    function initUi() {
        // HEADER ---------------------------------------------------------
        const storeLogo = byId('storeLogo');
        const storeName = byId('storeName');
        const storeAddress = byId('storeAddress');
        const storeHours = byId('storeHours');
        const statusPill = byId('storeStatus');
        const toggleOpenBtn = byId('toggleOpenBtn');

        const imagePath = document.body.dataset.stallImage;
        if (imagePath) storeLogo.src = productImageUrl(imagePath) || '';
        storeLogo.alt = document.body.dataset.stallName + ' logo';
        storeName.textContent = document.body.dataset.stallName;
        storeAddress.textContent = document.body.dataset.stallLocation || '—';
        storeHours.textContent = document.body.dataset.stallHours || 'Hours not set';

        function renderStatus() {
            statusPill.textContent = state.open ? 'OPEN' : 'CLOSED';
            statusPill.className = 'status ' + (state.open ? 'open' : 'closed');
            toggleOpenBtn.textContent = state.open ? 'Mark as closed' : 'Mark as open';
            toggleOpenBtn.setAttribute('aria-pressed', String(!state.open));
        }
        renderStatus();

        toggleOpenBtn.addEventListener('click', async () => {
            const desired = !state.open;
            toggleOpenBtn.disabled = true;
            try {
                await menuRequest({action: 'stall_toggle', isOpen: desired});
                state.open = desired;
                renderStatus();
            } catch (err) {
                alert(err.message);
            } finally {
                toggleOpenBtn.disabled = false;
            }
        });

        // CATEGORIES -----------------------------------------------------
        const categoryList = byId('categoryList');
        const categorySearch = byId('categorySearch');
        const categorySortBtn = byId('categorySort');
        const addCategoryBtn = byId('addCategoryBtn');

        function renderCategories() {
            const q = categorySearch.value.trim().toLowerCase();
            let list = state.categories.filter(c => c.name.toLowerCase().includes(q));
            list = [...list].sort((a, b) =>
                state.categorySortAsc ? byText(a.name, b.name) : byText(b.name, a.name));

            categorySortBtn.innerHTML = '';
            categorySortBtn.appendChild(elem('i', 'bi ' + (state.categorySortAsc ? 'bi-sort-alpha-down' : 'bi-sort-alpha-up')));
            categorySortBtn.title = 'Sorted by name (' + (state.categorySortAsc ? 'A to Z' : 'Z to A') + ')';

            categoryList.replaceChildren();

            if (list.length === 0) {
                categoryList.appendChild(elem('li', 'empty-note',
                    state.categories.length === 0
                        ? 'No categories yet. Add one to get started.'
                        : 'No categories match your search.'));
                return;
            }

            list.forEach(cat => {
                const selected = cat.id === state.selectedCategoryId;
                const li = elem('li', 'list-row' + (selected ? ' selected' : ''));
                const main = elem('button', 'row-main');
                main.type = 'button';
                main.setAttribute('aria-pressed', String(selected));
                main.appendChild(elem('span', 'row-name', cat.name));
                main.addEventListener('click', () => {
                    state.selectedCategoryId = cat.id;
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
            state.categorySortAsc = !state.categorySortAsc;
            renderCategories();
        });

        // ITEMS ----------------------------------------------------------
        const itemList = byId('itemList');
        const itemSearch = byId('itemSearch');
        const itemSortBtn = byId('itemSort');
        const itemsNote = byId('itemsNote');
        const addItemBtn = byId('addItemBtn');

        function itemsIn(categoryId) {
            return state.items.filter(i => i.categoryId === categoryId);
        }

        function renderItems() {
            const sort = ITEM_SORTS[state.itemSortIndex];
            const cat = state.categories.find(c => c.id === state.selectedCategoryId);

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
            const list = itemsIn(cat.id).filter(i => i.name.toLowerCase().includes(q)).sort(sort.fn);

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
            state.itemSortIndex = (state.itemSortIndex + 1) % ITEM_SORTS.length;
            renderItems();
        });

        // CATEGORY MODAL -------------------------------------------------
        const categoryForm = byId('categoryForm');
        const categoryNameInput = byId('categoryName');
        const categoryError = byId('categoryError');
        const deleteCategoryBtn = byId('deleteCategoryBtn');

        function openCategoryModal(cat = null) {
            state.editingCategory = cat;
            categoryError.textContent = '';
            categoryNameInput.value = cat ? cat.name : '';
            byId('categoryModalTitle').textContent = cat ? 'Edit category' : 'Add category';
            deleteCategoryBtn.hidden = !cat;
            Modal.open('category-modal');
        }

        addCategoryBtn.addEventListener('click', () => openCategoryModal());

        categoryForm.addEventListener('submit', async e => {
            e.preventDefault();
            const name = categoryNameInput.value.trim();
            if (!name) { categoryError.textContent = 'Please enter a category name.'; return; }
            if (state.categories.some(c =>
                c.name.toLowerCase() === name.toLowerCase() &&
                (!state.editingCategory || c.id !== state.editingCategory.id))) {
                categoryError.textContent = 'You already have a category with that name.';
                return;
            }
            const saveBtn = categoryForm.querySelector('button[type=submit]');
            saveBtn.disabled = true;
            try {
                if (state.editingCategory) {
                    await menuRequest({action: 'category_rename', categoryId: state.editingCategory.id, name});
                    state.editingCategory.name = name;
                } else {
                    const data = await menuRequest({action: 'category_add', name});
                    state.categories.push({id: data.category.id, name: data.category.name});
                    state.selectedCategoryId = data.category.id;
                }
                Modal.close('category-modal');
                renderCategories();
                renderItems();
            } catch (err) {
                categoryError.textContent = err.message;
            } finally {
                saveBtn.disabled = false;
            }
        });

        deleteCategoryBtn.addEventListener('click', async () => {
            if (!state.editingCategory) return;
            const only = itemsIn(state.editingCategory.id).length;
            const warn = only ? ` ${only} item(s) in it must be moved or deleted first.` : '';
            if (!confirm(`Delete the category "${state.editingCategory.name}"?${warn}`)) return;
            deleteCategoryBtn.disabled = true;
            try {
                await menuRequest({action: 'category_delete', categoryId: state.editingCategory.id});
                state.categories = state.categories.filter(c => c.id !== state.editingCategory.id);
                if (state.selectedCategoryId === state.editingCategory.id) {
                    state.selectedCategoryId = state.categories.length ? state.categories[0].id : null;
                }
                Modal.close('category-modal');
                renderCategories();
                renderItems();
            } catch (err) {
                categoryError.textContent = err.message;
            } finally {
                deleteCategoryBtn.disabled = false;
            }
        });

        // ITEM MODAL -----------------------------------------------------
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
        const optionGroupsEditor = byId('productOptionGroups');
        const optionGroupsStatus = byId('customizationEditorStatus');
        const addOptionGroupBtn = byId('addOptionGroupBtn');
        const MAX_PHOTO_BYTES = 5 * 1024 * 1024;

        function renderPhoto() {
            photoBox.replaceChildren();
            if (state.pendingPhoto) {
                const img = elem('img');
                img.src = productImageUrl(state.pendingPhoto) || '';
                img.alt = itemNameInput.value.trim() || 'Item photo';
                photoBox.appendChild(img);
            } else {
                photoBox.appendChild(elem('i', 'bi bi-image'));
            }
            photoBtn.textContent = state.pendingPhoto ? 'Change photo' : 'Upload photo';
            removePhotoBtn.hidden = !state.pendingPhoto;
        }

        function renderAvailability() {
            availBtn.textContent = state.pendingAvailable ? 'Mark as unavailable' : 'Mark as available';
        }

        function renderOptionGroups() {
            optionGroupsEditor.replaceChildren();
            if (!state.editingItem?.databaseProductId) {
                optionGroupsStatus.textContent = 'Save the item once before adding customizations.';
                addOptionGroupBtn.disabled = true;
                return;
            }
            optionGroupsStatus.textContent = '';
            addOptionGroupBtn.disabled = false;

            state.editingOptionGroups.forEach((group, groupIndex) => {
                const groupCard = elem('section', 'option-group-editor');
                const groupHead = elem('div', 'option-group-editor-head');
                groupHead.style.display = 'grid';
                groupHead.style.gridTemplateColumns = 'minmax(0, 3fr) minmax(0, 1fr)';
                groupHead.style.alignItems = 'center';
                groupHead.style.gap = '6px';

                const nameInput = elem('input', 'form-control');
                nameInput.style.width = '100%';
                nameInput.type = 'text';
                nameInput.maxLength = 100;
                nameInput.placeholder = 'Section name (e.g. Size)';
                nameInput.value = group.name;
                nameInput.addEventListener('input', () => { group.name = nameInput.value; });

                const delGroup = elem('button', 'vendor-btn danger small', 'Remove section');
                delGroup.type = 'button';
                delGroup.style.width = '100%';
                delGroup.addEventListener('click', () => {
                    state.editingOptionGroups.splice(groupIndex, 1);
                    renderOptionGroups();
                });

                const reqLabel = elem('label', 'option-required-toggle');
                const reqInput = elem('input');
                reqInput.type = 'checkbox';
                reqInput.checked = group.required;
                reqInput.addEventListener('change', () => { group.required = reqInput.checked; });
                reqLabel.append(reqInput, document.createTextNode(' Required'));

                groupHead.append(nameInput, delGroup);
                groupCard.append(groupHead, reqLabel);

                const choices = elem('div', 'option-choice-list');
                group.choices.forEach((choice, choiceIndex) => {
                    const row = elem('div', 'option-choice-editor');
                    row.style.display = 'grid';
                    row.style.gridTemplateColumns = 'minmax(0, 1fr)';
                    row.style.alignItems = 'center';
                    row.style.gap = '6px';

                    const delChoice = elem('button', 'vendor-btn danger small', 'Remove choice');
                    delChoice.type = 'button';
                    delChoice.style.width = '100%';
                    delChoice.addEventListener('click', () => {
                        group.choices.splice(choiceIndex, 1);
                        renderOptionGroups();
                    });

                    const choiceName = elem('input', 'form-control option-choice-name');
                    choiceName.style.width = '100%';
                    choiceName.type = 'text';
                    choiceName.maxLength = 100;
                    choiceName.placeholder = 'Choice name';
                    choiceName.value = choice.name;
                    choiceName.addEventListener('input', () => { choice.name = choiceName.value; });

                    const priceInput = elem('input', 'form-control option-choice-price');
                    priceInput.style.width = '100%';
                    priceInput.type = 'number';
                    priceInput.min = '0';
                    priceInput.max = '99999999.99';
                    priceInput.step = '0.01';
                    priceInput.inputMode = 'decimal';
                    priceInput.placeholder = 'Extra price';
                    priceInput.value = choice.extraPrice;
                    priceInput.addEventListener('input', () => { choice.extraPrice = priceInput.value; });

                    const avLabel = elem('label', 'option-available-toggle');
                    const avInput = elem('input');
                    avInput.type = 'checkbox';
                    avInput.checked = choice.available !== false;
                    avInput.addEventListener('change', () => { choice.available = avInput.checked; });
                    avLabel.append(avInput, document.createTextNode(' Available'));

                    const priceRemoveRow = elem('div', 'option-choice-price-remove');
                    priceRemoveRow.style.display = 'grid';
                    priceRemoveRow.style.gridTemplateColumns = 'minmax(0, 1fr) minmax(0, 1fr)';
                    priceRemoveRow.style.alignItems = 'center';
                    priceRemoveRow.style.gap = '6px';
                    priceRemoveRow.append(priceInput, delChoice);

                    row.append(choiceName, priceRemoveRow, avLabel);
                    choices.appendChild(row);
                });
                groupCard.appendChild(choices);

                const addChoice = elem('button', 'vendor-btn secondary small', 'Add choice');
                addChoice.type = 'button';
                addChoice.addEventListener('click', () => {
                    group.choices.push({optionId: null, name: '', extraPrice: '0.00', available: true});
                    renderOptionGroups();
                });
                groupCard.appendChild(addChoice);
                optionGroupsEditor.appendChild(groupCard);
            });
        }

        addOptionGroupBtn.addEventListener('click', () => {
            if (!state.editingItem?.databaseProductId) {
                optionGroupsStatus.textContent = 'Save the item once before adding customizations.';
                return;
            }
            state.editingOptionGroups.push({
                groupId: null,
                name: '',
                required: true,
                choices: [{optionId: null, name: '', extraPrice: '0.00', available: true}],
            });
            renderOptionGroups();
        });

        function openItemModal(item = null) {
            state.editingItem = item;
            itemForm.reset();
            itemError.textContent = '';
            photoInput.value = '';
            state.pendingPhoto = item ? item.photo : null;
            state.pendingAvailable = item ? item.available : true;
            state.editingOptionGroups = item
                ? item.optionGroups.map(g => ({...g, choices: g.choices.map(c => ({...c}))}))
                : [];

            byId('itemModalTitle').textContent = item ? 'Edit item' : 'Add item';
            itemNameInput.value = item ? item.name : '';
            itemPriceInput.value = item ? item.price : '';
            itemDescInput.value = item ? item.description : '';

            itemCats.replaceChildren();
            state.categories.forEach(cat => {
                const wrap = elem('div', 'form-check');
                const box = elem('input', 'form-check-input');
                box.type = 'checkbox';
                box.id = 'item-cat-' + cat.id;
                box.value = cat.id;
                box.checked = item ? item.categoryId === cat.id : cat.id === state.selectedCategoryId;
                box.addEventListener('change', () => {
                    if (box.checked) {
                        itemCats.querySelectorAll('input[type="checkbox"]').forEach(b => {
                            if (b !== box) b.checked = false;
                        });
                    }
                });
                const label = elem('label', 'form-check-label', cat.name);
                label.htmlFor = box.id;
                wrap.append(box, label);
                itemCats.appendChild(wrap);
            });

            deleteItemBtn.hidden = !item;
            availBtn.hidden = !item;
            renderAvailability();
            renderPhoto();
            renderOptionGroups();
            Modal.open('item-modal');
        }

        addItemBtn.addEventListener('click', () => openItemModal());

        photoBtn.addEventListener('click', () => photoInput.click());
        removePhotoBtn.addEventListener('click', () => {
            state.pendingPhoto = null;
            photoInput.value = '';
            renderPhoto();
        });
        photoInput.addEventListener('change', async () => {
            const file = photoInput.files[0];
            if (!file) return;
            if (!file.type.startsWith('image/')) {
                itemError.textContent = 'Please choose an image file.';
                photoInput.value = '';
                return;
            }
            if (file.size > MAX_PHOTO_BYTES) {
                itemError.textContent = 'That image is too big. Please choose one under 5 MB.';
                photoInput.value = '';
                return;
            }
            itemError.textContent = '';
            photoBtn.disabled = true;
            try {
                const path = await uploadPhoto(file);
                state.pendingPhoto = path;
                renderPhoto();
            } catch (err) {
                itemError.textContent = err.message;
            } finally {
                photoBtn.disabled = false;
            }
        });

        availBtn.addEventListener('click', () => {
            state.pendingAvailable = !state.pendingAvailable;
            renderAvailability();
        });

        itemForm.addEventListener('submit', async e => {
            e.preventDefault();
            const name = itemNameInput.value.trim();
            const price = parseFloat(itemPriceInput.value);
            const categoryBox = itemCats.querySelector('input:checked');
            const categoryId = categoryBox ? Number(categoryBox.value) : null;

            if (!name) { itemError.textContent = 'Please enter an item name.'; return; }
            if (Number.isNaN(price) || price < 0) { itemError.textContent = 'Please enter a valid price.'; return; }
            if (!categoryId) { itemError.textContent = 'Pick a category for this item.'; return; }

            const saveBtn = itemForm.querySelector('button[type=submit]');
            saveBtn.disabled = true;
            try {
                const payload = {
                    categoryId,
                    name,
                    price,
                    description: itemDescInput.value.trim(),
                    imagePath: state.pendingPhoto,
                    available: state.pendingAvailable,
                };

                let productId;
                if (state.editingItem?.databaseProductId) {
                    await menuRequest({action: 'product_update', productId: state.editingItem.databaseProductId, ...payload});
                    productId = state.editingItem.databaseProductId;
                } else {
                    const data = await menuRequest({action: 'product_add', ...payload});
                    productId = data.productId;
                }

                // Save option groups after the product exists.
                if (productId && state.editingOptionGroups.length >= 0) {
                    try {
                        const groups = await saveOptionGroups(productId, state.editingOptionGroups);
                        if (Array.isArray(groups)) {
                            state.editingOptionGroups = groups.map(g => ({
                                ...g,
                                choices: g.choices.map(c => ({...c})),
                            }));
                        }
                    } catch (optErr) {
                        itemError.textContent = 'Item saved, but customizations failed: ' + optErr.message;
                        saveBtn.disabled = false;
                        return;
                    }
                }

                // Refresh the local record.
                const refreshed = {
                    databaseProductId: productId,
                    name,
                    price,
                    description: payload.description,
                    categoryId,
                    photo: payload.imagePath,
                    available: payload.available,
                    optionGroups: state.editingOptionGroups,
                };
                if (state.editingItem?.databaseProductId) {
                    const idx = state.items.findIndex(i => i.databaseProductId === state.editingItem.databaseProductId);
                    if (idx >= 0) state.items[idx] = refreshed;
                } else {
                    state.items.push(refreshed);
                }
                state.selectedCategoryId = categoryId;
                Modal.close('item-modal');
                renderCategories();
                renderItems();
            } catch (err) {
                itemError.textContent = err.message;
            } finally {
                saveBtn.disabled = false;
            }
        });

        deleteItemBtn.addEventListener('click', async () => {
            if (!state.editingItem?.databaseProductId) return;
            if (!confirm(`Delete "${state.editingItem.name}"? This cannot be undone.`)) return;
            deleteItemBtn.disabled = true;
            try {
                await menuRequest({action: 'product_delete', productId: state.editingItem.databaseProductId});
                state.items = state.items.filter(i => i.databaseProductId !== state.editingItem.databaseProductId);
                Modal.close('item-modal');
                renderItems();
            } catch (err) {
                itemError.textContent = err.message;
            } finally {
                deleteItemBtn.disabled = false;
            }
        });

        // CLOSE MODALS ---------------------------------------------------
        document.querySelectorAll('[data-close]').forEach(btn => {
            btn.addEventListener('click', () => Modal.closeAll());
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') Modal.closeAll();
        });

        // FIRST RENDER ---------------------------------------------------
        renderCategories();
        renderItems();
    }

    load();
})();