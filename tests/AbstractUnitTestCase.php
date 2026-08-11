<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractUnitTestCase extends KernelTestCase
{
    protected ValidatorInterface $validator;

    // Initialization of the Symfony kernel
    protected function setUp(): void
    {
        parent::setUp();
        $kernel = self::bootKernel();
        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    // Helper Methods
    protected function assertEntityValid(object $entity): void
    {
        $violations = $this->validator->validate($entity);
        $this->assertCount(
            0,
            $violations,
            "Expected no validation violations, but found:\n" . $this->formatViolations($violations)
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
        
        // Filter violations for the specific property
        $propertyViolations = array_filter(
            iterator_to_array($violations),
            fn($v) => $v->getPropertyPath() === $property
        );

        $this->assertNotEmpty(
            $propertyViolations,
            "Expected property '$property' to have validation violations, but none were found."
        );
    }
    protected function assertPropertyValid(object $entity, string $property): void
    {
        $violations = $this->validator->validate($entity);
        
        // Filter violations for the specific property
        $propertyViolations = array_filter(
            iterator_to_array($violations),
            fn($v) => $v->getPropertyPath() === $property
        );

        $this->assertEmpty(
            $propertyViolations,
            "Expected property '$property' to be valid, but found validation violations:\n" . $this->formatViolations($propertyViolations)
        );
    }
    // Format the violations into a readable string
    private function formatViolations($violations): string
    {
        $formatted = '';
        foreach ($violations as $violation) {
            $formatted .= sprintf(
                " - %s: %s\n",
                $violation->getPropertyPath(),
                $violation->getMessage()
            );
    }
        return $formatted;
    }
}
