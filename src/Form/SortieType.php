<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Repository\CampusRepository;
use App\Repository\LieuRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

// Formulaire de création de sortie
class SortieType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'invalid_message' => '',
                'attr' => [
                    'placeholder' => 'Nom de votre sortie'
                ]
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date et heure de la sortie',
                'invalid_message' => '',
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date limite d\'inscription',
                'invalid_message' => ''
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée',
                'invalid_message' => '',
                'attr' => [
                    'placeholder' => 'Nombre de minutes'
                ]
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre de places',
                'invalid_message' => '',
                'attr' => [
                    'placeholder' => 'L\'organisateur est par défaut participant'
                ]
                ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos',
                'invalid_message' => '',
                'attr' => [
                    'placeholder' => 'Votre description'
                ]
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'invalid_message' => '',
                'query_builder' => function (LieuRepository $lieuRepository) {
                    return $lieuRepository
                        ->createQueryBuilder('s')
                        ->addOrderBy('s.nom', 'ASC');
                }
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'invalid_message' => '',
                'query_builder' => function (CampusRepository $campusRepository) {
                    return $campusRepository
                        ->createQueryBuilder('s')
                        ->addOrderBy('s.nom', 'ASC');
                }
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            'required' => false
        ]);
    }
}