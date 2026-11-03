import './bootstrap';

const mobileMenuToggle = document.querySelector('[data-mobile-menu-toggle]');
const appShell = document.querySelector('.app-shell');

const setMobileMenuOpen = (open) => {
    if (!appShell || !(mobileMenuToggle instanceof HTMLButtonElement)) return;
    appShell.classList.toggle('mobile-menu-open', open);
    mobileMenuToggle.setAttribute('aria-expanded', String(open));
    mobileMenuToggle.setAttribute('aria-label', open ? 'Close navigation menu' : 'Open navigation menu');
};

mobileMenuToggle?.addEventListener('click', () => {
    setMobileMenuOpen(!appShell?.classList.contains('mobile-menu-open'));
});
document.querySelector('[data-mobile-menu-close]')?.addEventListener('click', () => setMobileMenuOpen(false));
document.querySelector('.sidebar .main-nav')?.addEventListener('click', (event) => {
    if (event.target.closest('a')) setMobileMenuOpen(false);
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setMobileMenuOpen(false);
});

const profileDialog = document.querySelector('[data-profile-dialog]');
const profileOpenButton = document.querySelector('[data-profile-open]');

if (profileDialog instanceof HTMLDialogElement && profileOpenButton instanceof HTMLButtonElement) {
    const closeButtons = profileDialog.querySelectorAll('[data-profile-close]');
    const avatarInput = profileDialog.querySelector('[data-profile-avatar-input]');
    const avatarPreview = profileDialog.querySelector('[data-profile-avatar-preview]');
    const avatarInitial = profileDialog.querySelector('[data-profile-avatar-initial]');
    let previewUrl = null;

    profileOpenButton.addEventListener('click', () => profileDialog.showModal());
    closeButtons.forEach((button) => {
        button.addEventListener('click', () => profileDialog.close());
    });
    profileDialog.addEventListener('click', (event) => {
        if (event.target === profileDialog) profileDialog.close();
    });
    profileDialog.addEventListener('close', () => profileOpenButton.focus());

    avatarInput?.addEventListener('change', () => {
        if (!(avatarInput instanceof HTMLInputElement) || !avatarPreview || !(avatarPreview instanceof HTMLImageElement)) return;
        const file = avatarInput.files?.[0];
        if (!file) return;

        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);
        avatarPreview.src = previewUrl;
        avatarPreview.hidden = false;
        if (avatarInitial instanceof HTMLElement) avatarInitial.hidden = true;
    });

    if (profileDialog.hasAttribute('data-profile-invalid')) profileDialog.showModal();
}

const userAvatarInput = document.querySelector('[data-user-avatar-input]');
const userAvatarPreview = document.querySelector('[data-user-avatar-preview]');
const userAvatarInitial = document.querySelector('[data-user-avatar-initial]');
const userAvatarLabel = document.querySelector('[data-user-avatar-label]');
const userAvatarRemove = document.querySelector('[data-user-avatar-remove]');

if (userAvatarInput instanceof HTMLInputElement && userAvatarPreview instanceof HTMLImageElement) {
    let userPreviewUrl = null;
    userAvatarInput.addEventListener('change', () => {
        const file = userAvatarInput.files?.[0];
        if (!file) return;

        if (userPreviewUrl) URL.revokeObjectURL(userPreviewUrl);
        userPreviewUrl = URL.createObjectURL(file);
        userAvatarPreview.src = userPreviewUrl;
        userAvatarPreview.hidden = false;
        if (userAvatarInitial instanceof HTMLElement) userAvatarInitial.hidden = true;
        if (userAvatarLabel) userAvatarLabel.textContent = 'Change avatar';
        if (userAvatarRemove instanceof HTMLInputElement) userAvatarRemove.checked = false;
    });

    if (userAvatarRemove instanceof HTMLInputElement) {
        userAvatarRemove.addEventListener('change', () => {
            if (userAvatarRemove.checked) {
                userAvatarInput.value = '';
                userAvatarPreview.hidden = true;
                if (userAvatarInitial instanceof HTMLElement) userAvatarInitial.hidden = false;
            } else if (userAvatarPreview.src && userAvatarPreview.getAttribute('src')) {
                userAvatarPreview.hidden = false;
                if (userAvatarInitial instanceof HTMLElement) userAvatarInitial.hidden = true;
            }
        });
    }
}

const updateMaterialsSelectionCount = () => {
    const count = document.querySelector('[data-selection-count]');
    if (!count) return;

    const selectedCount = document.querySelectorAll('[data-material-row]:checked').length;
    count.textContent = `${selectedCount} selected`;
};

const confirmationDialog = document.querySelector('[data-confirm-dialog]');

if (confirmationDialog instanceof HTMLDialogElement) {
    const title = confirmationDialog.querySelector('#confirm-dialog-title');
    const message = confirmationDialog.querySelector('#confirm-dialog-message');
    const icon = confirmationDialog.querySelector('[data-confirm-icon]');
    const cancelButton = confirmationDialog.querySelector('[data-confirm-cancel]');
    const acceptButton = confirmationDialog.querySelector('[data-confirm-accept]');
    let pendingForm = null;
    let approvedForm = null;
    let submitter = null;
    let pendingAction = null;

    const openConfirmation = (trigger) => {
        const tone = trigger.dataset.confirmTone || 'primary';
        title.textContent = trigger.dataset.confirmTitle || 'Confirm this action';
        message.textContent = trigger.dataset.confirm;
        acceptButton.textContent = trigger.dataset.confirmAction || 'Confirm';
        acceptButton.classList.toggle('button-danger', tone === 'danger');
        acceptButton.classList.toggle('button-primary', tone !== 'danger');
        confirmationDialog.classList.toggle('confirm-dialog-danger', tone === 'danger');
        confirmationDialog.classList.toggle('confirm-dialog-warning', tone === 'warning');
        confirmationDialog.classList.toggle('confirm-dialog-primary', tone === 'primary');
        icon.textContent = tone === 'danger' ? '×' : tone === 'warning' ? '!' : '?';
        pendingAction = trigger.hasAttribute('data-confirm-print') ? 'print' : 'submit';
        confirmationDialog.showModal();
        acceptButton.focus();
    };

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        const trigger = form.hasAttribute('data-confirm')
            ? form
            : event.submitter instanceof HTMLElement && event.submitter.hasAttribute('data-confirm')
                ? event.submitter
                : null;
        if (!trigger) return;
        if (approvedForm === form) {
            approvedForm = null;
            return;
        }

        event.preventDefault();
        pendingForm = form;
        submitter = event.submitter;
        openConfirmation(trigger);
    });

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-confirm]');
        if (!(trigger instanceof HTMLButtonElement) || trigger.type !== 'button') return;

        event.preventDefault();
        pendingForm = null;
        submitter = trigger;
        openConfirmation(trigger);
    });

    cancelButton.addEventListener('click', () => confirmationDialog.close());

    acceptButton.addEventListener('click', () => {
        if (!pendingAction) return;

        const action = pendingAction;
        const form = pendingForm;
        const trigger = submitter;
        pendingAction = null;
        pendingForm = null;
        confirmationDialog.close();

        if (action === 'print') {
            window.print();
            return;
        }
        if (!form) return;

        approvedForm = form;
        if (trigger && trigger.form === form) form.requestSubmit(trigger);
        else form.requestSubmit();
    });

    confirmationDialog.addEventListener('close', () => {
        pendingForm = null;
        if (submitter?.isConnected) submitter.focus();
        submitter = null;
    });
}

