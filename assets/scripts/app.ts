window.addEventListener('load', () => {
    const banner = document.getElementById('ref-banner');

    if (!banner) {
        return;
    }

    const hashParams = new URLSearchParams(window.location.hash.slice(1));
    const ref: string|null = hashParams.get('ref');

    if (ref) {
        const name = document.createElement('strong');
        name.textContent = ref;
        banner.replaceChildren(name, document.createTextNode(' pense que cette catégorie va te plaire !'));
    }

});
