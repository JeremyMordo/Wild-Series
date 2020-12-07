<?php

namespace App\Form;

use App\Entity\Program;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\Entity\Actor;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class ProgramType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titre'])
            ->add('summary', TextareaType::class, ['label' => 'Description'])
            ->add('poster', TextType::class, ['label' => 'Image'])
            ->add('country', CountryType::class, ['label' => 'Pays'])
            ->add('year', IntegerType::class, ['label' => 'Année de diffusion'])
            ->add('category', null, ['choice_label' => 'name'])
            ->add('actors', EntityType::class, ['class' => Actor::class,'choice_label' => 'name', 'expanded' => true, 'multiple' => true, 'by_reference' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Program::class,
        ]);
    }
}