document.querySelector('[data-toast-container]')?.addEventListener('click', (event) => {
    const closeButton = event.target.closest('[aria-label="Dismiss notification"]');
    if (closeButton) closeButton.closest('.toast')?.remove();
});

document.querySelectorAll('[data-toast-container] .toast').forEach((toast) => {
    if (!toast.hasAttribute('data-toast-persistent')) {
        window.setTimeout(() => toast.remove(), 8000);
    }
});

document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
    if (!(toggle instanceof HTMLButtonElement)) return;
    const passwordInput = document.getElementById(toggle.getAttribute('aria-controls'));
    if (!(passwordInput instanceof HTMLInputElement)) return;

    toggle.addEventListener('click', () => {
        const showPassword = passwordInput.type === 'password';
        passwordInput.type = showPassword ? 'text' : 'password';
        toggle.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
        toggle.setAttribute('aria-pressed', String(showPassword));
        toggle.querySelector('[data-password-hidden]')?.toggleAttribute('hidden', showPassword);
        toggle.querySelector('[data-password-visible]')?.toggleAttribute('hidden', !showPassword);
    });
});

document.addEventListener('click', (event) => {
    const importButton = event.target.closest('[data-trigger-import]');
    if (importButton) {
        const input = document.getElementById('materials-import-file');
        const form = document.getElementById('materials-import-form');
        if (input instanceof HTMLInputElement && form instanceof HTMLFormElement) {
            form.dataset.submitAfterFile = 'true';
            input.addEventListener('cancel', () => {
                delete form.dataset.submitAfterFile;
            }, { once: true });
            input.click();
        }
        return;
    }

    const resetButton = event.target.closest('[data-reset-filters]');
    if (resetButton) {
        window.location.assign(resetButton.dataset.resetUrl);
        return;
    }

    const addButton = event.target.closest('[data-add-line]');
    if (addButton) {
        const listId = addButton.dataset.addLine;
        const list = document.getElementById(listId);
        const template = document.getElementById(`${listId}-template`) || document.getElementById('purchase-line-template');
        if (!list || !template) return;

        const indexes = [...list.querySelectorAll('[name]')]
            .map((field) => Number(field.name.match(/\[(\d+)\]/)?.[1] ?? -1));
        const index = Math.max(-1, ...indexes) + 1;
        const row = template.content.firstElementChild.cloneNode(true);
        row.querySelectorAll('[data-name]').forEach((field) => {
            field.name = `${listId === 'purchase-lines' ? 'items' : listId === 'production-inputs' ? 'inputs' : 'outputs'}[${index}][${field.dataset.name}]`;
            field.removeAttribute('data-name');
        });
        list.append(row);
        document.getElementById('purchase-order-form')?.dispatchEvent(new CustomEvent('purchase:lines-changed', { detail: { addedRow: row } }));
        return;
    }

    const removeButton = event.target.closest('[data-remove-line]');
    if (removeButton) {
        const row = removeButton.closest('[data-line-row]');
        const list = row?.parentElement;
        if (row && list && list.querySelectorAll('[data-line-row]').length > 1) {
            row.remove();
            document.getElementById('purchase-order-form')?.dispatchEvent(new CustomEvent('purchase:lines-changed'));
        }
    }
});

document.addEventListener('change', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLInputElement || target instanceof HTMLSelectElement)) return;

    if (target.matches('[data-currency-code]')) {
        const symbolInput = document.querySelector('[data-currency-symbol]');
        const selectedOption = target.selectedOptions[0];
        if (symbolInput instanceof HTMLInputElement && selectedOption) {
            symbolInput.value = selectedOption.dataset.symbol || '';
        }
        return;
    }

    if (target.matches('[data-select-all]')) {
        document.querySelectorAll('[data-material-row]').forEach((checkbox) => {
            checkbox.checked = target.checked;
        });
        updateMaterialsSelectionCount();
        return;
    }

    if (target.matches('[data-material-row]')) {
        const rows = [...document.querySelectorAll('[data-material-row]')];
        const selectAll = document.querySelector('[data-select-all]');
        if (selectAll instanceof HTMLInputElement) {
            selectAll.checked = rows.length > 0 && rows.every((checkbox) => checkbox.checked);
            selectAll.indeterminate = rows.some((checkbox) => checkbox.checked) && !selectAll.checked;
        }
        updateMaterialsSelectionCount();
        return;
    }

    if (target.matches('#materials-import-file')) {
        const form = target.closest('form');
        if (form instanceof HTMLFormElement && form.dataset.submitAfterFile === 'true' && target.files?.length) {
            delete form.dataset.submitAfterFile;
            form.requestSubmit();
        }
        return;
    }

    if (target.matches('.materials-filters select')) {
        const form = target.closest('form');
        if (form instanceof HTMLFormElement) form.requestSubmit();
        return;
    }

    if (target.matches('[data-page-size]')) {
        const pageSize = document.querySelector('#materials-page-size');
        const form = pageSize?.closest('form');
        if (pageSize instanceof HTMLInputElement && form instanceof HTMLFormElement) {
            pageSize.value = target.value;
            form.requestSubmit();
        }
    }
});

