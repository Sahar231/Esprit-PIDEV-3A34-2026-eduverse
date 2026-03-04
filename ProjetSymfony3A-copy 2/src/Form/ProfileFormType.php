<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class , [
            'constraints' => [
                new NotBlank([
                    'message' => 'Please enter your full name',
                ]),
            ],
        ])
            ->add('email', TextType::class , [
            'disabled' => true,
        ])
            ->add('jobTitle', TextType::class, [
                'required' => false,
            ])
            ->add('bio', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, [
                'required' => false,
            ])
            ->add('picture', TextType::class, [
                'required' => false,
                'label' => 'Profile Picture URL'
            ])
            ->add('isTwoFactorEnabled', CheckboxType::class, [
                'label' => 'Activer l\'authentification à deux facteurs',
                'required' => false,
            ])
            ->add('phoneNumber', TelType::class, [
                'label' => 'Numéro de téléphone (format international ex: +216...)',
                'required' => false,
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class ,
        ]);
    }
}
