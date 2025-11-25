<?php

namespace App\Form;

use App\Entity\Admin;
use App\Entity\Role;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')

            // Mot de passe avec confirmation (seulement en création ou si on veut le changer)
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmer le mot de passe'],
                'required' => !$options['is_edit'], // Obligatoire en création, optionnel en édition
                'mapped' => false, // Important : on gère le hash manuellement dans le controller
            ])

            // Affichage du nom du rôle au lieu de l'ID
            ->add('role', EntityType::class, [
                'class' => Role::class,
                'choice_label' => 'label', // ← Affiche le nom du rôle
                'placeholder' => 'Choisir un rôle',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Admin::class,
            'is_edit' => false, // Pour savoir si on est en création ou édition
        ]);

        $resolver->setAllowedTypes('is_edit', 'bool');
    }
}