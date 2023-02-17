<?php

namespace App\Tests\Service;


use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Service\MyClient;

class MyClientMockTest extends TestCase
{

    public function testGetUserByEmail(): void
    {
        $client = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $params = $this->createMock(ParameterBagInterface::class);
        
        $response
            ->expects(self::once())
            ->method('getContent')
            ->willReturn(
                json_encode([
                    'user' => [
                        'id' => 1,
                        'email' => 'john@doe.com'
                    ]
                ])
            );
        $client
            ->method('request')
            ->with('GET', '/v1/user', ['query' => ['email' => 'john@doe.com']])
            ->willReturn($response);
        
        $myClient = new MyClient($client, $params);
        $jsonContent = $myClient->getUserByEmail('john@doe.com');
        $content = json_decode($jsonContent);
        $email = $content->user->email;
        self::assertSame('john@doe.com', $email);
    }
    
    
}
