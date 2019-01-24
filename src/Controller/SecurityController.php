<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\Token;
use App\Service\Mailer;
use App\Form\RegistrationType;
use App\Form\ResetPasswordType;
use App\Event\UserRegisterEvent;
use App\Form\ForgotPasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
// use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
     * @return Response
     * 
     * @Route("/register", name="security_register")
     */
    public function register(
        Request $request, 
        ObjectManager $manager, 
        Token $token
    ) {
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $manager->persist($user);
            $manager->flush();

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
     * confirm
     * 
     * @return Response
     * 
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

    /**
     * forgotPassword
     * 
     * @return Response
     * 
     * @Route("/forgot-password", name="security_forgot_password")
     */
    public function forgotPassword(
        Request $request, 
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        Mailer $mailer,
        Token $token
    ) {
        $user = new User();

        $form = $this->createForm(ForgotPasswordType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $user = $userRepository->findOneBy([
                'username' => $user->getUsername()
            ]);

            if(null !== $user) {

                $user->setResetToken($token->getToken(30));
                $entityManager->flush();

                $mailer->sendResetPassword($user);

                $this->addFlash(
                    'success', 
                    "Votre demande reinitialisation a été prise en compte ! Un Email de vérification vous a été envoyé"
                );

                return $this->redirectToRoute('homepage');
            }

            return $this->render('security/forgot_password.html.twig', [
                'form' => $form->createView(),
                'error' => "Nom d'utilisateur inconnu"
            ]);
        }

        return $this->render('security/forgot_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * resetPassword
     * 
     * @return Response
     * 
     * @Route("/reset-password/{token}", name="security_reset_password")
     */
    public function resetPassword(
        Request $request,
        string $token,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ) {
        $user = $userRepository->findOneBy([
            'resetToken' => $token
        ]);

        if (null !== $user) {
            
            $form = $this->createForm(ResetPasswordType::class, $user);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {

                $user->setResetToken('');
                $entityManager->flush();

                $this->addFlash(
                    'success', 
                    "Votre mot de passe a bien été modifié !"
                );

                return $this->redirectToRoute('homepage');
            }

            return new Response(
                $this->render(
                    'security/reset_password.html.twig',
                    [
                        'user' => $user,
                        'form' => $form->createView()
                    ]
                )
            );
        }

        return $this->redirectToRoute('homepage');
    }
}
