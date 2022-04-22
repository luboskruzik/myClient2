<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Model\User;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', Type\ChoiceType::class, [
                'choices'  => [
                    'Mr.' => 'mr',
                    'Mrs.' => 'mrs',
                    'Ms.' => 'ms',
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('first_name', Type\TextType::class, [])
            ->add('last_name', Type\TextType::class, [])
            ->add('email', Type\EmailType::class, [])
            ->add('phone', Type\TextType::class, [])
            ->add('prefix', Type\HiddenType::class, [])
            ->add('country', Type\HiddenType::class, [])
            ->add('privacy_policy', Type\CheckboxType::class)
            ->add('newsletter', Type\CheckboxType::class, [
                'required' => false
            ])
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
