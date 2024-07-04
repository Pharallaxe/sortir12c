<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

// Formulaire de création de participant
class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo')
            ->add('prenom', TextType::class, [
                'label' => 'Prénom'])
            ->add('nom')
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone'
            ])
            ->add('email', EmailType::class, [])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Le mot de passe et la confirmation du mot de passe doivent concorder.',
                'options' => ['attr' => ['class' => 'form-control']],
                'required' => false,
                'mapped' => false,
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmation du mot de passe']
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
            ])
            ->add('imageProfil', FileType::class, [
                'mapped' => false,
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/gif'
                ],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\Image(
                        [
                            'maxSize' => '1024k',
                            'mimeTypesMessage' => 'Le format est invalide',
                            'maxSizeMessage' => 'Le fichier est trop volumineux'
                        ]
                    )
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
            'required' => false
        ]);
    }
}