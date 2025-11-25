<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Program;
use App\Entity\RegistrationFollowup;
use App\Entity\RegistrationStep;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFollowupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status')
            
            ->add('participant', EntityType::class, [
                'class' => Participant::class,
                'choice_label' => 'lastname',
            ])
            ->add('step', EntityType::class, [
                'class' => RegistrationStep::class,
                'choice_label' => 'step',
            ])
            ->add('program', EntityType::class, [
                'class' => Program::class,
                'choice_label' => 'title',

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RegistrationFollowup::class,
        ]);
    }
}
