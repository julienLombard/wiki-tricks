<?php

namespace App\Form;

use App\Entity\User;
use App\Form\PictureType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class RegistrationType extends AbstractType
{
    /**
     * getConfiguration
     *
     * @param mixed $label
     * @param mixed $placeholder
     * @param mixed $options
     * @return array
     */
    private function getConfiguration($label, $placeholder, $options = []) 
    {
        return array_merge([
            'label' => $label,
            'attr' => [
                'placeholder' => $placeholder
            ]
            ], $options);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, $this->getConfiguration("Identifiant", "Votre identifiant ..."))
            ->add('email', EmailType::class, $this->getConfiguration("Email", "Votre email ..."))
            // ->add('password', PasswordType::class, $this->getConfiguration("Mot de passe", "Votre mot de passe ..."))
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class, 
                'first_options' => $this->getConfiguration("Mot de passe", "Votre mot de passe ..."),
                'second_options' => $this->getConfiguration("Confirmation du mot de passe", "Confirmer votre mot de passe ...")
            ])
            // ->add('registeredAt')
            // ->add('validate')
            // ->add('picture',
            // CollectionType::class,
            // [
            //     'entry_type' => PictureType::class,
            //     'allow_add' => true,
            //     'allow_delete' => true,
            //     "by_reference"  => false
            // ]
        // )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
