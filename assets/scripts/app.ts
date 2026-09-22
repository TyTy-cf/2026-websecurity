window.addEventListener('load', () => {
    const banner = document.getElementById('ref-banner');

    if (!banner) {
        return;
    }

    const hashParams = new URLSearchParams(window.location.hash.slice(1));
    const ref: string|null = hashParams.get('ref');
    console.log("test", ref );

    if (ref) {
        const strong = document.createElement('strong');
        strong.textContent= ref;
        //banner.innerHTML = `<strong>${ref}</strong> pense que cette catégorie va te plaire !`;
        banner.append(strong, ' pense que cette catégorie va te plaire !');
    }

});
