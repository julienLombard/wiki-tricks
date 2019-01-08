<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\Video;
use App\Entity\Comment;
use App\Entity\Picture;
use App\Form\TrickType;
use App\Form\VideoType;
use App\Form\CommentType;
use App\Form\PictureType;
use App\Service\Pagination;
use App\Service\FileUploader;
use App\Repository\TrickRepository;
use App\Repository\CommentRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * Class TrickController
 * @package App\Controller
 */
class TrickController extends AbstractController {

    /**
     * index
     *
     * @param TrickRepository $repo
     * @param Pagination $pagination
     * @return void
     * 
     * @Route("/", name="homepage")
     */
    public function index(TrickRepository $repo, Pagination $pagination){
        
        $pagination->setEntityClass(Trick::class)
                    ->setLimit(9)
                    ->setPage(1);

        return $this->render('home.html.twig',[
            'pagination' => $pagination
        ]);
    }

    /**
     * loadMoreTricks
     *
     * @param TrickRepository $repo
     * @param mixed $page
     * @return void
     * 
     * @Route("/load/{page<\d+>?1}", name="load_more_tricks")
     */
    public function loadMoreTricks(TrickRepository $repo, $page): Response {

        $tricks = $repo->findBy([], ["id" => "asc"], 9, ($page - 1) * 9);

        return $this->render("_tricks.html.twig", ["tricks" => $tricks]);

    }

    /**
     * showTrick
     *
     * @param Trick $trick
     * @param Request $request
     * @param ObjectManager $manager
     * @param mixed $page
     * @param CommentRepository $commentRepo
     * @return void
     * 
     * @Route("/show/{slug}/{page<\d+>?1}", name="trick_show")
     */
    public function showTrick(Trick $trick, Request $request, ObjectManager $manager, $page, CommentRepository $commentRepo){

        $comments = $commentRepo->findBy(["trick" => $trick], ["publishedAt" => "DESC"], 6, ($page - 1) * 6);

        $comment = new Comment();
        
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        // $pagination->setEntity(Comment::class)
        // ->setPage($page);

        if ($form->isSubmitted() && $form->isValid()) {

            $comment->setPublishedAt(new \DateTimeImmutable())
                    ->setTrick($trick);
                    // ->setUser($user);

            $manager->persist($comment);
            $manager->flush();

            return $this->redirectToRoute('trick_show', [
                'slug' => $trick->getSlug(),
                'comments' => $comments
                // 'pagination' => $pagination
                ]);
        }

        return $this->render('trick_show.html.twig', [
            'trick' => $trick,
            'commentForm' => $form->createView(),
            'comments' => $comments
            ]);
    }

    /**
     * loadMorecomments
     *
     * @param Trick $trick
     * @param CommentRepository $repo
     * @param mixed $page
     * @return void
     * 
     * @Route("/loadComments/{id<\d+>?1}/{page<\d+>?1}", name="load_more_comments")
     */
    public function loadMorecomments(Trick $trick, CommentRepository $repo, $page): Response {

        $comments = $repo->findBy(["trick" => $trick],["publishedAt" => "DESC"], 6, ($page - 1) * 6);

        return $this->render("_comments.html.twig", ["comments" => $comments]);

    }

    /**
     * createTrick
     *
     * @param Request $request
     * @param ObjectManager $manager
     * @param FileUploader $fileUploader
     * @return void
     * 
     * @Route("/create-trick", name="trick_create")
     */
    public function createTrick(Request $request, ObjectManager $manager, FileUploader $fileUploader){

        $trick = new Trick();

        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($trick->getPictures() as $picture) {
                // $picture->setTrick($trick);
                // $manager->persist($picture);

                $file = $picture->getName();
                $fileName = $fileUploader->upload($file);
                // $fileName = md5(uniqid()).'.'.$file->guessExtension();
                // $file->move($this->getParameter('picture_directory'), $fileName);
                $picture->setName($fileName);
            }

            // foreach ($trick->getVideos() as $video) {
            //     $video->setTrick($trick);
            //     $manager->persist($video);
            // }

            $trick->setPublishedAt(new \DateTimeImmutable());

            $manager->persist($trick);
            // $manager->persist($form->getData());
            $manager->flush();

            $this->addFlash(
                'success',
                "La figure a bien été ajouté !"
            );

            return $this->redirectToRoute('trick_show',['slug' => $trick->getSlug()]);
        }

        return $this->render('trick_create.html.twig', ['formTrick' =>$form->createView()]);
    }

    /**
     * modifyTrick
     *
     * @param Trick $trick
     * @param Request $request
     * @param ObjectManager $manager
     * @param FileUploader $fileUploader
     * @return void
     * 
     * @Route("/modify-trick/{slug}", name="trick_modify")
     */
    public function modifyTrick(Trick $trick,Request $request, ObjectManager $manager, FileUploader $fileUploader){

        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           
            foreach ($trick->getPictures() as $picture) {
                //     $filesystem = new Filesystem();
                //     $fileSystem->remove(array('picture_directory', '%kernel.project_dir%/public/pictures/', $file));
                // $picture->setTrick($trick);
                // $manager->persist($picture);

                $file = $picture->getName();
                $fileName = $fileUploader->upload($file);
                // $fileName = md5(uniqid()).'.'.$file->guessExtension();
                // $file->move($this->getParameter('picture_directory'), $fileName);
                $picture->setName($fileName);
            }

            // foreach ($trick->getVideos() as $video) {
            //     $video->setTrick($trick);
            //     $manager->persist($video);
            // }

            $trick->setModifiedAt(new \DateTimeImmutable());

            $manager->persist($trick);
            $manager->flush();

            $this->addFlash(
                'success',
                "La figure a bien été modifié !"
            );

            return $this->redirectToRoute('trick_show',['slug' => $trick->getSlug()]);
        }

        return $this->render('trick_modify.html.twig', [
            'formTrick' =>$form->createView(),
            'trick' => $trick
            ]);
    }

    /**
     * deleteTrick
     *
     * @param Trick $trick
     * @param ObjectManager $manager
     * @return void
     * 
     * @Route("/delete/{slug}", name="trick_delete")
     */
    public function deleteTrick(Trick $trick, ObjectManager $manager){

        $manager->remove($trick);
        $manager->flush();

        $this->addFlash(
            'success',
            "La figure a bien été supprimé !"
        );

        return $this->redirectToRoute('homepage');
    }

    /**
     * deleteHomeTrick
     *
     * @param Trick $trick
     * @param ObjectManager $manager
     * @return void
     * 
     * @Route("/deleteHome/{id<\d+>?1}", name="trick_delete")
     */
    public function deleteHomeTrick(Trick $trick, ObjectManager $manager){

        $manager->remove($trick);
        $manager->flush();

        $this->addFlash(
            'success',
            "La figure a bien été supprimé !"
        );

        return $this->render("_flash_message.html.twig", ["label" => "success"]);
    }
}