// Production Form Functionality
const productionForm = document.getElementById('production-form');
if (productionForm) {
    const categorySelect = productionForm.querySelector('[data-category-filter]');
    const productSelect = productionForm.querySelector('[data-product-select]');
    const unitTag = productionForm.querySelector('[data-quantity-unit-tag]');
    const quantityInput = productionForm.querySelector('#quantity-made-input');
    const ingredientsContainer = productionForm.querySelector('[data-ingredients-container]');
    const emptyHint = document.getElementById('ingredients-empty-hint');
    const template = document.getElementById('ingredient-row-template');

    // Category elements
    const selectWrap = productionForm.querySelector('[data-category-select-wrap]');
    const newWrap = productionForm.querySelector('[data-new-category-wrap]');
    const newCatInput = productionForm.querySelector('#new-category-input');
    const toggleBtn = productionForm.querySelector('[data-toggle-new-category]');
    const toggleLabel = productionForm.querySelector('[data-toggle-label]');

    // Product elements
    const productSelectWrap = productionForm.querySelector('[data-product-select-wrap]');
    const newProductWrap = productionForm.querySelector('[data-new-product-wrap]');
    const newProductInput = productionForm.querySelector('#new-product-input');
    const toggleProductBtn = productionForm.querySelector('[data-toggle-new-product]');
    const toggleProductLabel = productionForm.querySelector('[data-toggle-product-label]');

    // Goods type classification checkboxes
    const finishedGoodCheckbox = document.getElementById('type-finished-good');
    const semiFinishedGoodCheckbox = document.getElementById('type-semi-finished-good');

    finishedGoodCheckbox?.addEventListener('change', () => {
        if (finishedGoodCheckbox.checked && semiFinishedGoodCheckbox) {
            semiFinishedGoodCheckbox.checked = false;
        } else if (!finishedGoodCheckbox.checked && semiFinishedGoodCheckbox && !semiFinishedGoodCheckbox.checked) {
            finishedGoodCheckbox.checked = true;
        }
        syncLivePreviewCard();
    });

    semiFinishedGoodCheckbox?.addEventListener('change', () => {
        if (semiFinishedGoodCheckbox.checked && finishedGoodCheckbox) {
            finishedGoodCheckbox.checked = false;
        } else if (!semiFinishedGoodCheckbox.checked && finishedGoodCheckbox && !finishedGoodCheckbox.checked) {
            finishedGoodCheckbox.checked = true;
        }
        syncLivePreviewCard();
    });

    // Live preview elements on the page
    const dynamicGroup = document.getElementById('production-dynamic-group');
    const dynamicTitle = document.getElementById('dynamic-group-title');
    const dynamicCardName = document.getElementById('dynamic-card-name');
    const dynamicCardQty = document.getElementById('dynamic-card-qty');

    // Edit mode elements
    const editingProductIdInput = document.getElementById('editing-product-id');
    const panelTitle = document.getElementById('production-create-title');
    const panelSubtitle = document.getElementById('production-create-subtitle');
    const editBadge = document.getElementById('production-edit-mode-badge');
    const submitLabel = document.getElementById('production-submit-label');
    const batchInput = document.getElementById('batch-no-input');
    const expiryInput = document.getElementById('expiry-date-input');
    const noteInput = document.getElementById('note-input');
    const defaultBatchNo = batchInput?.getAttribute('placeholder') || '';

    const showNewCategoryMode = (show) => {
        if (!selectWrap || !newWrap) return;
        if (show) {
            selectWrap.style.display = 'none';
            newWrap.style.display = 'flex';
            if (categorySelect) categorySelect.value = '__new__';
            if (toggleLabel) toggleLabel.textContent = '← Select existing';
            newCatInput?.focus();
        } else {
            newWrap.style.display = 'none';
            selectWrap.style.display = 'flex';
            if (categorySelect && categorySelect.value === '__new__') categorySelect.value = '';
            if (newCatInput) newCatInput.value = '';
            if (toggleLabel) toggleLabel.textContent = '＋ New category';
        }
        filterProductsByCategory();
        syncCatalogGroups();
    };

    const showNewProductMode = (show) => {
        if (!productSelectWrap || !newProductWrap) return;
        if (show) {
            productSelectWrap.style.display = 'none';
            newProductWrap.style.display = 'flex';
            if (productSelect) productSelect.value = '__new__';
            if (toggleProductLabel) toggleProductLabel.textContent = '← Select existing';
            newProductInput?.focus();
        } else {
            newProductWrap.style.display = 'none';
            productSelectWrap.style.display = 'flex';
            if (productSelect && productSelect.value === '__new__') productSelect.value = '';
            if (newProductInput) newProductInput.value = '';
            if (toggleProductLabel) toggleProductLabel.textContent = '＋ New product';
        }
        syncLivePreviewCard();
    };

    toggleBtn?.addEventListener('click', () => {
        const isCurrentlyNew = newWrap && getComputedStyle(newWrap).display !== 'none';
        showNewCategoryMode(!isCurrentlyNew);
    });

    toggleProductBtn?.addEventListener('click', () => {
        const isCurrentlyNew = newProductWrap && getComputedStyle(newProductWrap).display !== 'none';
        showNewProductMode(!isCurrentlyNew);
    });

    const updateProductUnit = () => {
        if (!productSelect || !unitTag) return;
        const selected = productSelect.selectedOptions?.[0];
        const unit = selected?.dataset?.unit;
        if (unit) {
            unitTag.textContent = `(${unit})`;
            unitTag.hidden = false;
        } else {
            unitTag.textContent = '';
            unitTag.hidden = true;
        }
    };

    const updateRowStock = (row) => {
        const select = row.querySelector('[data-ingredient-select]');
        const textSpan = row.querySelector('[data-stock-text]');
        const unitSelect = row.querySelector('[data-ingredient-unit]');
        if (!select) return;

        const option = select.selectedOptions?.[0];
        if (!option || !option.value) {
            if (textSpan) {
                textSpan.textContent = '—';
                textSpan.classList.remove('has-stock', 'no-stock');
            }
            return;
        }

        const stock = parseFloat(option.dataset.stock || 0);
        const unit = option.dataset.unit || '';

        if (unit && unitSelect) {
            const matchingOpt = [...unitSelect.options].find(
                (opt) => opt.value.toLowerCase() === unit.toLowerCase()
            );
            if (matchingOpt) {
                unitSelect.value = matchingOpt.value;
            }
        }

        if (textSpan) {
            const formattedStock = stock.toFixed(2);
            textSpan.textContent = `${formattedStock} ${unit} usable`;
            textSpan.classList.toggle('has-stock', stock > 0);
            textSpan.classList.toggle('no-stock', stock <= 0);
        }
    };

    const filterProductsByCategory = () => {
        if (!(productSelect instanceof HTMLSelectElement)) return;
        const isNewMode = newWrap && getComputedStyle(newWrap).display !== 'none';
        const categoryId = isNewMode ? '' : (categorySelect?.value || '');

        if (categoryId === '__new__') {
            showNewCategoryMode(true);
            return;
        }

        [...productSelect.options].forEach((option, index) => {
            if (index === 0 || option.value === '__new__') return;
            // If category selected and product has category assigned, show matching
            if (categoryId && option.dataset.categoryId) {
                option.hidden = (option.dataset.categoryId !== categoryId);
            } else {
                option.hidden = false;
            }
            option.disabled = option.hidden;
        });

        updateProductUnit();
    };

    // --- Sync left-panel catalog groups and live preview cards ---
    const catalogPrompt = document.getElementById('production-category-prompt');
    const catalogGroupEls = document.querySelectorAll('#production-category-groups [data-category-id]');

    const syncLivePreviewCard = () => {
        const isNewCat = newWrap && getComputedStyle(newWrap).display !== 'none';
        const isNewProd = Boolean(newProductInput?.value?.trim());
        const typedCat = newCatInput?.value?.trim() || 'New Category';
        const typedProd = newProductInput?.value?.trim() || 'New Product';
        const qtyVal = '1.00 Unit';
        const isSemiFinished = Boolean(semiFinishedGoodCheckbox?.checked);
        const classLabel = isSemiFinished ? 'Semi Finished Good' : 'Finished Good';

        if (dynamicTitle) dynamicTitle.textContent = typedCat;
        if (dynamicCardName) dynamicCardName.textContent = typedProd;
        if (dynamicCardQty) dynamicCardQty.textContent = qtyVal;

        const dynClassBadge = document.getElementById('dynamic-card-classification');
        if (dynClassBadge) {
            dynClassBadge.textContent = classLabel;
            dynClassBadge.classList.toggle('is-semi-finished', isSemiFinished);
            dynClassBadge.classList.toggle('is-finished', !isSemiFinished);
        }

        // If in existing category mode, update preview card inside the visible category
        const selectedCatId = isNewCat ? '' : (categorySelect?.value || '');
        document.querySelectorAll('[data-category-preview-card]').forEach((card) => {
            const group = card.closest('[data-category-id]');
            const isMatch = group && String(group.dataset.categoryId) === selectedCatId;
            if (isMatch && isNewProd && !editingProductIdInput?.value) {
                card.style.display = 'flex';
                const nameEl = card.querySelector('[data-category-preview-name]');
                const qtyEl = card.querySelector('[data-category-preview-qty]');
                const classEl = card.querySelector('[data-category-preview-classification]');
                if (nameEl) nameEl.textContent = typedProd;
                if (qtyEl) qtyEl.textContent = qtyVal;
                if (classEl) {
                    classEl.textContent = classLabel;
                    classEl.classList.toggle('is-semi-finished', isSemiFinished);
                    classEl.classList.toggle('is-finished', !isSemiFinished);
                }
            } else {
                card.style.display = 'none';
            }
        });
    };

    const syncCatalogGroups = () => {
        const isNewMode = newWrap && getComputedStyle(newWrap).display !== 'none';
        const rawCategoryId = isNewMode ? '__new__' : (categorySelect?.value || '');

        if (!rawCategoryId) {
            // Nothing selected — show all category groups so the catalog is visible
            catalogGroupEls.forEach((g) => {
                g.classList.remove('production-category-hidden');
            });
            if (dynamicGroup) dynamicGroup.classList.add('production-category-hidden');
            catalogPrompt?.classList.add('production-prompt-hidden');
            syncLivePreviewCard();
            return;
        }

        catalogPrompt?.classList.add('production-prompt-hidden');

        if (rawCategoryId === '__new__') {
            // Typing a new category — show the dynamic preview group
            catalogGroupEls.forEach((g) => g.classList.add('production-category-hidden'));
            if (dynamicGroup) {
                dynamicGroup.classList.remove('production-category-hidden');
                dynamicGroup.open = true;
            }
            syncLivePreviewCard();
            return;
        }

        if (dynamicGroup) dynamicGroup.classList.add('production-category-hidden');

        // Show only the matching category group
        catalogGroupEls.forEach((g) => {
            const matches = String(g.dataset.categoryId) === rawCategoryId;
            g.classList.toggle('production-category-hidden', !matches);
            if (matches) g.open = true;
        });

        syncLivePreviewCard();
    };

    categorySelect?.addEventListener('change', () => {
        if (categorySelect.value === '__new__') {
            showNewCategoryMode(true);
        } else {
            filterProductsByCategory();
        }
        syncCatalogGroups();
    });

    productSelect?.addEventListener('change', () => {
        if (productSelect.value === '__new__') {
            showNewProductMode(true);
            return;
        }
        updateProductUnit();
        const selectedOption = productSelect.selectedOptions?.[0];
        const assignedCatId = selectedOption?.dataset?.categoryId;
        const isNewCatMode = newWrap && getComputedStyle(newWrap).display !== 'none';
        if (assignedCatId && categorySelect && !isNewCatMode) {
            categorySelect.value = assignedCatId;
            filterProductsByCategory();
            syncCatalogGroups();
        }
        syncLivePreviewCard();
    });

    newCatInput?.addEventListener('input', () => {
        if (dynamicTitle) dynamicTitle.textContent = newCatInput.value.trim() || 'New Category';
        syncCatalogGroups();
    });

    newProductInput?.addEventListener('input', syncLivePreviewCard);
    quantityInput?.addEventListener('input', syncLivePreviewCard);

    const populateFormForEdit = (card) => {
        if (!card) return;
        const productId = card.dataset.productId;
        const catId = card.dataset.categoryId;
        const productName = card.dataset.productName;
        const quantity = card.dataset.quantity || '';
        const batchNo = card.dataset.batchNumber || '';
        const expiry = card.dataset.expiry || '';
        const notes = card.dataset.notes || '';
        let ingredients = [];
        try {
            ingredients = JSON.parse(card.dataset.ingredients || '[]');
        } catch (e) {
            ingredients = [];
        }

        // Open drawer if closed
        const workspace = document.getElementById('production-workspace');
        if (workspace && !workspace.classList.contains('production-workspace-open')) {
            workspace.classList.add('production-workspace-open');
        }

        // Set active card highlight
        document.querySelectorAll('.production-product-card').forEach((c) => {
            c.classList.remove('production-card-active');
        });
        card.classList.add('production-card-active');

        // Set hidden edit ID
        if (editingProductIdInput) {
            editingProductIdInput.value = productId;
        }

        // Update Drawer Header to Edit Mode
        if (panelTitle) panelTitle.textContent = `Edit Recipe: ${productName}`;
        if (panelSubtitle) panelSubtitle.textContent = `Modify formulation recipe ingredients (for 1 unit) for this product.`;
        if (editBadge) editBadge.style.display = 'inline-block';
        if (submitLabel) submitLabel.textContent = 'Save Product & Recipe';

        // Set category
        if (catId && categorySelect) {
            showNewCategoryMode(false);
            categorySelect.value = catId;
            filterProductsByCategory();
            syncCatalogGroups();
        }

        // Set product
        showNewProductMode(false);
        if (productSelect) {
            productSelect.value = productId;
            updateProductUnit();
        }
        if (newProductInput) {
            newProductInput.value = productName;
        }

        // Set goods classification checkbox
        const pType = card.dataset.productType || '';
        if (pType === 'semi_finished_good' || pType === 'semi_finished') {
            if (semiFinishedGoodCheckbox) semiFinishedGoodCheckbox.checked = true;
            if (finishedGoodCheckbox) finishedGoodCheckbox.checked = false;
        } else {
            if (finishedGoodCheckbox) finishedGoodCheckbox.checked = true;
            if (semiFinishedGoodCheckbox) semiFinishedGoodCheckbox.checked = false;
        }

        // Set units (quantity) - Recipe is always defined per 1 unit
        if (quantityInput) {
            quantityInput.value = '1';
        }

        // Set batch number
        if (batchInput) {
            batchInput.value = batchNo;
        }

        // Set expiry date
        if (expiryInput) {
            expiryInput.value = expiry;
        }

        // Set notes
        if (noteInput) {
            noteInput.value = notes;
        }

        // Populate ingredients
        if (ingredientsContainer && template) {
            ingredientsContainer.innerHTML = '';
            if (Array.isArray(ingredients) && ingredients.length > 0) {
                ingredients.forEach((ing, index) => {
                    const row = template.content.firstElementChild.cloneNode(true);
                    row.querySelectorAll('[data-name]').forEach((field) => {
                        field.name = `ingredients[${index}][${field.dataset.name}]`;
                        field.removeAttribute('data-name');
                    });
                    const matSelect = row.querySelector('[name$="[material_id]"]');
                    const qtyField = row.querySelector('[name$="[quantity]"]');
                    const unitField = row.querySelector('[name$="[unit]"]');

                    if (matSelect) matSelect.value = ing.material_id;
                    if (qtyField) qtyField.value = ing.quantity !== undefined && ing.quantity !== '' ? Number(ing.quantity).toFixed(2) : '';
                    if (unitField && ing.unit) unitField.value = ing.unit;

                    ingredientsContainer.appendChild(row);
                    updateRowStock(row);
                });
            }
            updateIngredientsEmptyHint();
        }

        // Smooth scroll panel into view if needed
        document.getElementById('production-create-panel')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    };

    const resetFormToCreateMode = () => {
        // Clear active card
        document.querySelectorAll('.production-product-card').forEach((c) => {
            c.classList.remove('production-card-active');
        });

        // Reset editing ID
        if (editingProductIdInput) editingProductIdInput.value = '';

        // Reset Drawer Header
        if (panelTitle) panelTitle.textContent = 'Add Product / Recipe';
        if (panelSubtitle) panelSubtitle.textContent = 'Define product details and formulation recipe for 1 unit.';
        if (editBadge) editBadge.style.display = 'none';
        if (submitLabel) submitLabel.textContent = 'Save Product & Recipe';

        // Reset fields
        showNewCategoryMode(false);
        showNewProductMode(false);
        if (categorySelect) categorySelect.value = '';
        if (productSelect) productSelect.value = '';
        if (quantityInput) quantityInput.value = '1';
        if (finishedGoodCheckbox) finishedGoodCheckbox.checked = true;
        if (semiFinishedGoodCheckbox) semiFinishedGoodCheckbox.checked = false;
        if (batchInput) batchInput.value = defaultBatchNo;
        if (expiryInput) expiryInput.value = '';
        if (noteInput) noteInput.value = '';

        // Reset ingredients
        if (ingredientsContainer) ingredientsContainer.innerHTML = '';
        updateIngredientsEmptyHint();
        updateProductUnit();
        syncCatalogGroups();
    };

    productionForm.addEventListener('production:reset-create', resetFormToCreateMode);

    const closeAllProductMenus = () => {
        document.querySelectorAll('[data-product-dropdown]').forEach((dropdown) => {
            dropdown.hidden = true;
        });
        document.querySelectorAll('[data-product-menu-toggle]').forEach((btn) => {
            btn.setAttribute('aria-expanded', 'false');
        });
    };

    document.addEventListener('click', (e) => {
        if (!e.target.closest('[data-product-menu-wrap]')) {
            closeAllProductMenus();
        }
    });

    // Click on product card or its 3-dots menu in the left list
    document.getElementById('production-category-groups')?.addEventListener('click', (e) => {
        const menuToggle = e.target.closest('[data-product-menu-toggle]');
        if (menuToggle) {
            e.preventDefault();
            e.stopPropagation();
            const wrap = menuToggle.closest('[data-product-menu-wrap]');
            const dropdown = wrap?.querySelector('[data-product-dropdown]');
            const isOpen = dropdown && !dropdown.hidden;
            closeAllProductMenus();
            if (dropdown && !isOpen) {
                dropdown.hidden = false;
                menuToggle.setAttribute('aria-expanded', 'true');
            }
            return;
        }

        const editBtn = e.target.closest('[data-product-edit]');
        if (editBtn) {
            e.preventDefault();
            e.stopPropagation();
            closeAllProductMenus();
            const card = editBtn.closest('[data-product-id]');
            if (card) populateFormForEdit(card);
            return;
        }

        const deleteBtn = e.target.closest('[data-product-delete]') || e.target.closest('.production-delete-form');
        if (deleteBtn) {
            e.stopPropagation();
            closeAllProductMenus();
            return;
        }

        const card = e.target.closest('[data-product-id]');
        if (!card) return;
        closeAllProductMenus();
        populateFormForEdit(card);
    });

    // Ingredients empty hint updater
    const updateIngredientsEmptyHint = () => {
        if (!ingredientsContainer || !emptyHint) return;
        const rows = ingredientsContainer.querySelectorAll('[data-ingredient-row]');
        emptyHint.style.display = rows.length === 0 ? 'flex' : 'none';
    };

    filterProductsByCategory();
    syncCatalogGroups();
    updateIngredientsEmptyHint();

    // Initialize any existing rows and handle ?edit=<product_id>
    const editParamId = new URLSearchParams(window.location.search).get('edit');
    if (editParamId) {
        const targetCard = document.querySelector(`.production-product-card[data-product-id="${CSS.escape(editParamId)}"]`);
        if (targetCard) {
            populateFormForEdit(targetCard);
        }
    }
    ingredientsContainer?.querySelectorAll('[data-ingredient-row]').forEach((row) => {
        updateRowStock(row);
    });

    // Delegation for ingredient change and add/remove
    productionForm.addEventListener('change', (e) => {
        if (e.target.matches('[data-ingredient-select]')) {
            const row = e.target.closest('[data-ingredient-row]');
            if (row) updateRowStock(row);
        }
    });

    productionForm.addEventListener('click', (e) => {
        const addBtn = e.target.closest('[data-add-ingredient]');
        if (addBtn && ingredientsContainer && template) {
            const indexes = [...ingredientsContainer.querySelectorAll('[name]')]
                .map((field) => Number(field.name.match(/\[(\d+)\]/)?.[1] ?? -1));
            const newIndex = Math.max(-1, ...indexes) + 1;

            const newRow = template.content.firstElementChild.cloneNode(true);
            newRow.querySelectorAll('[data-name]').forEach((field) => {
                field.name = `ingredients[${newIndex}][${field.dataset.name}]`;
                field.removeAttribute('data-name');
            });

            ingredientsContainer.appendChild(newRow);
            updateRowStock(newRow);
            updateIngredientsEmptyHint();
            return;
        }

        const removeBtn = e.target.closest('[data-remove-ingredient]');
        if (removeBtn && ingredientsContainer) {
            const row = removeBtn.closest('[data-ingredient-row]');
            row?.remove();
            updateIngredientsEmptyHint();
        }
    });
}

