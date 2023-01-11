<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;

class LoginFormAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    use TargetPathTrait;
    
    private UserPasswordHasherInterface $passwordHasher;
    private UserProvider $userProvider;
    private RouterInterface $router;


    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        UserProvider $userProvider,
        RouterInterface $router
        ) {
        $this->passwordHasher = $passwordHasher;
        $this->userProvider = $userProvider;
        $this->router = $router;
    }

    public function supports(Request $request): ?bool
    {
        return ($request->getPathInfo() === '/login' && $request->isMethod('POST'));
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        
        return new Passport(
            new UserBadge($email, function($userIdentifier){
                $user = $this->userProvider->loadUserByIdentifier($userIdentifier);
                
                if (!$user) {
                    throw new UserNotFoundException();
                }
                
                return $user;
            }),
            new CustomCredentials(function($credentials, User $user) {
                
                return $this->passwordHasher->isPasswordValid($user, $credentials);
            }, $password),
            [
                new CsrfTokenBadge(
                    'xyz',
                    $request->request->get('csrf_token')
                ),
                new RememberMeBadge()
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
//        if ($target = $this->getTargetPath($request->getSession(), $firewallName)) {
//            return new RedirectResponse($target);
//        }

        return new RedirectResponse($this->router->generate('home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->getFlashBag()->add('error', 'Email or password not valid');
//        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);

        return new RedirectResponse($this->router->generate('login'));
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        /*
         * If you would like this class to control what happens when an anonymous user accesses a
         * protected page (e.g. redirect to /login), uncomment this method and make this class
         * implement Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface.
         *
         * For more details, see https://symfony.com/doc/current/security/experimental_authenticators.html#configuring-the-authentication-entry-point
         */
        
        return new RedirectResponse(
            $this->router->generate('login')
        );
    }
}
