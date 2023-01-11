<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
//use App\Security\User;
use App\Entity\RegisterUser;
use App\Model\UserPassword;

class MyClient 
{
    private HttpClientInterface $client;
    private ParameterBagInterface $params;
    
    public function __construct(
        HttpClientInterface $client,
        ParameterBagInterface $params
        )
    {
        $this->client = $client;
        $this->params = $params;
    }

    /**
     * @throws ExceptionInterface
     */
    public function getUserByEmail(string $email): string
    {
        $response = $this->client->request(
            'GET',
            $this->params->get('api_url') . '/v1/user',
            [
                'query' => [
                    'email' => $email
                ]
            ]
        );
        
        return $response->getContent();
    }


    /**
     * @throws ExceptionInterface
     */
    public function addNewUser(RegisterUser $user, string $password): string
    {
        $response = $this->client->request(
            'POST',
            $this->params->get('api_url') . '/v1/user',
            [
                'json' => [
                    'title' => $user->getTitle(),
                    'first_name' => $user->getFirstName(),
                    'last_name' => $user->getLastName(),
                    'email' => $user->getEmail(),
                    'phone' => $user->getPhone(),
                    'prefix' => $user->getPrefix(),
                    'country' => $user->getCountry(),
                    'newsletter' => $user->isNewsletter(),
                    'created_at' => $user->getCreatedAt()->format('U'),
                    'password' => $password
                ]
            ]
        );
        return $response->getStatusCode();
    }

    public function setUserPassword(UserPassword $password)
    {
        dd($password);
    }
    
}
