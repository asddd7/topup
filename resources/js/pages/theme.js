const html = document.documentElement;
const toggle = document.getElementById('themeToggle');

if (toggle) {
const savedTheme = localStorage.getItem('topup-theme');

const systemDark =
    window.matchMedia &&
    window.matchMedia('(prefers-color-scheme: dark)').matches;

const initialTheme =
    savedTheme ||
    (systemDark ? 'dark' : 'light');

applyTheme(initialTheme);

toggle.addEventListener('click', () => {

    const currentTheme =
        html.getAttribute('data-theme') || 'light';

    const newTheme =
        currentTheme === 'dark'
            ? 'light'
            : 'dark';

    applyTheme(newTheme);

    localStorage.setItem(
        'topup-theme',
        newTheme
    );
});

}

function applyTheme(theme) {


html.setAttribute(
    'data-theme',
    theme
);

if (!toggle) {
    return;
}

const icon = toggle.querySelector('i');

if (theme === 'dark') {

    if (icon) {
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
    }

    toggle.setAttribute(
        'aria-label',
        'Gunakan mode terang'
    );

    toggle.setAttribute(
        'title',
        'Gunakan mode terang'
    );

} else {

    if (icon) {
        icon.classList.remove('fa-sun');
        icon.classList.add('fa-moon');
    }

    toggle.setAttribute(
        'aria-label',
        'Gunakan mode gelap'
    );

    toggle.setAttribute(
        'title',
        'Gunakan mode gelap'
    );
}

}
