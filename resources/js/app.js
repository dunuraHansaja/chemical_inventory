import './bootstrap';

document.addEventListener('click', (event) => {
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
