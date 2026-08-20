<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use LogicException;

class AbstractKernelTestCase extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;
    protected ValidatorInterface $validator;
    protected ContainerInterface $container;
    protected KernelBrowser $client;

    // === Initialization ===
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->container = static::getContainer();
        $this->validator = self::getContainer()->get(ValidatorInterface::class);
        $this->entityManager = $this->container->get(EntityManagerInterface::class);
    
        $this->createSchema();
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();
        parent::tearDown();
    }

    // === Database Helpers ===    
    // = Schema creation =
    private function createSchema(): void
    {
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        if(empty($metadata)){
            throw new LogicException('No metadata found to create schema.');
        }

        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->entityManager);
        $schemaTool->createSchema($metadata);
    }
    // = Entity Persistence =
    protected function persist(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
    // = Entity Removal =
    protected function remove(object $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
    
    // = Clear the entity manager =
    protected function clearEntityManager(): void
    {
        $this->entityManager->clear();
    }

    // === Auth Helpers ===
    // = User Creation =
    protected function createUser(
        string $email = 'test@example.com',
        string $password = 'testpassword123',
        array $roles = ['ROLE_USER'],
        string $username = 'testuser',
        string $displayName = 'Test User'
    ): User {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
        $user->setRoles($roles);
        $user->setUsername($username);
        $user->setDisplayName($displayName);
        $user->setIsVerified(true);

        $this->persist($user);
        return $user;
    }

    // Get a service from the container
    protected function getService(string $serviceId): mixed
    {
        return $this->container->get($serviceId);
    }

    // Get a repository by entity class
    protected function getRepository(string $entityClass): mixed
    {
        return $this->entityManager->getRepository($entityClass);
    }
    
    // String & Format Helpers
    protected function generateRandomString(int $length = 10): string
    {
        return substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz', ceil($length / 26))), 0, $length);
    }
    protected function generateRandomEmail(): string
    {
        return $this->generateRandomString(8).'@example.com';
    }
}
