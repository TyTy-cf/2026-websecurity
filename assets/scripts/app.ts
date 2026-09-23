window.addEventListener('load', () => {
    setupPictureExtensionValidation();
    setupPasswordHelp();
    setupPasswordToggles();

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

type RuleState = 'pending' | 'valid' | 'invalid';

const STRENGTH_LEVELS = [
    {label: 'Très faible', className: 'bg-danger', width: 10},
    {label: 'Faible', className: 'bg-warning', width: 30},
    {label: 'Moyenne', className: 'bg-info', width: 60},
    {label: 'Forte', className: 'bg-success', width: 85},
    {label: 'Très forte', className: 'bg-success', width: 100},
];

const RULE_STATUS_TEXT: Record<RuleState, string> = {
    pending: 'à vérifier',
    valid: 'respectée',
    invalid: 'non respectée',
};

/**
 * Aide dynamique a la saisie du mot de passe du formulaire d'inscription.
 * Confort utilisateur uniquement : les contraintes qui font autorite sont
 * celles de RegistrationType (Length, PasswordStrength, Callback,
 * NotCompromisedPassword), revalidees cote serveur a la soumission.
 */
function setupPasswordHelp(): void {
    const help = document.getElementById('password-help');

    if (!help) {
        return;
    }

    const password = document.getElementById(help.dataset.passwordInput ?? '') as HTMLInputElement | null;
    const confirm = document.getElementById(help.dataset.confirmInput ?? '') as HTMLInputElement | null;

    if (!password || !confirm) {
        return;
    }

    const identityInputs = (help.dataset.identityInputs ?? '')
        .split(' ')
        .map((id) => document.getElementById(id) as HTMLInputElement | null)
        .filter((input): input is HTMLInputElement => input !== null);

    const minLength = Number(help.dataset.minLength);
    const maxLength = Number(help.dataset.maxLength);
    const minScore = Number(help.dataset.minScore);
    const meter = help.querySelector<HTMLElement>('[data-password-meter]');
    const strengthLabel = help.querySelector<HTMLElement>('[data-password-strength]');

    const rules = new Map<string, HTMLElement>();
    help.querySelectorAll<HTMLElement>('[data-password-rule]').forEach((item) => {
        const icon = document.createElement('span');
        icon.className = 'password-help__icon';
        icon.setAttribute('aria-hidden', 'true');

        const status = document.createElement('span');
        status.className = 'visually-hidden password-help__status';

        item.prepend(icon);
        item.append(status);
        rules.set(item.dataset.passwordRule ?? '', item);
    });

    const setRule = (name: string, state: RuleState): void => {
        const item = rules.get(name);

        if (!item) {
            return;
        }

        item.classList.toggle('text-success', state === 'valid');
        item.classList.toggle('text-danger', state === 'invalid');
        item.querySelector('.password-help__icon')!.textContent = state === 'valid' ? '✓' : state === 'invalid' ? '✗' : '•';
        item.querySelector('.password-help__status')!.textContent = ` (${RULE_STATUS_TEXT[state]})`;
    };

    const update = (): void => {
        const value = password.value;
        const typed = value.length > 0;
        const score = estimatePasswordStrength(value);
        const level = STRENGTH_LEVELS[score];

        if (meter) {
            meter.className = `progress-bar ${typed ? level.className : ''}`;
            meter.style.width = typed ? `${level.width}%` : '0%';
        }

        if (strengthLabel) {
            strengthLabel.textContent = typed ? level.label : '–';
        }

        const length = [...value].length;
        setRule('length', !typed ? 'pending' : length >= minLength && length <= maxLength ? 'valid' : 'invalid');
        setRule('strength', !typed ? 'pending' : score >= minScore ? 'valid' : 'invalid');
        setRule('identity', !typed ? 'pending' : containsIdentity(value, identityInputs) ? 'invalid' : 'valid');
        setRule('match', !typed || confirm.value === '' ? 'pending' : value === confirm.value ? 'valid' : 'invalid');
    };

    [password, confirm, ...identityInputs].forEach((input) => input.addEventListener('input', update));
    update();
}

/**
 * Meme logique que l'email / pseudo du Callback de RegistrationType :
 * pseudo et partie locale de l'email (3 caracteres minimum), insensible a la casse.
 */
function containsIdentity(password: string, identityInputs: HTMLInputElement[]): boolean {
    const lowered = password.toLowerCase();

    return identityInputs
        .map((input) => (input.type === 'email' ? input.value.split('@')[0] : input.value).toLowerCase())
        .some((identifier) => [...identifier].length >= 3 && lowered.includes(identifier));
}

/**
 * Portage de PasswordStrengthValidator::estimateStrength() (symfony/validator)
 * pour obtenir cote client exactement le meme score que cote serveur.
 * Le calcul se fait sur les octets UTF-8, comme strlen() / count_chars() en PHP.
 */
function estimatePasswordStrength(password: string): number {
    const bytes = new TextEncoder().encode(password);

    if (bytes.length === 0) {
        return 0;
    }

    const distinct = new Set(bytes);
    let control = 0, digit = 0, upper = 0, lower = 0, symbol = 0, other = 0;

    distinct.forEach((chr) => {
        if (chr < 32 || chr === 127) {
            control = 33;
        } else if (chr >= 48 && chr <= 57) {
            digit = 10;
        } else if (chr >= 65 && chr <= 90) {
            upper = 26;
        } else if (chr >= 97 && chr <= 122) {
            lower = 26;
        } else if (chr >= 128) {
            other = 128;
        } else {
            symbol = 33;
        }
    });

    const pool = lower + upper + digit + symbol + control + other;
    const chars = distinct.size;
    const entropy = chars * Math.log2(pool) + (bytes.length - chars) * Math.log2(chars);

    if (entropy >= 120) return 4;
    if (entropy >= 100) return 3;
    if (entropy >= 80) return 2;
    if (entropy >= 60) return 1;

    return 0;
}

const EYE_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>';
const EYE_OFF_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 19c-6.5 0-10-7-10-7a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="2" y1="2" x2="22" y2="22"/></svg>';

/**
 * Ajoute un bouton "oeil" a droite des champs [data-password-toggle] pour
 * afficher / masquer le mot de passe. Ajoute en JS (amelioration progressive) :
 * sans JavaScript, le champ reste un simple input password.
 */
function setupPasswordToggles(): void {
    document.querySelectorAll<HTMLInputElement>('input[data-password-toggle]').forEach((input) => {
        const group = document.createElement('div');
        group.className = 'input-group';
        input.replaceWith(group);
        group.append(input);

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-outline-secondary';
        group.append(button);

        const render = (visible: boolean): void => {
            button.innerHTML = visible ? EYE_OFF_ICON : EYE_ICON;
            button.setAttribute('aria-label', visible ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
            button.setAttribute('aria-pressed', String(visible));
            button.title = button.getAttribute('aria-label') ?? '';
        };

        button.addEventListener('click', () => {
            const visible = input.type === 'password';
            input.type = visible ? 'text' : 'password';
            render(visible);
            input.focus();
        });

        // Remasque avant l'envoi pour que le navigateur ne memorise pas le
        // mot de passe comme simple champ texte (autocompletion).
        input.form?.addEventListener('submit', () => {
            input.type = 'password';
            render(false);
        });

        render(false);
    });
}
