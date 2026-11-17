<meta name="theme-color" content="#ffffff">
<script @cspNonce>
    try {
        const theme = localStorage.getItem('theme');
        document.documentElement.classList.toggle('dark', theme === 'dark' || (!theme && matchMedia('(prefers-color-scheme: dark)').matches));
    } catch (_) {
        document.documentElement.classList.toggle('dark', matchMedia('(prefers-color-scheme: dark)').matches);
    }
    document.querySelector('meta[name="theme-color"]').content = document.documentElement.classList.contains('dark') ? '#050e28' : '#ffffff';
</script>
