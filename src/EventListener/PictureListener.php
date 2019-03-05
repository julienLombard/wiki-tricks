<?php

namespace App\EventListener;

use App\Entity\Picture;
use App\Service\FileUploader;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;

class PictureListener
{

    public function __construct(FileUploader $fileUploader) 
    {
        $this->fileUploader = $fileUploader;         
    }

    public function preFlush(PreFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork(); 

        if ($uow->getIdentityMap()) {
            $this->fileUploader->handlePictures($uow);
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        // only act on some "Product" entity
        if (!$entity instanceof Picture) {
            return;
        }

        $this->fileUploader->removeFile($entity);
    }

}