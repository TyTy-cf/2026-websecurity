window.addEventListener('load', () => {
    const fileInputs = document.querySelectorAll<HTMLInputElement>('input[type="file"][accept]');

    fileInputs.forEach((input) => {
        const acceptedTypes = input.accept
            .split(',')
            .map((type) => type.trim())
            .filter(Boolean);

        input.addEventListener('change', () => {
            const file = input.files?.[0];

            if (!file || acceptedTypes.length === 0) {
                return;
            }

            if (!acceptedTypes.includes(file.type)) {
                input.value = '';
                alert('Fichier invalide : seules les images (JPEG, PNG, WEBP, GIF) sont acceptées.');
            }
        });
    });

    const banner = document.getElementById('ref-banner');

    if (!banner) {
        return;
    }

    const hashParams = new URLSearchParams(window.location.hash.slice(1));
    const ref: string|null = hashParams.get('ref');

    if (ref) {
        const strong = document.createElement('strong');
        strong.textContent = ref;
        banner.append(strong, ' pense que cette catégorie va te plaire !');
    }

});
