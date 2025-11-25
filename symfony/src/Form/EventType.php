<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Organization;
use App\Entity\ParticipantType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // 1. Relation Organization
            ->add('organization', EntityType::class, [
                'class' => Organization::class,
                // Utiliser une propriété lisible (Name ou Title) au lieu de l'ID
                'choice_label' => 'name', 
                'label' => 'Organisation liée',
            ])
            
            // 2. Champs de Texte
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'événement',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['rows' => 4],
            ])
            
            // 3. Champs d'Heure (CamelCase utilisé ici, ajustez si votre entité utilise start_time)
            ->add('startTime', TimeType::class, [
                'label' => 'Heure de début',
                'input' => 'datetime', // Si vous travaillez avec des objets DateTime
                'widget' => 'single_text',
            ])
            ->add('endTime', TimeType::class, [
                'label' => 'Heure de fin',
                'input' => 'datetime',
                'widget' => 'single_text',
            ])
            
            // 4. Champs de Date (CamelCase utilisé ici, ajustez si votre entité utilise start_date)
            ->add('startDate', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text', // Affiche un champ de type date HTML5
            ])
            ->add('endDate', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
            ])

            ->add('authorizedParticipantTypes', EntityType::class, [
                'class' => ParticipantType::class,
                'choice_label' => 'name', 
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => 'Types de participants autorisés',
                
                // 🚩 CORRECTION : Indiquer à Symfony que ce champ est Virtuel
                'mapped' => false, 
                ])
            
            // 5. Champs de Gestion (created/updated) RETIRÉS
            // Ces champs doivent être gérés dans le contrôleur ou via les événements Doctrine.
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}