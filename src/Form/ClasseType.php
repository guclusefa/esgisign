<?php

namespace App\Form;

use App\Entity\Classe;
use App\Entity\Course;
use App\Entity\Promotion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClasseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('start')
            ->add('end')
            ->add('promotion', EntityType::class, [
                'class' => Promotion::class,
'choice_label' => 'name',
            ])
            ->add('course', EntityType::class, [
                'class' => Course::class,
'choice_label' => 'subjectAndProfLabel',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Classe::class,
        ]);
    }
}
