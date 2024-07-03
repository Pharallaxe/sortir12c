<?php

namespace App\Form;

use App\Entity\Lieu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

// Formulaire de création de lieu
class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'invalid_message' => '',
                'attr' => [
                    'placeholder' => 'Nom de lieu'
                ]
            ])
            ->add('rue', TextType::class, [
                'label' => 'Rue',
                'invalid_message' => '',
                'attr' => [
                    'placeholder' => 'Nom de la rue'
                ]
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Latitude',
                'invalid_message' => '',
                'attr' => [
                    'placeholder' => '45.5655'
                ]
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Longitude',
                'invalid_message' => '',
                'attr' => [
                    'placeholder' => '45.5655'
                ]
            ])
            ->add('nomDeVille', TextType::class, [
                'label' => 'Nom de ville',
                'invalid_message' => '',
                'attr' => [
                    'placeholder' => 'Rennes'
                ]
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code Postal',
                'invalid_message' => '',
                'attr' => [
                    'placeholder' => '35000',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
            'required' => false
        ]);
    }
}