<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class , [
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez saisir votre nom complet.',
                ]),
            ],
        ])
            ->add('email', TextType::class , [
            'constraints' => [
                new NotBlank([
                    'message' => 'L\'adresse email est requise.',
                ]),
                new \Symfony\Component\Validator\Constraints\Email([
                    'message' => 'L\'adresse email "{{ value }}" n\'est pas valide.',
                ]),
            ],
        ])
            ->add('role', ChoiceType::class , [
            'mapped' => false,
            'choices' => [
                'Student' => 'student',
                'Instructor' => 'instructor',
            ],
            'expanded' => true,
            'multiple' => false,
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez sélectionner un rôle.',
                ]),
            ],
        ])
            ->add('agreeTerms', CheckboxType::class , [
            'mapped' => false,
            'constraints' => [
                new IsTrue([
                    'message' => 'Vous devez accepter nos conditions d\'utilisation.',
                ]),
            ],
        ])
            ->add('plainPassword', PasswordType::class , [
            // instead of being set onto the object directly,
            // this is read and encoded in the controller
            'mapped' => false,
            'attr' => ['autocomplete' => 'new-password'],
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez saisir un mot de passe.',
                ]),
                new Length([
                    'min' => 6,
                    'minMessage' => 'Votre mot de passe doit comporter au moins {{ limit }} caractères.',
                    // max length allowed by Symfony for security reasons
                    'max' => 4096,
                ]),
                new PasswordStrength([
                    'minScore' => 4,
                    'message' => 'Le mot de passe doit contenir au moins 12 caractères avec des majuscules, des chiffres et des caractères spéciaux.',
                ]),
            ],
        ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
