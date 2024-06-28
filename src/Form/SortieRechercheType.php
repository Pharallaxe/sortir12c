<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Etat;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieRechercheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', SearchType::class, [
                'label' => 'Nom de la sortie',
                'required' => false,
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('etat', EntityType::class, [
                'class' => Etat::class,
                'choice_label' => 'libelle',
                'label' => 'Etat',
                'placeholder' => 'Tous',
                'required' => false,
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'label' => 'Campus',
                'placeholder' => 'Tous',
                'required' => false,
            ])
            ->add('organisateur', CheckboxType::class, [
                'label' => "Sorties dont je suis l'organisateur",
                'required' => false,
            ])
            ->add('participant', CheckboxType::class, [
                'label' => "Sorties auxquelles je suis inscrit",
                'required' => false,
            ])
            ->add('notParticipant', CheckboxType::class, [
                'label' => "Sorties auxquelles je ne suis pas inscrit",
                'required' => false,
            ])
            ->add('passe', CheckboxType::class, [
                'label' => "Sorties passées",
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