// Production Workspace Open / Close Toggle Handling
const productionWorkspace = document.getElementById('production-workspace');
if (productionWorkspace) {
    const createToggleBtn = document.getElementById('production-create-toggle');
    const panelCloseBtn = document.getElementById('production-panel-close-btn');
    const formCancelBtn = document.getElementById('production-form-cancel-btn');

    createToggleBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('production-form')?.dispatchEvent(new CustomEvent('production:reset-create'));
        productionWorkspace.classList.add('production-workspace-open');
        document.getElementById('production-create-panel')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    panelCloseBtn?.addEventListener('click', () => {
        document.getElementById('production-form')?.dispatchEvent(new CustomEvent('production:reset-create'));
        productionWorkspace.classList.remove('production-workspace-open');
    });

    formCancelBtn?.addEventListener('click', () => {
        document.getElementById('production-form')?.dispatchEvent(new CustomEvent('production:reset-create'));
        productionWorkspace.classList.remove('production-workspace-open');
    });
}


// Multi-Item Supplier Purchase Order Form Management
const purchaseOrderForm = document.getElementById('purchase-order-form');
if (purchaseOrderForm) {
    const linesList = document.getElementById('purchase-lines');
    const lineTemplate = document.getElementById('purchase-lines-template');
    const supplierSelect = purchaseOrderForm.querySelector('[data-purchase-supplier]');
    const vatInput = purchaseOrderForm.querySelector('[data-purchase-vat]');
    const itemCountBadge = purchaseOrderForm.querySelector('[data-purchase-item-count]');
    const addSupplierItemsBtn = purchaseOrderForm.querySelector('[data-add-supplier-items]');
    const summaryItemsEl = purchaseOrderForm.querySelector('[data-summary-items]');
    const summarySubtotalEl = purchaseOrderForm.querySelector('[data-summary-subtotal]');
    const summaryVatRateEl = purchaseOrderForm.querySelector('[data-summary-vat-rate]');
    const summaryVatEl = purchaseOrderForm.querySelector('[data-summary-vat]');
    const summaryTotalEl = purchaseOrderForm.querySelector('[data-summary-total]');
    const currencySymbol = purchaseOrderForm.dataset.currencySymbol || '$';
    const currencyCode = purchaseOrderForm.dataset.currencyCode || 'USD';

    const syncRowMaterialDetails = (row, autoFillCost = false) => {
        if (!row) return;
        const matSelect = row.querySelector('[data-purchase-material]');
        const unitPill = row.querySelector('[data-purchase-unit]');
        const costInput = row.querySelector('[data-purchase-cost]');
        if (!(matSelect instanceof HTMLSelectElement)) return;

        const selectedOpt = matSelect.selectedOptions?.[0];
        const unit = selectedOpt?.dataset?.unit || '';
        const defaultCost = selectedOpt?.dataset?.cost || '';

        if (unitPill) {
            unitPill.textContent = unit ? `(${unit})` : '';
        }
        if (autoFillCost && costInput instanceof HTMLInputElement && (!costInput.value || Number(costInput.value) === 0) && defaultCost !== '') {
            costInput.value = Number(defaultCost).toFixed(2);
        }
    };

    const syncSupplierMaterials = () => {
        const selectedSupplierId = supplierSelect?.value || '';
        let supplierMatchCount = 0;

        const sampleSelect = linesList?.querySelector('[data-purchase-material]') || lineTemplate?.content?.querySelector('[data-purchase-material]');
        if (sampleSelect) {
            [...sampleSelect.options].forEach((opt) => {
                if (opt.value && selectedSupplierId && String(opt.dataset.supplierId) === String(selectedSupplierId)) {
                    supplierMatchCount++;
                }
            });
        }

        if (addSupplierItemsBtn) {
            addSupplierItemsBtn.hidden = !(selectedSupplierId && supplierMatchCount > 0);
        }

        linesList?.querySelectorAll('[data-purchase-material]').forEach((select) => {
            [...select.options].forEach((opt) => {
                if (!opt.value) return;
                const baseLabel = opt.dataset.baseLabel || opt.textContent.replace(/^★\s*/, '').trim();
                const isPreferred = selectedSupplierId && String(opt.dataset.supplierId) === String(selectedSupplierId);
                opt.textContent = isPreferred ? `★ ${baseLabel}` : baseLabel;
            });
        });
    };

    const recalculatePurchaseOrder = () => {
        if (!linesList) return;
        const rows = [...linesList.querySelectorAll('[data-line-row]')];
        const count = rows.length;
        let totalQty = 0;
        let subtotal = 0;

        rows.forEach((row, idx) => {
            const badge = row.querySelector('[data-line-index]');
            if (badge) badge.textContent = `#${idx + 1}`;

            syncRowMaterialDetails(row, false);

            const qtyInput = row.querySelector('[data-purchase-qty]');
            const costInput = row.querySelector('[data-purchase-cost]');
            const lineTotalEl = row.querySelector('[data-purchase-line-total]');

            const qty = parseFloat(qtyInput?.value || '0') || 0;
            const cost = parseFloat(costInput?.value || '0') || 0;
            const lineTotal = qty * cost;

            totalQty += qty;
            subtotal += lineTotal;

            if (lineTotalEl) {
                lineTotalEl.textContent = `${currencySymbol}${lineTotal.toFixed(2)}`;
            }
        });

        const vatRate = parseFloat(vatInput?.value || '0') || 0;
        const vatAmount = subtotal * (vatRate / 100);
        const grandTotal = subtotal + vatAmount;

        if (itemCountBadge) {
            itemCountBadge.textContent = `${count} ${count === 1 ? 'item' : 'items'}`;
        }
        if (summaryItemsEl) {
            summaryItemsEl.textContent = `${count} ${count === 1 ? 'item' : 'items'} · ${totalQty.toFixed(2)} total qty`;
        }
        if (summarySubtotalEl) {
            summarySubtotalEl.textContent = `${currencySymbol}${subtotal.toFixed(2)}`;
        }
        if (summaryVatRateEl) {
            summaryVatRateEl.textContent = vatRate.toFixed(2);
        }
        if (summaryVatEl) {
            summaryVatEl.textContent = `${currencySymbol}${vatAmount.toFixed(2)}`;
        }
        if (summaryTotalEl) {
            summaryTotalEl.textContent = `${currencySymbol}${grandTotal.toFixed(2)} ${currencyCode}`;
        }
    };

    purchaseOrderForm.addEventListener('purchase:lines-changed', (e) => {
        if (e.detail?.addedRow) {
            syncSupplierMaterials();
            syncRowMaterialDetails(e.detail.addedRow, false);
        }
        recalculatePurchaseOrder();
    });

    purchaseOrderForm.addEventListener('change', (e) => {
        if (e.target.matches('[data-purchase-supplier]')) {
            syncSupplierMaterials();
            return;
        }
        if (e.target.matches('[data-purchase-material]')) {
            const row = e.target.closest('[data-line-row]');
            syncRowMaterialDetails(row, true);
            recalculatePurchaseOrder();
            return;
        }
        if (e.target.matches('[data-purchase-qty], [data-purchase-cost], [data-purchase-vat]')) {
            recalculatePurchaseOrder();
        }
    });

    purchaseOrderForm.addEventListener('input', (e) => {
        if (e.target.matches('[data-purchase-qty], [data-purchase-cost], [data-purchase-vat]')) {
            recalculatePurchaseOrder();
        }
    });

    addSupplierItemsBtn?.addEventListener('click', () => {
        const selectedSupplierId = supplierSelect?.value || '';
        if (!selectedSupplierId || !linesList || !lineTemplate) return;

        const templateSelect = lineTemplate.content.querySelector('[data-purchase-material]');
        if (!templateSelect) return;

        const supplierMaterialOptions = [...templateSelect.options].filter(
            (opt) => opt.value && String(opt.dataset.supplierId) === String(selectedSupplierId)
        );
        if (supplierMaterialOptions.length === 0) return;

        const existingMaterialIds = new Set(
            [...linesList.querySelectorAll('[data-purchase-material]')]
                .map((sel) => String(sel.value))
                .filter(Boolean)
        );

        supplierMaterialOptions.forEach((opt) => {
            if (existingMaterialIds.has(String(opt.value))) return;

            // Check if the first row is currently blank and reuse it
            const emptyRow = [...linesList.querySelectorAll('[data-line-row]')].find((r) => {
                const sel = r.querySelector('[data-purchase-material]');
                return sel && !sel.value;
            });

            if (emptyRow) {
                const sel = emptyRow.querySelector('[data-purchase-material]');
                if (sel) sel.value = opt.value;
                syncRowMaterialDetails(emptyRow, true);
                existingMaterialIds.add(String(opt.value));
            } else {
                const indexes = [...linesList.querySelectorAll('[name]')]
                    .map((field) => Number(field.name.match(/\[(\d+)\]/)?.[1] ?? -1));
                const nextIndex = Math.max(-1, ...indexes) + 1;
                const newRow = lineTemplate.content.firstElementChild.cloneNode(true);
                newRow.querySelectorAll('[data-name]').forEach((field) => {
                    field.name = `items[${nextIndex}][${field.dataset.name}]`;
                    field.removeAttribute('data-name');
                });
                const sel = newRow.querySelector('[data-purchase-material]');
                if (sel) sel.value = opt.value;
                linesList.append(newRow);
                syncRowMaterialDetails(newRow, true);
                existingMaterialIds.add(String(opt.value));
            }
        });

        syncSupplierMaterials();
        recalculatePurchaseOrder();
    });

    syncSupplierMaterials();
    recalculatePurchaseOrder();
}


