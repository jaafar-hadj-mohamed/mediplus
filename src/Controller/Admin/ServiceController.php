<?php

namespace App\Controller\Admin;

use App\Entity\Service;
use App\Form\ServiceFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/service')]
#[IsGranted('ROLE_ADMIN')]
class ServiceController extends AbstractController
{
    #[Route('/', name: 'admin_service_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $services = $entityManager->getRepository(Service::class)->findBy(
            [],
            ['nom' => 'ASC']
        );

        return $this->render('admin/service/index.html.twig', [
            'services' => $services,
        ]);
    }

    #[Route('/nouveau', name: 'admin_service_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $service = new Service();
        $service->setActif(true);

        $form = $this->createForm(ServiceFormType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($service);
            $entityManager->flush();

            $this->addFlash('success', 'Service créé avec succès !');
            return $this->redirectToRoute('admin_service_index');
        }

        return $this->render('admin/service/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_service_show', requirements: ['id' => '\d+'])]
    public function show(Service $service, EntityManagerInterface $entityManager): Response
    {
        // Compter les réservations liées à ce service
        $totalReservations = $entityManager->getRepository(\App\Entity\Reservation::class)->count([
            'service' => $service
        ]);

        return $this->render('admin/service/show.html.twig', [
            'service' => $service,
            'totalReservations' => $totalReservations,
        ]);
    }

    #[Route('/{id}/modifier', name: 'admin_service_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ServiceFormType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Service modifié avec succès !');
            return $this->redirectToRoute('admin_service_show', ['id' => $service->getId()]);
        }

        return $this->render('admin/service/edit.html.twig', [
            'form' => $form,
            'service' => $service,
        ]);
    }

    #[Route('/{id}/toggle', name: 'admin_service_toggle', requirements: ['id' => '\d+'])]
    public function toggle(Service $service, EntityManagerInterface $entityManager): Response
    {
        $service->setActif(!$service->isActif());
        $entityManager->flush();

        $status = $service->isActif() ? 'activé' : 'désactivé';
        $this->addFlash('success', "Service {$status} avec succès !");

        return $this->redirectToRoute('admin_service_index');
    }

    #[Route('/{id}/supprimer', name: 'admin_service_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Service $service, EntityManagerInterface $entityManager): Response
    {
        // Vérifier s'il y a des réservations liées
        $reservations = $entityManager->getRepository(\App\Entity\Reservation::class)->findBy([
            'service' => $service
        ]);

        if (count($reservations) > 0) {
            $this->addFlash('error', 'Impossible de supprimer ce service car il est lié à des réservations.');
            return $this->redirectToRoute('admin_service_show', ['id' => $service->getId()]);
        }

        $entityManager->remove($service);
        $entityManager->flush();

        $this->addFlash('success', 'Service supprimé avec succès !');
        return $this->redirectToRoute('admin_service_index');
    }
}
