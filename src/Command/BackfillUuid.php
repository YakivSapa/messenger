<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

#[AsCommand(
    name: 'app:backfill-uuid',
    description: 'Backfill UUIDs for existing users.',
)]
class BackfillUuid extends Command
{
    public function __construct(private ManagerRegistry $doctrine)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mysqlEm = $this->doctrine->getManager();
        $repo = $mysqlEm->getRepository(User::class);
        $users = $repo->findAll();

        $output->writeln('Starting UUID backfill...');
        foreach ($users as $user) {
            if (!$user->getUuid()) {
                $user->setUuid(new \Symfony\Component\Uid\UuidV7());
                $mysqlEm->persist($user);
            }
        }
        $mysqlEm->flush();

        $output->writeln('UUID backfill completed.');

        return Command::SUCCESS;
    }
}