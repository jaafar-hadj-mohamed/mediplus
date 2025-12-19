<?php

namespace App\Form;

use App\Entity\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class ServiceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du service',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom du service est obligatoire'])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Décrivez le service en détail...'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'La description est obligatoire'])
                ]
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (en minutes)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 30'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'La durée est obligatoire']),
                    new Positive(['message' => 'La durée doit être un nombre positif'])
                ]
            ])
            ->add('prix', MoneyType::class, [
                'label' => 'Prix (en TND)',
                'currency' => 'TND',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 50.00'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le prix est obligatoire']),
                    new Positive(['message' => 'Le prix doit être un nombre positif'])
                ]
            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Service actif',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }
}
