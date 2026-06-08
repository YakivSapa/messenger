<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:backfill-displayname',
    description: 'Backfill displayName for existing users',
)]
class BackfillDisplaynameCommand extends Command
{
    public function __construct(private UserRepository $userRepository, private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Starting backfill of display names...');
        $batchSize = 100;
        $i = 0;
        $users = $this->userRepository->createQueryBuilder('u')
            ->where('u.displayName IS NULL')
            ->getQuery()
            ->toIterable();
        foreach ($users as $user) {
            /** @var User $user */
            $displayName = $user->getUsername() ?? 'User ' . $user->getId();
            $user->setDisplayName($displayName);
            if (($i % $batchSize) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                $output->writeln("Processed {$i} users...");
            }
            $i++;
        }
        $this->entityManager->flush();
        
        $output->writeln('Finished backfilling display names.');
        return Command::SUCCESS;
    }
}
