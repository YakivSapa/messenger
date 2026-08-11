<?php

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser as Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AbstractWebTestCase extends WebTestCase
{
    protected ValidatorInterface $validator;
    protected ?Client $client = null;
    protected EntityManagerInterface $entityManager;

    // Initialization
    public function setUp(): void
    {
        parent::setUp();
        $client = static::createClient();
        self::bootKernel();

        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->validator = self::getContainer()->get(ValidatorInterface::class);

        $this->createSchema();
    }

    // Database schema creation
    private function createSchema(): void
    {
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        if(empty($metadata)){
            throw new LogicException('No metadata found to create schema.');
        }

        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->entityManager);
        $schemaTool->createSchema($metadata);
    }

    // Persists entity to the database
    protected function persist(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    // Removes entity from the database
    protected function remove(object $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    // Helper functions
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

    // Format violations into a readable string
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
