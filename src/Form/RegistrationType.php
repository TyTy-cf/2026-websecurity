<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\PasswordStrength;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RegistrationType extends AbstractType
{
    /**
     * Regles partagees avec l'aide dynamique cote client (assets/scripts/app.ts),
     * transmises au template via des attributs data-*.
     */
    public const PASSWORD_MIN_LENGTH = 12;
    // Borne haute : evite un cout de hachage abusif (DoS) sur des chaines geantes.
    public const PASSWORD_MAX_LENGTH = 4096;
    public const PASSWORD_MIN_SCORE = PasswordStrength::STRENGTH_MEDIUM;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'register.email_label',
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('nickname', TextType::class, [
                'label' => 'register.nickname_label',
                'constraints' => [
                    new NotBlank(),
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'register.password_label',
                    'attr' => [
                        'class' => 'form-control',
                        'autocomplete' => 'new-password',
                        'minlength' => self::PASSWORD_MIN_LENGTH,
                        'maxlength' => self::PASSWORD_MAX_LENGTH,
                        'aria-describedby' => 'password-help',
                        'data-password-toggle' => true,
                    ],
                ],
                'second_options' => [
                    'label' => 'register.password_confirm_label',
                    'attr' => [
                        'class' => 'form-control',
                        'autocomplete' => 'new-password',
                        'data-password-toggle' => true,
                    ],
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'constraints' => [
                    new NotBlank(),
                    new Length(
                        min: self::PASSWORD_MIN_LENGTH,
                        max: self::PASSWORD_MAX_LENGTH,
                        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                        maxMessage: 'Le mot de passe ne peut pas dépasser {{ limit }} caractères.',
                    ),
                    new PasswordStrength(
                        minScore: self::PASSWORD_MIN_SCORE,
                        message: 'Le mot de passe est trop faible : allongez-le ou variez les types de caractères.',
                    ),
                    new Callback($this->validatePasswordDiffersFromIdentity(...)),
                    new NotCompromisedPassword(
                        message: 'Ce mot de passe apparaît dans une fuite de données connue, choisissez-en un autre.',
                    ),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'register.submit',
                'attr' => [
                    'class' => 'btn btn-success mt-2',
                ],
            ])
        ;
    }

    /**
     * Refuse un mot de passe contenant l'email (partie locale) ou le pseudo,
     * informations publiques ou facilement devinables.
     */
    public function validatePasswordDiffersFromIdentity(?string $password, ExecutionContextInterface $context): void
    {
        if (null === $password || '' === $password) {
            return;
        }

        $form = $context->getRoot();
        $email = (string) $form->get('email')->getData();
        $identifiers = [
            (string) $form->get('nickname')->getData(),
            explode('@', $email)[0],
        ];

        foreach ($identifiers as $identifier) {
            if (mb_strlen($identifier) >= 3 && false !== mb_stripos($password, $identifier)) {
                $context->buildViolation('Le mot de passe ne doit pas contenir votre pseudo ni votre adresse e-mail.')
                    ->addViolation();

                return;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'messages',
        ]);
    }
}
