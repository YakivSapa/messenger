<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
abstract class AbstractTestCase extends TestCase
{
    // Assertion Helpers
    protected function assertArraysEqual(array $expected, array $actual, string $message = ''): void
    {
        $this->assertEqualsCanonicalizing($expected, $actual, $message);
    }
    protected function assertArraysNotEqual(array $expected, array $actual, string $message = ''): void
    {
        $this->assertNotEqualsCanonicalizing($expected, $actual, $message);
    }
    protected function assertValidEmail(string $email, string $message = ''): void
    {
        $this->assertTrue(
            filter_var($email, FILTER_VALIDATE_EMAIL) !== false,
            $message ?: "'{$email}' is not a valid email address."
        );
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
