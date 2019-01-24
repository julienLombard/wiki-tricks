<?php

namespace App\EventListener;

use App\Entity\User;
use App\Security\Token;
use App\Service\Mailer;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
// use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class UserListener
{

    public function __construct(
        Mailer $mailer, 
        Token $token, 
        UserPasswordEncoderInterface $encoder) 
    {
        $this->mailer = $mailer;
        $this->token = $token;
        $this->encoder = $encoder;
    }

    public function prePersist(User $user)
    {
        $this->encodePassword($user);

        $user->setConfirmationToken($this->token->getToken(30));
        $user->setValidate(False);
        $user->setRegisterDate();

        $this->mailer->sendConfirmationEmail($user);
    }

    public function preUpdate(User $user)
    {
        $this->encodePassword($user);
    }

    public function encodePassword(User $user)
    {
        if (null === $user->getPlainPassword() ) {
            return;
        }

        $user->setPassword($this->encoder->encodePassword($user, $user->getPlainPassword()));
        $user->setPlainPassword(null);
    }
}