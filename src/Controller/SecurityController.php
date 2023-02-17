<?php

namespace App\Controller;

use App\Form\UserPasswordType;
use App\Form\UserEmailType;
use App\Form\RegisterUserType;
use App\Entity\RegisterUser;
use App\Repository\RegisterUserRepository;
use App\Model\UserPassword;
use App\Model\UserEmail;
use App\Service\MyClient;
use App\Service\MyVerifyEmailHelper;
use App\Security\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
//use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Doctrine\Persistence\ManagerRegistry;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('security/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername()
        ]);
    }
    
    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        throw new \Exception('logout() should never be reached');
    }
    
    /**
     * @Route("/register", name="register")
     * @throws TransportExceptionInterface
     */
    public function register(
        Request $request, 
        MailerInterface $mailer,
        ManagerRegistry $doctrine,
        RegisterUserRepository $registerUserRepository,
        MyVerifyEmailHelper $helper
        )
    {
        $user = new RegisterUser();
        
        $form = $this->createForm(RegisterUserType::class, $user);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setCreatedAt(new \DateTimeImmutable('now'));
//            $entityManager = $doctrine->getManager();
//            $entityManager->persist($user);
//            $entityManager->flush();
            $registerUserRepository->add($user, true);
            
            $signedUrl = $helper->getSignedUrl($user);

            $email = (new TemplatedEmail())
                ->from(new Address('myClient@gmail.com', 'myClient team'))
                ->to(new Address($user->getEmail(), $user->getFirstName()))
                ->subject('Welcome to myClient!')
                ->htmlTemplate('email/registerConfirm.html.twig')
                ->context([
                    'user' => $user->getFirstName(),
                    'signedUrl' => $signedUrl
                ]);
                
            try {
                $mailer->send($email);
                $this->addFlash('success', 'Your account has been created, please verify it by clicking the activation link sent to your email.');
                
                return $this->redirectToRoute('home');
                
            } catch (TransportExceptionInterface $e) {
                $registerUserRepository->remove($user, true);
                throw $this->createNotFoundException($e->getMessage());
            }
        }
        
        return $this->renderForm('security/register.html.twig', [
            'form' => $form,
        ]);
    }
    
    /**
     * @Route("/verify", name="verify")
     */
    public function verifyUserEmailAndCreatePassword(
        Request $request,
        MyVerifyEmailHelper $helper,
        ManagerRegistry $doctrine,
        RegisterUserRepository $registerUserRepository,
        MyClient $client,
        UserPasswordHasherInterface $passwordHasher
        )
    {
        $id = $request->query->get('id');
//        $user = $doctrine->getRepository(UserRegister::class)->find($id);
        $user = $registerUserRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException();
        }
        
        try {
            $helper->verifySignedUrl($user, $request->getUri());
                
        } catch (VerifyEmailExceptionInterface $e) {
            switch(true) {
                case $e instanceof Exception\ExpiredSignatureException:
                    $message = 'expired signature';
                    break;
                case $e instanceof Exception\InvalidSignatureException:
                    $message = 'invalid signature';
                    break;
                case $e instanceof Exception\WrongEmailVerifyException:
                    $message = 'wrong email';
                    break;
                default:
                    $message = 'something is wrong';
            }
            $this->addFlash('error', $message);
            return $this->redirectToRoute('register');
        }
        
        $userPassword = new UserPassword();
        $form = $this->createForm(UserPasswordType::class, $userPassword);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordHasher->hashPassword(
                new User(),
                $userPassword->getPassword()
            );
            try {
                $statusCode = $client->addNewUser($user, $password);
                if ($statusCode != Response::HTTP_CREATED) {
                    $this->addFlash('success', sprintf('Dear %s %s %s, for some reason your registration has not been successful. Please contact the webmaster.', $user->getTitle(), $user->getFirstName(), $user->getLastName()));
                    return $this->redirectToRoute('home');
                }
                $entityManager = $doctrine->getManager();
                $entityManager->remove($user);
                $entityManager->flush();

                $this->addFlash('success', sprintf('Dear %s %s %s, you can log in now with your email and password now.', $user->getTitle(), $user->getFirstName(), $user->getLastName()));
                return $this->redirectToRoute('login');

            } catch (ExceptionInterface $exception) {
                throw $this->createNotFoundException($exception->getMessage());
            }
        }
        return $this->renderForm('security/userPassword.twig', [
            'form' => $form
        ]);
    }

    /**
     * @Route("/new-password", name="new-password")
     */
    public function createNewPassword(
        Request $request,
        MyClient $myClient
    )
    {
        $userEmail = new UserEmail();
        $form = $this->createForm(UserEmailType::class, $userEmail);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $content = $myClient->getUserByEmail($userEmail->getEmail());
                $user = json_decode($content)->user;
                if (!$user) {
                    $this->addFlash('error', sprintf('No registration with the email address "%s" has been found', $userEmail->getEmail()));
                    return $this->redirectToRoute('new-password');
                }
            } catch (ExceptionInterface $exception) {
                throw $this->createNotFoundException($exception->getMessage());
            }
        }
        return $this->renderForm('security/userEmail.twig', [
            'form' => $form
        ]);
    }
}
