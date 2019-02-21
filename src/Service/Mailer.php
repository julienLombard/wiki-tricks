<?php

namespace App\Service;

use App\Entity\User;
use App\Security\Token;

class Mailer {

    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var \Twig_Environment
     */
    private $twig;
    /**
     * @var string
     */
    private $mailFrom;

    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig, string $mailFrom, Token $token)  
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->mailFrom = $mailFrom;
        $this->token = $token;
    }

    public function sendConfirmationEmail(User $user)
    {
        $body = $this->twig->render('email/registration.html.twig', [ 
            'user' => $user
        ]);

        $message = (new \Swift_Message())
            ->setSubject('Welcome !')
            ->setFrom($this->mailFrom)
            ->setTo($user->getEmail())
            ->setBody($body, 'text/html');

        $this->mailer->send($message);
    }

    public function sendResetPassword(User $user)
    {
        // $user->setResetToken($this->token->getToken(30));

        $body = $this->twig->render('email/reset_password.html.twig', [ 
            'user' => $user
        ]);

        $message = (new \Swift_Message())
            ->setSubject('Reset Password')
            ->setFrom($this->mailFrom)
            ->setTo($user->getEmail())
            ->setBody($body, 'text/html');

        $this->mailer->send($message);
    }
}