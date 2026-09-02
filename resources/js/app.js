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

document.querySelectorAll('[data-copy-text]').forEach((button) => {
    button.addEventListener('click', async () => {
        const text = button.dataset.copyText;

        if (!text) {
            return;
        }

        try {
            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(text);
            } else {
                const input = document.createElement('textarea');
                input.value = text;
                input.setAttribute('readonly', '');
                input.style.position = 'fixed';
                input.style.opacity = '0';
                document.body.append(input);
                input.select();

                const copied = document.execCommand('copy');
                input.remove();

                if (!copied) {
                    throw new Error('Browser tidak mendukung penyalinan otomatis.');
                }
            }

            button.textContent = 'Tersalin';
            button.setAttribute('aria-label', `${button.dataset.copyLabel ?? 'Nomor rekening'} tersalin`);
        } catch {
            button.textContent = 'Gagal menyalin';
        }

        window.setTimeout(() => {
            button.textContent = 'Salin';
            button.setAttribute('aria-label', button.dataset.copyLabel ?? 'Salin nomor rekening');
        }, 1800);
    });
});
