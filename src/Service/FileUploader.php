<?php

namespace App\Service;

use App\Entity\Picture;
use App\Entity\Trick;
use App\Repository\PictureRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FileUploader
{
    private $targetDirectory;
    const DEFAULT_PICTURE = 'c9c2ec87388e6c5de07df7ca945a9773.jpg';
    const DEFAULT_AVATAR = 'ba842b987877357e9758acdd5946d67a.png';

    public function __construct($targetDirectory, PictureRepository $pictureRepository)
    {
        $this->targetDirectory = $targetDirectory;
        $this->pictureRepository = $pictureRepository;
    }

    public function upload(UploadedFile $file)
    {
        $fileName = md5(uniqid()).'.'.$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }

        return $fileName;
    }

    public function removeFile($picture)
    {
        $fileName = $picture->getName();            

        $filesystem = new Filesystem();
        $filesystem->remove($this->targetDirectory . $fileName);
    }

    public function removeOldFile($name)
    {
        unlink($this->targetDirectory . '/' . $name);
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    public function handlePictures($uow) 
    {
        $im = $uow->getIdentityMap()["App\Entity\Picture"];

        foreach ($im as $picture) {
            
            if ($picture->getName()) {                
                $originalObject = $uow->getOriginalEntityData($picture);
                $oldName = $originalObject["name"];   

                if ($picture->getName() !== $oldName) {
                    $this->removeOldFile($oldName);
                }

            } else {
                $oldName = $this->getPictureName($uow, $picture);
                $picture->setName($oldName);
            }           
        }
    }

    public function checkPictureName(Picture $picture) 
    {
        if ($picture->getName()) {
            $file = $picture->getName();
            $fileName = $this->upload($file);

            $picture->setName($fileName);
        }

        return;
    }

    public function getPictureName($uow, $object) 
    {
        $originalObject = $uow->getOriginalEntityData($object);
        $name = $originalObject["name"];
        return $name;
    }

    public function setDefaultPicture(Trick $trick)
    {
        $pictures = $trick->getPictures();
        if (!null === $pictures) {
            return;
        }
        $picture = new Picture;
        $picture->setName(self::DEFAULT_PICTURE);
        
        $trick->addPicture($picture);
    }
}