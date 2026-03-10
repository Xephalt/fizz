<?php

namespace Tests\App\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AnnouncementPopupControllerTest extends WebTestCase
{
    public function test_active_endpoint_returns_json(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/announcement-popups/active');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }
}
