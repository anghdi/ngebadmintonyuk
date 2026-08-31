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
