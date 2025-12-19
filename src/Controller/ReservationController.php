<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Service;
use App\Form\ReservationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reservation')]
class ReservationController extends AbstractController
{
    #[Route('/nouvelle/{service}', name: 'app_reservation_new', requirements: ['service' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager, ?Service $service = null): Response
    {
        $reservation = new Reservation();
        $reservation->setUser($this->getUser());
        $reservation->setStatut('en_attente');

        if ($service) {
            $reservation->setService($service);
        }

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si une réservation existe déjà pour cet utilisateur à cette date/heure
            $existingReservation = $entityManager->getRepository(Reservation::class)->findOneBy([
                'user' => $this->getUser(),
                'dateReservation' => $reservation->getDateReservation(),
                'heureReservation' => $reservation->getHeureReservation(),
            ]);

            if ($existingReservation) {
                $this->addFlash('warning', 'Vous avez déjà une réservation à cette date et heure.');
                return $this->redirectToRoute('app_reservation_new', ['service' => $service?->getId()]);
            }

            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Votre demande de rendez-vous a été envoyée avec succès ! Vous recevrez une confirmation prochainement.');

            return $this->redirectToRoute('app_reservation_mes_reservations');
        }

        return $this->render('reservation/new.html.twig', [
            'form' => $form,
            'service' => $service,
        ]);
    }

    #[Route('/mes-reservations', name: 'app_reservation_mes_reservations')]
    #[IsGranted('ROLE_USER')]
    public function mesReservations(EntityManagerInterface $entityManager): Response
    {
        $reservations = $entityManager->getRepository(Reservation::class)->findBy(
            ['user' => $this->getUser()],
            ['dateReservation' => 'DESC', 'heureReservation' => 'DESC']
        );

        return $this->render('reservation/mes_reservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/{id}/annuler', name: 'app_reservation_cancel', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que c'est bien la réservation de l'utilisateur connecté
        if ($reservation->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas annuler cette réservation.');
        }

        // Ne peut annuler que si en_attente ou confirmé
        if (!in_array($reservation->getStatut(), ['en_attente', 'confirme'])) {
            $this->addFlash('error', 'Cette réservation ne peut pas être annulée.');
            return $this->redirectToRoute('app_reservation_mes_reservations');
        }

        $reservation->setStatut('annule');
        $entityManager->flush();

        $this->addFlash('success', 'Votre rendez-vous a été annulé.');

        return $this->redirectToRoute('app_reservation_mes_reservations');
    }
}
