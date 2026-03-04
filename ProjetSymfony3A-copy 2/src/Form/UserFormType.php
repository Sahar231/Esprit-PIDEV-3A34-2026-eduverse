<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class , [
            'constraints' => [
                new NotBlank(['message' => 'L\'adresse email est requise.']),
                new Email(['message' => 'L\'adresse email n\'est pas valide.']),
            ],
        ])
            ->add('fullName', TextType::class , [
            'constraints' => [
                new NotBlank(['message' => 'Le nom complet est requis.']),
            ],
        ])
            ->add('role', ChoiceType::class , [
            'choices' => [
                'Student' => 'Student',
                'Instructor' => 'Instructor',
            ],
            'constraints' => [
                new NotBlank(['message' => 'Veuillez sélectionner un rôle.']),
            ],
        ])
            ->add('isTwoFactorEnabled', CheckboxType::class, [
                'label' => 'Activer l\'authentification à deux facteurs',
                'required' => false,
            ])
            ->add('phoneNumber', TelType::class, [
                'label' => 'Numéro de téléphone (format international ex: +216...)',
                'required' => false,
            ]);

        if ($options['is_creation']) {
            $builder->add('plainPassword', PasswordType::class , [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un mot de passe.']),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit comporter au moins {{ limit }} caractères.',
                        'max' => 4096,
                    ]),
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_creation' => false,
        ]);
    }
}
