window.addEventListener('load', () => {
    setupPictureExtensionValidation();

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

/**
 * Validation cote client de l'extension du fichier image du formulaire de topic.
 * Confort utilisateur uniquement : la validation qui fait autorite reste celle du
 * serveur (contrainte Assert\File dans TopicType), une verification JS etant
 * trivialement contournable.
 */
function setupPictureExtensionValidation(): void {
    const input = document.getElementById('topic_picture') as HTMLInputElement | null;

    if (!input) {
        return;
    }

    const allowedExtensions = ['jpg', 'jpeg', 'png'];
    const errorMessage = 'Merci de charger une image au format JPG, JPEG ou PNG.';

    input.addEventListener('change', () => {
        const file = input.files?.[0];

        if (!file) {
            return;
        }

        const extension = file.name.split('.').pop()?.toLowerCase() ?? '';

        if (!allowedExtensions.includes(extension)) {
            window.alert(errorMessage);
            input.value = '';
        }
    });
}
