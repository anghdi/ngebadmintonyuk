export function installRequiredProfileDialog(documentObject) {
    const dialog = documentObject.querySelector('[data-profile-required]');
    if (!dialog) {
        return;
    }

    dialog.addEventListener('cancel', (event) => event.preventDefault());
    if (typeof dialog.showModal === 'function') {
        dialog.removeAttribute('open');
        dialog.showModal();
    }
}
