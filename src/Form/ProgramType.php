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
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class ProgramType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titre'])
            ->add('summary', TextareaType::class, ['label' => 'Description'])
            ->add('poster')
            ->add('posterFile', VichFileType::class, [
                'required'      => false,
                'allow_delete'  => true, // not mandatory, default is true
                'download_uri' => true, // not mandatory, default is true
            ])
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
