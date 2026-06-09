<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class GeneralUserSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('displayName', null, [
                'label' => 'Display Name',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a display name.'
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Your display name should be at least {{ limit }} characters',
                        'max' => 32,
                        'maxMessage' => 'Your display name should not be longer than {{ limit }} characters',
                    ]),
                ]
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
