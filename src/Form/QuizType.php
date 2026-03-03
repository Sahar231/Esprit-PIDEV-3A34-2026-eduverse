<?php

namespace App\Form;

use App\Entity\Quiz;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class QuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre du Quiz',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3, 'max' => 255]),
                ],
                'attr' => ['placeholder' => 'Ex: Quiz sur les verbes anglais']
            ])
            ->add('level', ChoiceType::class, [
                'label' => 'Niveau de Difficulté',
                'choices' => [
                    'Facile' => 'facile',
                    'Intermédiaire' => 'intermediaire',
                    'Difficile' => 'difficile',
                ],
                'constraints' => [new NotBlank()],
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Durée (en minutes)',
                'constraints' => [new NotBlank()],
                'attr' => ['placeholder' => '30']
            ])
            ->add('questions', CollectionType::class, [
                'entry_type' => QuestionType::class,
                'label' => 'Questions',
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'by_reference' => false,
                'attr' => ['class' => 'questions-collection']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quiz::class,
        ]);
    }
}
