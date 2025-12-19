<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Statistiques générales
        $totalServices = $entityManager->getRepository(Service::class)->count(['actif' => true]);
        $totalUsers = $entityManager->getRepository(User::class)->count([]);
        $totalReservations = $entityManager->getRepository(Reservation::class)->count([]);

        // Réservations en attente
        $reservationsEnAttente = $entityManager->getRepository(Reservation::class)->findBy(
            ['statut' => 'en_attente'],
            ['createdAt' => 'DESC']
        );

        // Réservations du jour
        $today = new \DateTime('today');
        $reservationsAujourdhui = $entityManager->getRepository(Reservation::class)
            ->createQueryBuilder('r')
            ->where('r.dateReservation = :today')
            ->setParameter('today', $today)
            ->orderBy('r.heureReservation', 'ASC')
            ->getQuery()
            ->getResult();

        // Dernières réservations
        $dernieresReservations = $entityManager->getRepository(Reservation::class)->findBy(
            [],
            ['createdAt' => 'DESC'],
            5
        );

        return $this->render('admin/dashboard/index.html.twig', [
            'totalServices' => $totalServices,
            'totalUsers' => $totalUsers,
            'totalReservations' => $totalReservations,
            'reservationsEnAttente' => $reservationsEnAttente,
            'reservationsAujourdhui' => $reservationsAujourdhui,
            'dernieresReservations' => $dernieresReservations,
        ]);
    }
}
