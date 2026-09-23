<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Politique de mot de passe unique de l'application (CNIL 2022-100 / NIST SP 800-63B rév. 4) :
 * - longueur plutôt que composition : aucune majuscule/chiffre/caractère spécial imposé ;
 * - entropie estimée >= 80 bits (seuil CNIL pour un mot de passe seul) ;
 * - refus des mots de passe ayant fuité (liste de blocage exigée par le NIST) ;
 * - refus des mots de passe dérivés de l'email ou du pseudo.
 *
 * Pas de NotBlank ici : null signifie « pas de changement de mot de passe ».
 * Le caractère obligatoire se déclare là où il a du sens (formulaire d'inscription, de réinitialisation…).
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
final class PasswordPolicy extends Assert\Compound
{
    public const MIN_LENGTH = 12;
    public const MAX_LENGTH = 4096;

    protected function getConstraints(array $options): array
    {
        // Sequentially : un seul message à la fois, le plus actionnable d'abord
        // (et pas d'appel à Have I Been Pwned pour un mot de passe déjà trop court).
        return [
            new Assert\Sequentially([
                new Assert\Length(
                    min: self::MIN_LENGTH,
                    max: self::MAX_LENGTH,
                    minMessage: 'Votre mot de passe doit contenir au moins {{ limit }} caractères. Astuce : une phrase de plusieurs mots est facile à retenir et difficile à deviner.',
                    maxMessage: 'Votre mot de passe ne doit pas dépasser {{ limit }} caractères.',
                ),
                new PasswordNotSimilarToIdentity(),
                new Assert\NotCompromisedPassword(
                    message: 'Ce mot de passe apparaît dans des fuites de données connues : les attaquants l\'essaieront en premier. Choisissez-en un autre, par exemple une phrase de plusieurs mots sans rapport entre eux.',
                ),
                new Assert\PasswordStrength(
                    minScore: Assert\PasswordStrength::STRENGTH_MEDIUM,
                    message: 'Ce mot de passe est trop prévisible. Allongez-le ou variez davantage les caractères, par exemple avec une phrase de plusieurs mots.',
                ),
            ]),
        ];
    }
}
