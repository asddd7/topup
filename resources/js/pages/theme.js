const html = document.documentElement;
const toggle = document.getElementById('themeToggle');
const icon = toggle ? toggle.querySelector('i') : null;

function getSystemTheme() {
    return (
        window.matchMedia &&
        window.matchMedia('(prefers-color-scheme: dark)').matches
    )
        ? 'dark'
        : 'light';
}

function getInitialTheme() {
    const currentTheme = html.getAttribute('data-theme');

    if (currentTheme === 'dark' || currentTheme === 'light') {
        return currentTheme;
    }

    const savedTheme = localStorage.getItem('topup-theme');

    if (savedTheme === 'dark' || savedTheme === 'light') {
        return savedTheme;
    }

    return getSystemTheme();
}

function applyTheme(theme) {
    html.setAttribute('data-theme', theme);
    html.style.colorScheme = theme;

    if (icon) {
        icon.classList.toggle('fa-moon', theme === 'light');
        icon.classList.toggle('fa-sun', theme === 'dark');
    }
}

const initialTheme = getInitialTheme();

applyTheme(initialTheme);

if (toggle) {
    toggle.addEventListener('click', () => {
        const currentTheme =
            html.getAttribute('data-theme') || 'light';

        const newTheme =
            currentTheme === 'dark'
                ? 'light'
                : 'dark';

        localStorage.setItem('topup-theme', newTheme);

        applyTheme(newTheme);
    });
}