<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\Service;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateReservation', DateType::class, [
                'label' => 'Date du rendez-vous',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'min' => (new \DateTime())->format('Y-m-d')
                ],
                'html5' => true,
            ])
            ->add('heureReservation', TimeType::class, [
                'label' => 'Heure du rendez-vous',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'html5' => true,
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Motif de consultation / Notes (optionnel)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Décrivez brièvement le motif de votre consultation...'
                ]
            ])
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => function(Service $service) {
                    return $service->getNom() . ' (' . $service->getDuree() . ' min - ' . $service->getPrix() . ' TND)';
                },
                'label' => 'Service médical',
                'attr' => ['class' => 'form-control'],
                'query_builder' => function($repository) {
                    return $repository->createQueryBuilder('s')
                        ->where('s.actif = :actif')
                        ->setParameter('actif', true)
                        ->orderBy('s.nom', 'ASC');
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
