<?php

namespace App\Tests\Service;

use App\Service\MyClient;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MyClientTest extends KernelTestCase
{
    public function testGetUserByEmail() 
    {
        self::bootKernel();
        
        $container = static::getContainer();
        
        $myClient = $container->get(MyClient::class);
        $json = $myClient->getUserByEmail('john@doe.com');
        $content = json_decode($json);
        $email = $content->user->email;
        $this->assertEquals('john@doe.com',$email);
    }
}