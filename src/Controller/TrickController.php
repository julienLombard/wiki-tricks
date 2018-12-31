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
use App\Repository\TrickRepository;
// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\Serializer\SerializerInterface;
// use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
// use Symfony\Component\Serializer\Encoder\XmlEncoder;
// use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
// use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
// use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
// use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;

class TrickController extends Controller {

    /**
     * @Route("/{page<\d+>?1}", name="homepage")
     */
    public function index(TrickRepository $repo, $page, Pagination $pagination){
        
        $pagination->setEntityClass(Trick::class)
                    ->setPage($page);

        return $this->render('home.html.twig',[
            'pagination' => $pagination
            ]);
    }

    /**
     * @Route("/load", name="load_more_tricks")
     */
    public function loadMoreTricks(TrickRepository $repo): Response {

        // $data = $repo->findBy([], [], $limit, $offset);
        $data = $repo->findBy([], [], 5, 5);

        // var_dump($data);
        
        // $normalizer = new JsonSerializableNormalizer();
        // $normalizer = array(new ObjectNormalizer());
        // $normalizer->setCircularReferenceLimit(1);
        // $normalizer->setCircularReferenceHandler(function($Tricks){
        //     // @var Tricks $object
        //     return $Tricks->getId();
        // });
        // $serializer = new Serializer([$normalizer], [new JsonEncoder()]);

        // $json = $serializer->serialize($Tricks, 'json');

        // $this->assertJson($json);
        // $this->assertCount(1, json_decode($json));

        // return $this->json([$data], 200);
        // return new Response($this->json([$data], 200));
        return new JsonResponse($data);

    }

    /**
     * @Route("/show/{slug}", name="trick_show")
     */
    public function showTrick(Trick $trick, Request $request, ObjectManager $manager){

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
                'id' => $trick->getId(),
                // 'pagination' => $pagination
                ]);
        }

        return $this->render('trick_show.html.twig', [
            'trick' => $trick,
            'commentForm' => $form->createView()
            ]);
    }

    /**
     * @Route("/create-trick", name="trick_create")
     */
    public function createTrick(Request $request, ObjectManager $manager){

        $trick = new Trick();

        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($trick->getPictures() as $picture) {
                $picture->setTrick($trick);
                $manager->persist($picture);
            }

            foreach ($trick->getVideos() as $video) {
                $video->setTrick($trick);
                $manager->persist($video);
            }

            $trick->setPublishedAt(new \DateTimeImmutable());

            $manager->persist($trick);
            $manager->flush();

            $this->addFlash(
                'Success',
                "La figure a bien été ajouté !"
            );

            return $this->redirectToRoute('trick_show',['id' => $trick->getId()]);
        }

        return $this->render('trick_create.html.twig', ['formTrick' =>$form->createView()]);
    }

    /**
     * @Route("/modify-trick/{slug}", name="trick_modify")
     */
    public function modifyTrick(Trick $trick,Request $request, ObjectManager $manager){

        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($trick->getPictures() as $picture) {
                $picture->setTrick($trick);
                $manager->persist($picture);
            }

            foreach ($trick->getVideos() as $video) {
                $video->setTrick($trick);
                $manager->persist($video);
            }

            $trick->setModifiedAt(new \DateTimeImmutable());

            $manager->persist($trick);
            $manager->flush();

            $this->addFlash(
                'Success',
                "La figure a bien été modifié !"
            );

            return $this->redirectToRoute('trick_show',['id' => $trick->getId()]);
        }

        return $this->render('trick_modify.html.twig', ['formTrick' =>$form->createView()]);
    }

    /**
     * @Route("/delete/{slug}", name="trick_delete")
     */
    public function deleteTrick(Trick $trick, ObjectManager $manager){

        $manager->remove($trick);
        $manager->flush();

        $this->addFlash(
            'Success',
            "La figure a bien été supprimé !"
        );

        return $this->redirectToRoute('homepage');
    }
}