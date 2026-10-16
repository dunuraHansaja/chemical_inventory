import './bootstrap';

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
        const template = document.getElementById(`${listId}-template`);
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
        return;
    }

    const removeButton = event.target.closest('[data-remove-line]');
    if (removeButton) {
        const row = removeButton.closest('[data-line-row]');
        const list = row?.parentElement;
        if (row && list && list.querySelectorAll('[data-line-row]').length > 1) row.remove();
    }
});

document.addEventListener('change', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLInputElement || target instanceof HTMLSelectElement)) return;

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
