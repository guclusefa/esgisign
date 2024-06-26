<?php

namespace App\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileFilterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('search', SearchType::class, [
                'attr' => [
                    'placeholder' => 'Rechercher'
                ],
                'required' => false,
                'label' => 'Rechercher'
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
                'label' => 'Rôles'
            ])
            ->add('isVerified', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
                'label' => 'Vérifié'
            ])
            ->add('isBanned', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
                'label' => 'Banni'
            ])
            ->add('order', ChoiceType::class, [
                'choices' => [
                    'Nom d\'utilisateur' => 'username',
                    'Nom' => 'lastname',
                    'Prénom' => 'firstname',
                    'Date de création' => 'createdAt',
                    'Date de mise à jour' => 'updatedAt',
                ],
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'label' => 'Trier par'
            ])
            ->add('direction', ChoiceType::class, [
                'choices' => [
                    'Croissant' => 'ASC',
                    'Décroissant' => 'DESC',
                ],
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'label' => 'Direction'
            ])
            ->setMethod('GET')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
