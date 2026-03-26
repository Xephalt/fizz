<?php

namespace App\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

final class AnnouncementPopupFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre (EN)',
                'constraints' => [
                    new NotBlank(message: 'Le titre est obligatoire'),
                ],
                'attr' => [
                    'placeholder' => 'Title in English',
                ],
            ])

            ->add('titleFr', TextType::class, [
                'label' => 'Titre (FR)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Titre en français',
                ],
            ])

            ->add('content', CKEditorType::class, [
                'label' => 'Contenu (EN)',
                'constraints' => [
                    new NotBlank(message: 'Le contenu est obligatoire'),
                ],
            ])

            ->add('contentFr', CKEditorType::class, [
                'label' => 'Contenu (FR)',
                'required' => false,
            ])

            ->add('imageUrl', FileType::class, [
                'label' => 'Image (EN)',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new Image(['maxSize' => '2M']),
                ],
            ])

            ->add('imageUrlFr', FileType::class, [
                'label' => 'Image (FR)',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new Image(['maxSize' => '2M']),
                ],
            ])

            ->add('isActive', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
            ])

            ->add('priority', IntegerType::class, [
                'label' => 'Priorité',
                'data' => 0,
                'constraints' => [
                    new PositiveOrZero(message: 'La priorité doit être positive ou nulle'),
                ],
                'help' => 'Plus le chiffre est petit, plus le popup apparaît en premier (0 = premier)',
            ])

            ->add('recurrenceSeconds', ChoiceType::class, [
                'label'    => 'Récurrence',
                'required' => false,
                // Note: on utilise 0 comme sentinelle pour "jamais" car ChoiceType
                // ne gère pas fiablement null comme valeur sélectionnée.
                // La conversion 0 <-> null se fait dans le controller.
                'choices'  => [
                    'Jamais (vue une seule fois)' => 0,
                    '3 heures'                    => 10800,
                    '1 jour'                      => 86400,
                    '3 jours'                     => 259200,
                    '1 semaine'                   => 604800,
                    '1 mois (30 jours)'           => 2592000,
                ],
                'help' => 'Délai après lequel la popup réapparaît pour un utilisateur qui l\'a déjà vue.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
        ]);
    }
}