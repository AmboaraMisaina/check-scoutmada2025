<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Event;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('allowedCategories', EntityType::class, [
            'class' => Category::class,
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
            'required' => false,
            'label' => 'Catégories autorisées',
            'help' => 'Sélectionnez les types de participants qui peuvent accéder à cet événement.',
            'mapped' => false, // Important : pas de champ direct
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
