<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('comment', TextareaType::class, ['label' => 'Entrer votre commentaire'])
            ->add('rate', ChoiceType::class, [
                'choices' => [
                    '1/10' => 1,
                    '2/10' => 2,
                    '3/10' => 3,
                    '4/10' => 4,
                    '5/10' => 5,
                    '6/10' => 6,
                    '7/10' => 7,
                    '8/10' => 8,
                    '9/10' => 9,
                    '10/10' => 10,
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
