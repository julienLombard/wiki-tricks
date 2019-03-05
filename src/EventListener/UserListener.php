<?php

namespace App\EventListener;

use App\Entity\User;
use App\Entity\Picture;
use App\Security\Token;
use App\Service\Mailer;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
// use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class UserListener
{
    const DEFAULT_AVATAR = 'ba842b987877357e9758acdd5946d67a.png';

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
        $user->setRoles([User::ROLE_ADMIN]);
        $user->setRegisterDate();
        
        $this->setDefaultAvatar($user);

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

    public function setDefaultAvatar(User $user)
    {
        $picture = $user->getPicture();
        
        if (!null === $picture) {
            return;
        }

        $picture = new Picture;
        $picture->setName(self::DEFAULT_AVATAR);
        
        $user->setPicture($picture);
    }
}