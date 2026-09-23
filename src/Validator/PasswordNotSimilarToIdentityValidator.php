<?php

namespace App\Validator;

use App\Entity\User;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class PasswordNotSimilarToIdentityValidator extends ConstraintValidator
{
    private const MIN_IDENTIFIER_LENGTH = 3;

    public function validate(#[\SensitiveParameter] mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PasswordNotSimilarToIdentity) {
            throw new UnexpectedTypeException($constraint, PasswordNotSimilarToIdentity::class);
        }

        $user = $this->context->getObject();

        if (null === $value || '' === $value || !$user instanceof User) {
            return;
        }

        $password = mb_strtolower($value);
        $email = mb_strtolower((string) $user->getEmail());

        $identifiers = [
            $email,
            strstr($email, '@', true) ?: '',
            mb_strtolower((string) $user->getNickname()),
        ];

        foreach ($identifiers as $identifier) {
            if (mb_strlen($identifier) >= self::MIN_IDENTIFIER_LENGTH && str_contains($password, $identifier)) {
                $this->context->buildViolation($constraint->message)->addViolation();

                return;
            }
        }
    }
}
