<?php

namespace App\Form;

use App\Entity\BadgeTemplate;
use App\Entity\Organization;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType; // 🚩 Importez FileType
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File; // Optionnel : pour la validation du fichier

class BadgeTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            
            // 🚩 1. REMPLACEMENT PAR FILETYPE
            // On peut l'appeler 'file' car ce n'est pas le champ de l'entité
            ->add('file', FileType::class, [ 
                'label' => 'Modèle de Badge (Image ou PDF)',
                
                // Indique que ce champ n'est pas lié directement à la propriété 'filePath' de l'entité
                'mapped' => false, 
                
                // Le fichier n'est pas obligatoire lors de l'édition d'une entité existante
                'required' => false, 
                
                // Optionnel: Validation du type de fichier
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Veuillez téléverser un fichier image (JPG, PNG) ou PDF valide.',
                    ])
                ],
            ])
            
            // 🚩 2. SUPPRESSION DES CHAMPS DE DATE
            // Les champs createdAt et updatedAt sont gérés par l'Entité et/ou le Contrôleur/Doctrine
            
            ->add('organization', EntityType::class, [
                'class' => Organization::class,
                'choice_label' => 'name', // Afficher le nom de l'organisation est plus convivial que l'ID
                'label' => 'Organisation Propriétaire',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BadgeTemplate::class,
        ]);
    }
}