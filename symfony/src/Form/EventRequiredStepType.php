<?php

// namespace App\Form;

// use App\Entity\Event;
// use App\Entity\EventRequiredStep;
// use App\Entity\RegistrationStep;
// use Symfony\Bridge\Doctrine\Form\Type\EntityType;
// use Symfony\Component\Form\AbstractType;
// use Symfony\Component\Form\FormBuilderInterface;
// use Symfony\Component\OptionsResolver\OptionsResolver;

// class EventRequiredStepType extends AbstractType
// {
//     public function buildForm(FormBuilderInterface $builder, array $options): void
//     {
//         $builder
//             ->add('isMandatory')
//             ->add('createdAt', null, [
//                 'widget' => 'single_text',
//             ])
//             ->add('event', EntityType::class, [
//                 'class' => Event::class,
//                 'choice_label' => 'id',
//             ])
//             ->add('step', EntityType::class, [
//                 'class' => RegistrationStep::class,
//                 'choice_label' => 'id',
//             ])
//         ;
//     }

//     public function configureOptions(OptionsResolver $resolver): void
//     {
//         $resolver->setDefaults([
//             'data_class' => EventRequiredStep::class,
//         ]);
//     }
// }
