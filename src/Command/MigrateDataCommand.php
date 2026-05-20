<?php

namespace App\Command;

// use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

#[AsCommand(
    name: 'app:migrate-data',
    description: 'Migrate data from the SQLite database to the current database.',
)]
class MigrateDataCommand extends Command
{
    // protected static $defaultName = 'app:migrate-data';
    // protected static $defaultDescription = 'Migrate data from the SQLite database to the current database.';
    public function __construct(private ManagerRegistry $doctrine)
    {
        parent::__construct();
    }

    // protected function configure(): void
    // {
    //     $this
    //         ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
    //         ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
    //     ;
    // }
    private function normalizeRoles(mixed $roles): array
    {
        if (is_array($roles)) {
            return $roles;
        }

        if (is_string($roles)) {
            if (str_starts_with($roles, '[')) {
                return json_decode($roles, true) ?: [];
            }

            return array_filter(array_map('trim', explode(',', $roles)));
        }

        return [];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sqliteConn = $this->doctrine->getConnection('sqlite');
        $rows = $sqliteConn->fetchAllAssociative('SELECT * FROM user');
        $mysqlEm = $this->doctrine->getManager();
        $repo = $mysqlEm->getRepository(User::class);
        $users = $repo->findAll();
        // foreach ($rows as $row) {
        //     dump($row['roles']);
        // }
        $output->writeln('Starting migration...');
        foreach ($rows as $row) {
            $newUser = new User();
            $newUser->setId($row['id']);
            $newUser->setEmail($row['email']);
            $newUser->setPassword($row['password']);
            $newUser->setRoles($this->normalizeRoles($row['roles']));
            $newUser->setIsVerified($row['is_verified']);
            $mysqlEm->persist($newUser);
        }
        $mysqlEm->flush();
        $output->writeln('Migration completed successfully.');
        $connection = $this->doctrine->getConnection('mysql');
        $maxId = $connection->fetchOne('SELECT MAX(id) FROM user');
        $nextId = $maxId + 1;
        $output->writeln('Max ID in the new database: ' . $maxId);
        $connection->executeStatement('ALTER TABLE user AUTO_INCREMENT = ' . $nextId);
        $output->writeln('Auto-increment value updated to: ' . $nextId);
        return Command::SUCCESS;
    }
}
