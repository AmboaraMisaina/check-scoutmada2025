<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Program;
use App\Entity\Registration;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('created_by')
            ->add('created_at')
            ->add('updated_at')
            ->add('participant', EntityType::class, [
                'class' => Participant::class,
                'choice_label' => 'id',
            ])
            ->add('program', EntityType::class, [
                'class' => Program::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Registration::class,
        ]);
    }
}
