<?php

namespace App\Form;

use App\Entity\Organization;
use App\Entity\RegistrationStep;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Nom de l'étape
            ->add('step', TextType::class, [
                'label' => 'Nom de l\'étape',
            ])
            // Ordre de l'étape (peut être un entier)
            ->add('step_order', IntegerType::class, [
                'label' => 'Ordre d\'affichage',
            ])
            // Relation ManyToOne vers l'Organization
            ->add('organization', EntityType::class, [
                'class' => Organization::class,
                // Utilisez un champ plus parlant que 'id' pour la sélection (ex: 'name' ou 'title')
                'choice_label' => 'name', 
                'label' => 'Organisation',
                'placeholder' => 'Choisir une organisation',
            ])
        ;
        
        // Les champs 'created_at', 'updated_at', 'created_by', et 'updated_by' sont retirés.
        // Ils devraient être gérés automatiquement par des fonctionnalités
        // de timestamping et d'audit de l'utilisateur (listeners Doctrine).
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RegistrationStep::class,
        ]);
    }
}