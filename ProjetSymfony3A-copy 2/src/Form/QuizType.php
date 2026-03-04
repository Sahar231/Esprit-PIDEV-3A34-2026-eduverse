<?php

namespace App\Form;

use App\Entity\Quiz;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class QuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Quiz Title',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter quiz title (start with uppercase)'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Quiz title is required']),
                    new Assert\Length([
                        'min' => 3,
                        'minMessage' => 'Quiz title must be at least 3 characters',
                        'max' => 255,
                        'maxMessage' => 'Quiz title cannot exceed 255 characters'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[A-Z]/',
                        'message' => 'Quiz title must start with an uppercase letter (A-Z)'
                    ])
                ]
            ])
            ->add('level', ChoiceType::class, [
                'label' => 'Level',
                'choices' => [
                    'Beginner' => 'beginner',
                    'Intermediate' => 'intermediate',
                    'Advanced' => 'advanced',
                ],
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please select a level'])
                ]
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Duration (in seconds)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => '0',
                    'placeholder' => 'e.g., 1800 for 30 minutes'
                ],
                'constraints' => [
                    new Assert\PositiveOrZero(['message' => 'Duration must be a positive number'])
                ]
            ])
            ->add('isApproved', HiddenType::class, [
                'data' => false,
                'empty_data' => false,
            ])
            ->add('questions', CollectionType::class, [
                'entry_type' => QuestionQuizType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'constraints' => [
                    new Assert\Count([
                        'min' => 1,
                        'minMessage' => 'You must add at least 1 question'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quiz::class,
        ]);
    }
}
