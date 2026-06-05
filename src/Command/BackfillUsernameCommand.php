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
    name: 'app:backfill-username',
    description: 'Backfill usernames for existing users',
)]
class BackfillUsernameCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Starting backfill of usernames...');
        $batchSize = 100;
        $i = 0;
        $users = $this->userRepository->createQueryBuilder('u')
            ->where('u.username IS NULL')
            ->getQuery()
            ->toIterable();
        foreach ($users as $user) {
            /** @var User $user */
            $username = $this->generateUsername($user);
            $user->setUsername($username);
            if (($i % $batchSize) === 0) {
                $this->em->flush();
                $this->em->clear();
                $output->writeln("Processed {$i} users...");
            }
            $i++;
        }
        $this->em->flush();
        $output->writeln('Finished backfilling usernames.');
        return Command::SUCCESS;
    }
    private function generateUsername(User $user): string
    {
        if($user->getEmail()) {
            $base = strtolower(explode('@', $user->getEmail())[0]);
        } else {
            $base = 'user_' . $user->getId();
        }
        $base = $this->slugify($base);
        return $this->ensureUniqueUsername($base);
    }
    private function ensureUniqueUsername(string $username): string
    {
        $original = $username;
        $i = 1;

        while ($this->userRepository->findOneBy(['username' => $username])) {
            $username = $original . $i;
            $i++;
        }
        return $username;
    }
    private function slugify(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '.', $text);
        $text = trim($text, '.');

        // replace non letter or digits by -
        // $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        // transliterate
        // $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // remove duplicate -
        // $text = preg_replace('~-+~', '-', $text);

        return $text ?: 'user';
    }
}
