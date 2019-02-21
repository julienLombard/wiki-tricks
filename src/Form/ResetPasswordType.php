<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add('username')
            ->add('email', EmailType::class, [
                'label' => "Email",
                'required' => true
                ])
            ->add('plainPassword', PasswordType::class, [
                'label' => "Password",
                'required' => true
                ])
            // ->add('registeredAt')
            // ->add('confirmationToken')
            // ->add('validate')
            // ->add('resetToken')
            // ->add('picture')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
