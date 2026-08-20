<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractWebTestCase extends WebTestCase
{
    protected ValidatorInterface $validator;
    protected EntityManagerInterface $entityManager;
    protected KernelBrowser $client;

    // === Initialization ===
    public function setUp(): void
    {
        $this->client = static::createClient();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        // $this->validator = self::getContainer()->get(ValidatorInterface::class);

        $this->createSchema();
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
    // = Entity persistence =
    protected function persist(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
    // = Entity removal =
    protected function remove(object $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    // === Auth Helpers ===
    // = User Creation =
    protected function createAndLoginUser(
        string $email = 'test@example.com',
        string $password = 'testpassword123',
        array $roles = ['ROLE_USER'],
        string $username = 'testuser',
        string $displayName = 'Test User'
    ): User {
        // Create a new user entity
        $user = new User();
        $user->setEmail($email);
        $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
        $user->setRoles($roles);
        $user->setUsername($username);
        $user->setDisplayName($displayName);
        $user->setIsVerified(true);

        $this->persist($user);

        // Log in the created user
        $this->client->loginUser($user);

        return $user;
    }

    // === HTTP Helpers ===
    protected function get(string $path, array $headers = []): Response
    {
        $this->client->request('GET', $path, [], [], $this->buildHeaders($headers));
        return $this->client->getResponse();
    }
    protected function post(string $path, array $data = [], array $headers = []): Response
    {
        $this->client->request('POST', $path, [], [], $this->buildHeaders($headers), json_encode($data));
        return $this->client->getResponse();
    }
    private function buildHeaders(array $custom = []): array
    {
        return array_merge([
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], $custom);
    }

    // === Response Helpers ===
    protected function getStatus(): int
    {
        return$this->client->getResponse()->getStatusCode();
    }

    // === JSON Assertion Helpers ===
    protected function assertStatus(int $expectedStatus): void
    {
        $this->assertSame($expectedStatus, $this->getStatus(),
        sprintf(
            'Expected status %d but got %d. Response: %s',
            $expectedStatus,
            $this->getStatus(),
            $this->client->getResponse()->getContent()
        ));
    }

    // === Assertion Helpers ===
    protected function assertEntityValid(object $entity): void
    {
        $violations = $this->validator->validate($entity);
        $this->assertCount(
            0,
            $violations,
            "Expected entity to be valid, but found validation errors:\n". $this->formatViolations($violations)
        );
    }
    protected function assertEntityInvalid(object $entity, ?int $expectedViolationsCount = null): void
    {
        $violations = $this->validator->validate($entity);

        if ($expectedViolationsCount !== null) {
            $this->assertCount(
                $expectedViolationsCount, 
                $violations,
                sprintf('Expected %d violations, but got %d', $this->formatViolations($violations))
            );
        } else {
            $this->assertGreaterThan(
                0,
                $violations->count(),
                'Expected at least one validation error, but the entity is valid.'
            );
        }
    }
    protected function assertPropertyHasViolation(object $entity, string $property): void
    {
        $violations = $this->validator->validate($entity);
        $propertyViolations = array_filter(
            iterator_to_array($violations),
            fn($v) => $v->getPropertyPath() === $property
        );

        $this->assertNotEmpty(
            $propertyViolations,
            'Expected property $property to have violations, but found none.'
        );
    }
    protected function assertPropertyValid(object $entity, string $property): void
    {
        $violations = $this->validator->validate($entity);
        $propertyViolations = array_filter(
            iterator_to_array($violations),
            fn($v) => $v->getPropertyPath() === $property
        );

        $this->assertEmpty(
            $propertyViolations,
            "Expected property $property to be valid, but found violations:\n".$this->formatViolations($violations)
        );
    }
    protected function assertHtmlContains(string $text): void
    {
        $this->assertStringContainsString($text, $this->client->getResponse()->getContent());
    }

    // === Format Helpers ===
    private function formatViolations($violations): string
    {
        $formated = '';
        foreach($violations as $violation) {
            $formated .= sprintf(
                " - %s: %s\n",
                $violation->getPropertyPath(),
                $violation->getMessage()
            );
        }
        return $formated;
    }
}
