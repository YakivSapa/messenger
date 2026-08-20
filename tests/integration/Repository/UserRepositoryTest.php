<?php

namespace App\Tests\integration\Repository;

use App\Tests\AbstractKernelTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;

class UserRepositoryTest extends AbstractKernelTestCase
{
    private UserRepository $userRepository;
    private EntityManagerInterface $em;
    protected function setUp(): void
    {
        parent::setUp();
        $this->em = $this->getService(EntityManagerInterface::class);
        $this->userRepository = $this->getRepository(User::class);
    }

    public function testFindByEmailReturnsSingleUser(): void
    {
        $user = $this->createUser();
        $found = $this->userRepository->findByEmail($user->getEmail());
        $this->assertNotNull($found);
        $this->assertEquals($user->getEmail(), $found->getEmail());
    }
}
