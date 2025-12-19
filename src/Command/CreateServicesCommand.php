<?php

namespace App\Command;

use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-services',
    description: 'Crée des services médicaux de test',
)]
class CreateServicesCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $services = [
            [
                'nom' => 'Consultation Générale',
                'description' => 'Examen médical complet avec diagnostic et prescription. Idéal pour un bilan de santé.',
                'duree' => 30,
                'prix' => 50.00
            ],
            [
                'nom' => 'Analyse Sanguine',
                'description' => 'Prise de sang et analyse complète en laboratoire. Résultats sous 48h.',
                'duree' => 15,
                'prix' => 35.00
            ],
            [
                'nom' => 'Radiographie',
                'description' => 'Examen radiologique pour détection de fractures ou anomalies osseuses.',
                'duree' => 20,
                'prix' => 80.00
            ],
            [
                'nom' => 'Vaccination',
                'description' => 'Administration de vaccins selon le calendrier vaccinal recommandé.',
                'duree' => 10,
                'prix' => 25.00
            ],
            [
                'nom' => 'Échographie',
                'description' => 'Examen d\'imagerie médicale par ultrasons pour diagnostic.',
                'duree' => 25,
                'prix' => 70.00
            ],
            [
                'nom' => 'Électrocardiogramme (ECG)',
                'description' => 'Examen du rythme cardiaque et détection d\'anomalies cardiaques.',
                'duree' => 20,
                'prix' => 45.00
            ],
        ];

        foreach ($services as $data) {
            $service = new Service();
            $service->setNom($data['nom']);
            $service->setDescription($data['description']);
            $service->setDuree($data['duree']);
            $service->setPrix($data['prix']);
            $service->setActif(true);

            $this->entityManager->persist($service);
        }

        $this->entityManager->flush();

        $io->success('6 services médicaux créés avec succès !');

        return Command::SUCCESS;
    }
}
