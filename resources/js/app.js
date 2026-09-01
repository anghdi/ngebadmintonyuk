const sidebar = document.querySelector('#sidebar');
const menu = document.querySelector('[data-sidebar-open]');

function toggleSidebar(open) {
    sidebar?.classList.toggle('open', open);
    document.body.classList.toggle('nav-open', open);
    menu?.setAttribute('aria-expanded', open ? 'true' : 'false');
}

menu?.addEventListener('click', () => toggleSidebar(true));
document.querySelectorAll('[data-sidebar-close]').forEach((button) => {
    button.addEventListener('click', () => toggleSidebar(false));
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        toggleSidebar(false);
    }
});

document.querySelectorAll('[data-member-select]').forEach((select) => {
    select.addEventListener('change', () => {
        const option = select.options[select.selectedIndex];
        const form = select.closest('form');

        if (!option?.value || !form) {
            return;
        }

        const nameInput = form.querySelector('input[name="name"]');
        const phoneInput = form.querySelector('input[name="phone"]');

        if (nameInput) {
            nameInput.value = option.dataset.memberName ?? '';
        }

        if (phoneInput && option.dataset.memberPhone) {
            phoneInput.value = option.dataset.memberPhone;
        }
    });
});
