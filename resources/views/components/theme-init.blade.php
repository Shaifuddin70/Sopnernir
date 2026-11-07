<script>
    (() => {
        const key = 'shopnernir-theme';
        const stored = localStorage.getItem(key);
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const theme = stored === 'light' || stored === 'dark'
            ? stored
            : (prefersDark ? 'dark' : 'light');

        document.documentElement.classList.toggle('dark', theme === 'dark');
        document.documentElement.style.colorScheme = theme;
        document.documentElement.dataset.theme = theme;
    })();
</script>
