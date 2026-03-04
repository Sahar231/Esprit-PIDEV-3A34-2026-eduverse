<?php

namespace App\Form;

use App\Entity\QuestionQuiz;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class QuestionQuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', TextareaType::class, [
                'label' => 'Question',
                'attr' => ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Enter question (start with uppercase)'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Question text is required']),
                    new Assert\Length([
                        'min' => 5,
                        'minMessage' => 'Question must be at least 5 characters',
                        'max' => 2000,
                        'maxMessage' => 'Question cannot exceed 2000 characters'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[A-Z]/',
                        'message' => 'Question must start with an uppercase letter (A-Z)'
                    ])
                ]
            ])
            ->add('correctAnswer', TextType::class, [
                'label' => 'Correct Answer',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter correct answer (start with uppercase)'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Correct answer is required']),
                    new Assert\Length([
                        'min' => 1,
                        'minMessage' => 'Please provide a correct answer',
                        'max' => 500,
                        'maxMessage' => 'Answer cannot exceed 500 characters'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[A-Z]/',
                        'message' => 'Answer must start with an uppercase letter (A-Z)'
                    ])
                ]
            ])
            ->add('score', NumberType::class, [
                'label' => 'Score',
                'attr' => ['class' => 'form-control', 'step' => '0.01'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Score is required']),
                    new Assert\PositiveOrZero(['message' => 'Score must be 0 or positive'])
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Question Type',
                'choices' => [
                    'Multiple Choice (QCM)' => 'qcm',
                ],
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please select a question type'])
                ]
            ])
            ->add('choices', CollectionType::class, [
                'label' => 'Answer Choices',
                'entry_type' => TextType::class,
                'entry_options' => [
                    'attr' => ['class' => 'form-control', 'placeholder' => 'Enter choice (start with uppercase)'],
                    'constraints' => [
                        new Assert\NotBlank(['message' => 'Each choice must have text']),
                        new Assert\Length([
                            'min' => 1,
                            'minMessage' => 'Choice cannot be empty',
                            'max' => 500,
                            'maxMessage' => 'Choice cannot exceed 500 characters'
                        ]),
                        new Assert\Regex([
                            'pattern' => '/^[A-Z]/',
                            'message' => 'Choice must start with an uppercase letter (A-Z)'
                        ])
                    ]
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'attr' => ['class' => 'form-group'],
                'constraints' => [
                    new Assert\Count([
                        'min' => 2,
                        'minMessage' => 'You must provide at least 2 answer choices'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QuestionQuiz::class,
        ]);
    }
}
