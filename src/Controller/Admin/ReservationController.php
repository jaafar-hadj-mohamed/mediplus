<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/reservation')]
#[IsGranted('ROLE_ADMIN')]
class ReservationController extends AbstractController
{
    #[Route('/', name: 'admin_reservation_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $reservations = $entityManager->getRepository(Reservation::class)->findBy(
            [],
            ['dateReservation' => 'DESC', 'heureReservation' => 'DESC']
        );

        return $this->render('admin/reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/{id}', name: 'admin_reservation_show', requirements: ['id' => '\d+'])]
    public function show(Reservation $reservation): Response
    {
        return $this->render('admin/reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/confirmer', name: 'admin_reservation_confirm', requirements: ['id' => '\d+'])]
    public function confirm(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($reservation->getStatut() !== 'en_attente') {
            $this->addFlash('warning', 'Cette réservation ne peut pas être confirmée.');
            return $this->redirectToRoute('admin_reservation_show', ['id' => $reservation->getId()]);
        }

        $reservation->setStatut('confirme');
        $entityManager->flush();

        $this->addFlash('success', 'Réservation confirmée avec succès !');
        return $this->redirectToRoute('admin_reservation_show', ['id' => $reservation->getId()]);
    }

    #[Route('/{id}/refuser', name: 'admin_reservation_refuse', requirements: ['id' => '\d+'])]
    public function refuse(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($reservation->getStatut() !== 'en_attente') {
            $this->addFlash('warning', 'Cette réservation ne peut pas être refusée.');
            return $this->redirectToRoute('admin_reservation_show', ['id' => $reservation->getId()]);
        }

        $reservation->setStatut('annule');
        $entityManager->flush();

        $this->addFlash('success', 'Réservation refusée.');
        return $this->redirectToRoute('admin_reservation_index');
    }

    #[Route('/{id}/terminer', name: 'admin_reservation_complete', requirements: ['id' => '\d+'])]
    public function complete(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($reservation->getStatut() !== 'confirme') {
            $this->addFlash('warning', 'Cette réservation doit être confirmée avant d\'être marquée comme terminée.');
            return $this->redirectToRoute('admin_reservation_show', ['id' => $reservation->getId()]);
        }

        $reservation->setStatut('termine');
        $entityManager->flush();

        $this->addFlash('success', 'Réservation marquée comme terminée.');
        return $this->redirectToRoute('admin_reservation_show', ['id' => $reservation->getId()]);
    }

    #[Route('/{id}/remarque', name: 'admin_reservation_remarque', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function addRemarque(Reservation $reservation, Request $request, EntityManagerInterface $entityManager): Response
    {
        $remarque = $request->request->get('remarque');

        if ($remarque) {
            $reservation->setRemarqueAdmin($remarque);
            $entityManager->flush();

            $this->addFlash('success', 'Remarque ajoutée avec succès.');
        }

        return $this->redirectToRoute('admin_reservation_show', ['id' => $reservation->getId()]);
    }

    #[Route('/{id}/supprimer', name: 'admin_reservation_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($reservation);
        $entityManager->flush();

        $this->addFlash('success', 'Réservation supprimée avec succès.');
        return $this->redirectToRoute('admin_reservation_index');
    }
}
