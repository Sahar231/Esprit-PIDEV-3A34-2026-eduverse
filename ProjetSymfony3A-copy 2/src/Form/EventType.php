<?php

namespace App\Form;

use App\Entity\Club;
use App\Entity\Event;
use App\Repository\ClubRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'événement',
                'attr' => [
                    'placeholder' => 'Entrez le titre',
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Décrivez l\'événement...',
                    'rows' => 4,
                    'class' => 'form-control'
                ]
            ])
            ->add('eventDate', DateTimeType::class, [
                'label' => 'Date et Heure',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('location', TextType::class, [
                'label' => 'Lieu',
                'attr' => [
                    'placeholder' => 'Ex: Salle A, En ligne...',
                    'class' => 'form-control'
                ]
            ])
            ->add('club', EntityType::class, [
                'class' => Club::class,
                'choice_label' => 'name',
                'label' => 'Club',
                'placeholder' => 'Sélectionnez un club',
                'query_builder' => function (ClubRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.status = :status')
                        ->setParameter('status', Club::STATUS_APPROVED)
                        ->orderBy('c.name', 'ASC');
                },
                'attr' => ['class' => 'form-select']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'attr' => [
                'novalidate' => 'novalidate',
            ]
        ]);
    }
}
