<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Event\UserRegisterEvent;
use App\Security\TokenGenerator;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{

    /**
     * login
     * 
     * @return Response
     * 
     * @Route("/login", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, \Swift_Mailer $mailer)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $message = (new \Swift_Message('You Got Mail!'))
            ->setFrom('jlombard.test5@gmail.com')
            ->setTo('jlombard.test5@gmail.com')
            ->setBody('vous êtes connecté');

        $mailer->send($message);

        // dump($error);
        // exit;

        return $this->render('security/login.html.twig', [ 
            'hasError' => $error !== null,
            'username' => $lastUsername
            ]);
    }

    /**
     * logout
     *
     * @return void
     * 
     * @Route("/logout", name="security_logout")
     */
    public function logout()
    {
    }

    /**
     * register
     *
     * @return void
     * 
     * @Route("/register", name="security_register")
     */
    public function register(
        Request $request, 
        ObjectManager $manager, 
        UserPasswordEncoderInterface $encoder, 
        EventDispatcherInterface $eventDispatcher,
        TokenGenerator $tokenGenerator
    ) {
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $password = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $user->setConfirmationToken($tokenGenerator->getRandomSecureToken(30));
            $user->setValidate(False);
            $user->setRegisteredAt(new \DateTimeImmutable());

            $manager->persist($user);
            $manager->flush();

            $userRegisterEvent = new UserRegisterEvent($user);
            $eventDispatcher->dispatch(
                UserRegisterEvent::NAME,
                $userRegisterEvent
            );

            //Envoyer un email qui contient l'id/username de l'utilisateur
            // et qui contient un lien vers une route qui va configurer validate=true

            $this->addFlash(
                'success', 
                "Votre compte à bien été créé ! Un Email de validation vous a été envoyé"
            );

            return $this->redirectToRoute('homepage');
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/confirm/{token}", name="security_confirm")
     */
    public function confirm(
        string $token,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ) {
        $user = $userRepository->findOneBy([
            'confirmationToken' => $token
        ]);

        if (null !== $user) {
            $user->setValidate(true);
            $user->setConfirmationToken('');

            $entityManager->flush();
        }

        return new Response(
            $this->render(
                'security/confirmation.html.twig',
                [
                    'user' => $user,
                ]
            )
        );
    }
}
