<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Trick;
use App\Entity\Comment;
use App\Entity\Picture;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        // create 1 Pictures
        for ($i = 0; $i < 1; $i++) {
            $picture = new Picture();
            $picture->setUrl('http://placehold.it/350x150');
            $manager->persist($picture);

            // create 1 User
            for ($j = 0; $j < 1; $j++) {
                $user = new User();
                $user->setUsername('user_'.$j);
                $user->setEmail('user_'.$j.'@gmail.com');
                $user->setPassword('0000');
                $user->setRegisteredAt(new \DateTimeImmutable());
                $user->setValidate(true);
                $user->setPicture($picture);
                $manager->persist($user);

                // create 2 Categories
                for ($k = 0; $k < 2; $k++) {
                    $category = new Category();
                    $category->setName('category_'.$k);
                    $manager->persist($category);

                    // create 15 Tricks and Picture
                    for ($l = 0; $l < 15; $l++) {
                        
                        $picture = new Picture();
                        $picture->setUrl('http://placehold.it/350x150');
                        $manager->persist($picture);

                        $trick = new Trick();
                        $trick->setName('trick_'.$l);
                        $trick->setContent('<p>Aliquam dapibus urna nec varius vestibulum.</p>');
                        $trick->setPublishedAt(new \DateTimeImmutable());
                        $trick->setCategory($category);
                        $trick->addPicture($picture);
                        $manager->persist($trick);

                        // create 10 Comments
                        for ($m = 0; $m < 10; $m++) {
                            $comment = new Comment();
                            $comment->setTrick($trick);
                            $comment->setUser($user);
                            $comment->setContent('Comment_'.$m);
                            $comment->setPublishedAt(new \DateTimeImmutable());
                            $manager->persist($comment);
                        }
                    }
                }
            }
        }

        $manager->flush();
    }
}
