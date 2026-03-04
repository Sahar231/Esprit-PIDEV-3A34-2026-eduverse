<?php

namespace App\Form;

use App\Entity\Ressources;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RessourcesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Resource Title',
                'attr' => ['class' => 'form-control', 'placeholder' => 'E.g., Python Programming Book, Docker Software'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Resource title is required']),
                    new Assert\Length([
                        'min' => 3,
                        'minMessage' => 'Title must be at least 3 characters',
                        'max' => 255,
                        'maxMessage' => 'Title cannot exceed 255 characters'
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Describe what this resource is and why it is needed'],
                'constraints' => [
                    new Assert\Length([
                        'max' => 1000,
                        'maxMessage' => 'Description cannot exceed 1000 characters'
                    ])
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Resource Type',
                'choices' => [
                    'Textbook / Book' => 'textbook',
                    'Software / Tool' => 'software',
                    'Hardware' => 'hardware',
                    'Documentation' => 'documentation',
                    'Online Platform' => 'platform',
                    'Video Course' => 'video',
                    'Other' => 'other',
                ],
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Resource type is required'])
                ]
            ])
            ->add('url', UrlType::class, [
                'label' => 'URL (optional)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'https://example.com/resource'],
            ])
            ->add('cost', MoneyType::class, [
                'label' => 'Cost (optional)',
                'required' => false,
                'currency' => 'USD',
                'attr' => ['class' => 'form-control', 'placeholder' => '0.00'],
                'constraints' => [
                    new Assert\PositiveOrZero(['message' => 'Cost must be zero or positive'])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ressources::class,
        ]);
    }
}
