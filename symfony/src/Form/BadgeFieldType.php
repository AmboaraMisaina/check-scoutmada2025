<?php

namespace App\Form;

use App\Entity\BadgeField;
use App\Entity\BadgeTemplate;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BadgeFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fieldName')
            ->add('posX')
            ->add('posY')
            ->add('fontSize')
            ->add('fontColor')
            ->add('width')
            ->add('height')
            ->add('badgeTemplate', EntityType::class, [
                'class' => BadgeTemplate::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BadgeField::class,
        ]);
    }
}
