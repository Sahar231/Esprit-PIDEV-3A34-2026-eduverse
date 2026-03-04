<?php

namespace App\Form;

use App\Entity\Chapitre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChapitreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Chapter Title',
                'attr' => [
                    'placeholder' => 'Enter chapter title (start with uppercase)',
                    'class' => 'form-control'
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Chapter Content',
                'attr' => [
                    'rows' => 8,
                    'placeholder' => 'Enter chapter content (minimum 10 characters)',
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chapitre::class,
        ]);
    }
}
