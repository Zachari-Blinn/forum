<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class UserController extends AbstractController
{
    /**
     * @Route("/user/profil/show/{slug}", name="app_user_profil_show", methods={"GET"})
     */
    public function showProfil(User $user)
    {
        return $this->render('user/show.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * Edit user page
     * 
     * @Route("/user/profil/edit/{id}", name="app_user_profil_edit", methods={"GET","POST"})
     */
    public function editProfil(User $user, Request $request, SluggerInterface $slugger, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProfilType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $imageFile = $form->get('image')->getData();

            if($imageFile)
            {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try
                {
                    $imageFile->move($this->getParameter('profil_image_directory'), $newFilename);
                }
                catch (FileException $e)
                {
                    // todo error
                }

                $user->setImageFilename($newFilename);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('app_default'));
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/logout", name="app_logout", methods={"GET"})
     */
    public function logout()
    {
        // controller can be blank: it will never be executed!
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }
}
