<?php

namespace App\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

final class AnnouncementPopupFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label'       => 'Titre (EN)',
                'constraints' => [new NotBlank(message: 'Le titre est obligatoire')],
                'attr'        => ['placeholder' => 'Title in English'],
            ])
            ->add('titleFr', TextType::class, [
                'label'    => 'Titre (FR)',
                'required' => false,
                'attr'     => ['placeholder' => 'Titre en français'],
            ])
            ->add('content', CKEditorType::class, [
                'label'       => 'Contenu (EN)',
                'constraints' => [new NotBlank(message: 'Le contenu est obligatoire')],
            ])
            ->add('contentFr', CKEditorType::class, [
                'label'    => 'Contenu (FR)',
                'required' => false,
            ])
            ->add('imageUrl', TextType::class, [
                'label'    => 'URL image (EN)',
                'required' => false,
                'attr'     => ['placeholder' => 'https://...'],
            ])
            ->add('imageUrlFr', TextType::class, [
                'label'    => 'URL image (FR)',
                'required' => false,
                'attr'     => ['placeholder' => 'https://...'],
            ])
            ->add('isActive', CheckboxType::class, [
                'label'    => 'Actif',
                'required' => false,
            ])
            ->add('priority', IntegerType::class, [
                'label'       => 'Priorité',
                'data'        => 0,
                'constraints' => [new PositiveOrZero(message: 'La priorité doit être positive ou nulle')],
                'help'        => 'Plus le chiffre est petit, plus le popup apparaît en premier (0 = premier)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['csrf_protection' => true]);
    }
}
