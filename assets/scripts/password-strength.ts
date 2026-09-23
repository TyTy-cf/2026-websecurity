/**
 * Indicateur de robustesse du mot de passe.
 *
 * Le score est calculé côté client avec le même algorithme que
 * Symfony\Component\Validator\Constraints\PasswordStrengthValidator::estimateStrength,
 * afin que la barre affiche « accepté » exactement quand le serveur validera.
 * (entropie = chars * log2(pool) + (length - chars) * log2(chars), sur les octets UTF-8)
 */

const MIN_SCORE = 3; // PasswordStrength::STRENGTH_STRONG
const MIN_LENGTH = 12; // contrainte Length

interface Level {
    label: string;
    color: string;
    width: number;
}

const LEVELS: Level[] = [
    { label: 'Très faible', color: '#dc3545', width: 20 },
    { label: 'Faible', color: '#fd7e14', width: 40 },
    { label: 'Moyen', color: '#ffc107', width: 60 },
    { label: 'Fort', color: '#28a745', width: 80 },
    { label: 'Très fort', color: '#198754', width: 100 },
];

function estimateStrength(password: string): number {
    const bytes = new TextEncoder().encode(password);
    const length = bytes.length;
    if (length === 0) {
        return 0;
    }

    const seen = new Set<number>();
    for (const byte of bytes) {
        seen.add(byte);
    }

    let control = 0;
    let digit = 0;
    let upper = 0;
    let lower = 0;
    let symbol = 0;
    let other = 0;

    for (const c of seen) {
        if (c < 32 || c === 127) {
            control = 33;
        } else if (c >= 48 && c <= 57) {
            digit = 10;
        } else if (c >= 65 && c <= 90) {
            upper = 26;
        } else if (c >= 97 && c <= 122) {
            lower = 26;
        } else if (c >= 128) {
            other = 128;
        } else {
            symbol = 33;
        }
    }

    const chars = seen.size;
    const pool = lower + upper + digit + symbol + control + other;
    const entropy = chars * Math.log2(pool) + (length - chars) * Math.log2(chars);

    if (entropy >= 120) return 4; // très fort
    if (entropy >= 100) return 3; // fort -> seuil accepté par le serveur
    if (entropy >= 80) return 2; // moyen
    if (entropy >= 60) return 1; // faible
    return 0; // très faible
}

window.addEventListener('load', () => {
    const input = document.getElementById('registration_plainPassword_first') as HTMLInputElement | null
        ?? document.querySelector<HTMLInputElement>('input[type="password"]');
    const wrapper = document.getElementById('password-strength');

    if (!input || !wrapper) {
        return;
    }

    const bar = wrapper.querySelector<HTMLElement>('.progress-bar');
    const label = wrapper.querySelector<HTMLElement>('.js-strength-label');

    if (!bar || !label) {
        return;
    }

    input.addEventListener('input', () => {
        const value = input.value;

        if (value.length === 0) {
            wrapper.hidden = true;
            return;
        }
        wrapper.hidden = false;

        const score = estimateStrength(value);
        const level = LEVELS[score];

        bar.style.width = `${level.width}%`;
        bar.style.backgroundColor = level.color;
        bar.setAttribute('aria-valuenow', String(score));

        const messages: string[] = [level.label];
        if (value.length < MIN_LENGTH) {
            messages.push(`au moins ${MIN_LENGTH} caractères requis`);
        } else if (score < MIN_SCORE) {
            messages.push('trop faible pour être accepté');
        } else {
            messages.push('accepté ✓');
        }
        label.textContent = messages.join(' — ');
    });
});
