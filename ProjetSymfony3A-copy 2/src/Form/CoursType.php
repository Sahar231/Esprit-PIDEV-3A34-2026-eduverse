<?php

namespace App\Form;

use App\Entity\Chapitre;
use App\Entity\Cours;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Course Title',
                'attr' => [
                    'placeholder' => 'Enter course title (start with uppercase)',
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Course Description',
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Enter detailed course description (start with uppercase)',
                    'class' => 'form-control'
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Course Content',
                'attr' => [
                    'rows' => 8,
                    'placeholder' => 'Enter course content (start with uppercase, minimum 50 characters)',
                    'class' => 'form-control'
                ]
            ])
            ->add('category', TextType::class, [
                'label' => 'Category',
                'attr' => [
                    'placeholder' => 'e.g., Programming, Design, Management',
                    'class' => 'form-control'
                ]
            ])
            ->add('level', ChoiceType::class, [
                'label' => 'Course Level',
                'choices' => [
                    'Beginner' => 'beginner',
                    'Intermediate' => 'intermediate',
                    'Advanced' => 'advanced',
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('courseImage', FileType::class, [
                'label' => 'Course Image (JPG, PNG)',
                'required' => false,
                'attr' => [
                    'accept' => 'image/jpeg,image/png',
                    'class' => 'form-control'
                ]
            ])
            ->add('courseFile', FileType::class, [
                'label' => 'Course PDF File',
                'required' => false,
                'attr' => [
                    'accept' => 'application/pdf',
                    'class' => 'form-control'
                ]
            ])
            ->add('courseVideo', FileType::class, [
                'label' => 'Course Video (MP4, WebM)',
                'required' => false,
                'attr' => [
                    'accept' => 'video/mp4,video/webm',
                    'class' => 'form-control'
                ]
            ])
            ->add('chapitres', CollectionType::class, [
                'entry_type' => ChapitreType::class,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Create Course',
                'attr' => ['class' => 'btn btn-primary']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cours::class,
        ]);
    }
}