// Record Production Drawer in Finished Goods & Semi Finished Goods Modules
const recordProdDrawer = document.getElementById('record-production-drawer');
if (recordProdDrawer) {
    const backdrop = document.getElementById('record-production-backdrop');
    const productSelect = recordProdDrawer.querySelector('[data-record-product-select]');
    const qtyInput = recordProdDrawer.querySelector('[data-record-qty-input]');
    const locationSelect = recordProdDrawer.querySelector('[data-record-location-select]');
    const unitSuffix = document.getElementById('record-unit-suffix');
    const categoryPill = document.getElementById('record-prod-category-pill');
    const scaleBadge = document.getElementById('record-recipe-scale-badge');
    const recipeEmpty = document.getElementById('record-recipe-empty');
    const recipeList = document.getElementById('record-recipe-list');

    let productsMap = {};
    try {
        productsMap = JSON.parse(recordProdDrawer.dataset.productsMap || '{}');
    } catch (e) {
        productsMap = {};
    }

    const escapeHtml = (str) => String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

    const syncRecordProductionRecipe = (resetCustomQuantities = true) => {
        const selectedId = productSelect?.value || '';
        const product = productsMap[selectedId] || null;
        const qty = Math.max(0, parseFloat(qtyInput?.value || '0') || 0);

        if (!product) {
            if (unitSuffix) unitSuffix.textContent = 'Units';
            if (categoryPill) categoryPill.hidden = true;
            if (scaleBadge) scaleBadge.textContent = `× ${qty.toFixed(2)} units`;
            if (recipeEmpty) {
                recipeEmpty.hidden = false;
                recipeEmpty.innerHTML = 'Select a predefined product above to load its 1-unit formulation recipe and check raw material stock availability.';
            }
            if (recipeList) {
                recipeList.hidden = true;
                recipeList.innerHTML = '';
            }
            return;
        }

        if (unitSuffix) unitSuffix.textContent = product.unit || 'Units';
        if (categoryPill) {
            categoryPill.textContent = product.category || 'Uncategorized';
            categoryPill.hidden = false;
        }
        if (scaleBadge) {
            scaleBadge.textContent = `× ${qty.toFixed(2)} ${product.unit || 'units'}`;
        }
        if (resetCustomQuantities && locationSelect && product.location && !locationSelect.value) {
            locationSelect.value = product.location;
        }

        const recipes = Array.isArray(product.recipes) ? product.recipes : [];
        if (recipes.length === 0) {
            if (recipeEmpty) {
                recipeEmpty.hidden = false;
                recipeEmpty.innerHTML = `No raw material ingredients are pre-defined for <strong>${escapeHtml(product.name)}</strong>. Production will record the output batch directly.`;
            }
            if (recipeList) {
                recipeList.hidden = true;
                recipeList.innerHTML = '';
            }
            return;
        }

        if (recipeEmpty) recipeEmpty.hidden = true;
        if (recipeList) {
            recipeList.hidden = false;
            recipeList.innerHTML = recipes.map((ing, idx) => {
                const reqQty = Number((ing.per_unit_qty || 0) * (qty || 0));
                const avail = Number(ing.available_stock || 0);
                const sufficient = avail + 0.0001 >= reqQty;
                return `
                    <div class="record-recipe-row ${sufficient ? 'is-sufficient' : 'is-short'}" data-record-ing-row>
                        <input type="hidden" name="ingredients[${idx}][material_id]" value="${ing.material_id}">
                        <div class="record-recipe-info">
                            <strong>${escapeHtml(ing.name)}</strong>
                            <small>${ing.code ? escapeHtml(ing.code) + ' · ' : ''}1-unit recipe: ${Number(ing.per_unit_qty).toFixed(2)} ${escapeHtml(ing.unit)}</small>
                        </div>
                        <div class="record-recipe-Availability">
                            <span class="record-stock-pill ${sufficient ? 'stock-ok' : 'stock-low'}" data-ing-avail="${avail}" data-ing-unit="${escapeHtml(ing.unit)}">
                                ${sufficient ? '✓' : '⚠'} Stock: ${avail.toFixed(2)} ${escapeHtml(ing.unit)}
                            </span>
                        </div>
                        <div class="record-recipe-qty-wrap">
                            <input type="number"
                                   name="ingredients[${idx}][quantity]"
                                   step="0.01"
                                   min="0.01"
                                   value="${reqQty.toFixed(2)}"
                                   data-per-unit="${ing.per_unit_qty}"
                                   data-record-ing-qty
                                   required>
                            <span class="record-ing-unit">${escapeHtml(ing.unit)}</span>
                        </div>
                    </div>
                `;
            }).join('');
        }
    };

    const openRecordDrawer = (productId = '') => {
        if (productId && productSelect) {
            productSelect.value = String(productId);
            if (qtyInput && (!qtyInput.value || Number(qtyInput.value) <= 0)) {
                qtyInput.value = '1.00';
            }
        }
        syncRecordProductionRecipe(true);
        recordProdDrawer.classList.add('is-open');
        backdrop?.classList.add('is-open');
        productSelect?.focus();
    };

    const closeRecordDrawer = () => {
        recordProdDrawer.classList.remove('is-open');
        backdrop?.classList.remove('is-open');
    };

    document.addEventListener('click', (e) => {
        const openBtn = e.target.closest('[data-open-record-production]');
        if (openBtn) {
            e.preventDefault();
            openRecordDrawer('');
            return;
        }

        const rowProduceBtn = e.target.closest('[data-produce-product-id]');
        if (rowProduceBtn) {
            e.preventDefault();
            openRecordDrawer(rowProduceBtn.dataset.produceProductId || '');
            return;
        }

        const closeBtn = e.target.closest('[data-close-record-production]');
        if (closeBtn) {
            e.preventDefault();
            closeRecordDrawer();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && recordProdDrawer.classList.contains('is-open')) {
            closeRecordDrawer();
        }
    });

    productSelect?.addEventListener('change', () => {
        syncRecordProductionRecipe(true);
    });

    qtyInput?.addEventListener('input', () => {
        const qty = Math.max(0, parseFloat(qtyInput.value || '0') || 0);
        const selectedId = productSelect?.value || '';
        const product = productsMap[selectedId] || null;
        if (scaleBadge) {
            scaleBadge.textContent = `× ${qty.toFixed(2)} ${product?.unit || 'units'}`;
        }
        recipeList?.querySelectorAll('[data-record-ing-row]').forEach((row) => {
            const ingInput = row.querySelector('[data-record-ing-qty]');
            const stockPill = row.querySelector('[data-ing-avail]');
            if (ingInput instanceof HTMLInputElement) {
                const perUnit = parseFloat(ingInput.dataset.perUnit || '0') || 0;
                const reqQty = perUnit * qty;
                ingInput.value = reqQty.toFixed(2);
                if (stockPill) {
                    const avail = parseFloat(stockPill.dataset.ingAvail || '0') || 0;
                    const unit = stockPill.dataset.ingUnit || '';
                    const sufficient = avail + 0.0001 >= reqQty;
                    row.classList.toggle('is-sufficient', sufficient);
                    row.classList.toggle('is-short', !sufficient);
                    stockPill.classList.toggle('stock-ok', sufficient);
                    stockPill.classList.toggle('stock-low', !sufficient);
                    stockPill.textContent = `${sufficient ? '✓' : '⚠'} Stock: ${avail.toFixed(2)} ${unit}`;
                }
            }
        });
    });

    syncRecordProductionRecipe(false);
}
