<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testRegister(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Register');
        $this->assertCount(4, $crawler->filter('.form-controle'));
    }
}
