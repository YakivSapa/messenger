<?php

namespace App\Tests\Application\Controller;

use App\Tests\AbstractWebTestCase;

class ProfileControllerTest extends AbstractWebTestCase
{
    public function testUserCanAccessOwnProfile(): void
    {
        $user = $this->createAndLoginUser();
        $uuid = (string)$user->getUuid();
        $response = $this->get("/profile/{$uuid}");
        $this->assertStatus(200);
    }
}
