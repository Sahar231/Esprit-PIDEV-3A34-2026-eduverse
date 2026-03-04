<?php

namespace App\Form;

use App\Entity\Quizfor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class QuizforType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Quiz Title',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter quiz title'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Quiz title is required']),
                    new Assert\Length([
                        'min' => 3,
                        'minMessage' => 'Quiz title must be at least 3 characters',
                        'max' => 255,
                        'maxMessage' => 'Quiz title cannot exceed 255 characters'
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Brief description of the quiz'],
                'constraints' => [
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'Description cannot exceed 255 characters'
                    ])
                ]
            ])
            ->add('category', TextType::class, [
                'label' => 'Category',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'E.g., Math, Science, History'],
                'constraints' => [
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'Category cannot exceed 255 characters'
                    ])
                ]
            ])
            ->add('difficulty', ChoiceType::class, [
                'label' => 'Difficulty Level',
                'choices' => [
                    'Easy' => 'easy',
                    'Medium' => 'medium',
                    'Hard' => 'hard',
                ],
                'attr' => ['class' => 'form-control'],
                'required' => false
            ])
            ->add('total_score', NumberType::class, [
                'label' => 'Total Score',
                'scale' => 2,
                'attr' => ['class' => 'form-control', 'placeholder' => 'E.g., 100', 'step' => '0.01'],
                'constraints' => [
                    new Assert\PositiveOrZero(['message' => 'Total score must be zero or positive'])
                ]
            ])
            ->add('pass_score', NumberType::class, [
                'label' => 'Pass Score',
                'scale' => 2,
                'attr' => ['class' => 'form-control', 'placeholder' => 'E.g., 60', 'step' => '0.01'],
                'constraints' => [
                    new Assert\PositiveOrZero(['message' => 'Pass score must be zero or positive'])
                ],
                'required' => false
            ])
            ->add('total_questions', TextType::class, [
                'label' => 'Total Questions',
                'required' => false,
                'attr' => ['class' => 'form-control', 'readonly' => true, 'placeholder' => 'Auto-calculated from questions'],
                'mapped' => false,
                'data' => $options['data'] ? $options['data']->getTotalQuestions() : 0,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quizfor::class,
        ]);
    }
}
