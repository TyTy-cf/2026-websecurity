<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

// Exercice 14 — la politique de mot de passe est écrite ici et nulle part ailleurs.
// Pas de règle « une majuscule, un chiffre, un caractère spécial » : le NIST les
// déconseille, elles donnent Password1! sans vraiment agrandir l'espace de recherche.
// 12 caractères parce qu'on est dans le cas CNIL à 50 bits, valable grâce au login
// throttling de l'exercice 9.
#[\Attribute]
class StrongPassword extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\NotBlank(
                message: 'Merci de saisir un mot de passe.',
            ),
            new Assert\Length(
                min: 12,
                max: 4096,
                minMessage: 'Votre mot de passe doit faire au moins {{ limit }} caractères. Une phrase facile à retenir fait très bien l\'affaire.',
                maxMessage: 'Votre mot de passe ne peut pas dépasser {{ limit }} caractères.',
            ),
            new Assert\PasswordStrength(
                minScore: Assert\PasswordStrength::STRENGTH_MEDIUM,
                message: 'Ce mot de passe est trop facile à deviner. Allongez-le ou rendez-le moins prévisible.',
            ),
            // Interroge Have I Been Pwned en k-anonymat : on n'envoie que les 5 premiers
            // caractères du SHA-1, le mot de passe ne sort jamais du serveur.
            new Assert\NotCompromisedPassword(
                message: 'Ce mot de passe apparaît dans une fuite de données connue, il faut en choisir un autre.',
            ),
        ];
    }
}
