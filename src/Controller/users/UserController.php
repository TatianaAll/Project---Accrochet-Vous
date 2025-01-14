<?php

namespace App\Controller\users;

use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route(path: "/user/{id}/profile", name: "user_profile", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function showProfile(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            $this->addFlash("error", "Cet utilisateur n'existe pas.");
            return $this->redirectToRoute("home");
        }

        return $this->render('users/profile/profile.html.twig', ["user" => $user]);
    }

    #[Route(path: "/user/my-profile", name: "user_current_profile", methods: ["GET", "POST"])]
    public function showCurrentUserProfile(): Response
    {
        $user = $this->getUser();
        //dd($user);

        if (!$user) {
            $this->addFlash("error", "Cet utilisateur n'existe pas.");
            return $this->redirectToRoute("home");
        }

        return $this->render('users/profile/profile.html.twig', ["user" => $user]);
    }

    #[Route(path: "/user/my-profile/update-password", name: "user_profile_update_password", methods: ["POST", "GET"])]
    public function updateCurrentUserProfilePassword(EntityManagerInterface      $entityManager,
                                                     Request $request,
                                                     UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        if ($request->getMethod() === "POST") {
            if ($_POST['password'] && $_POST['password2'] && $_POST['password'] === $_POST['password2']) {
                $plaintextPassword = $_POST['password'];
                //dd($plaintextPassword);
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $plaintextPassword
                );
                $user->setPassword($hashedPassword);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash("success", "Mot de passe modifié");
                return $this->redirectToRoute("user_current_profile");
            }

        }
        return $this->render("users/profile/update_password.html.twig");

    }

    #[Route(path:"/user/my-profile/update-bio", name: "user_profile_update_bio", methods: ["POST", "GET"])]
    public function updateCurrentUserProfileBio(EntityManagerInterface $entityManager, Request $request) :Response
    {
        $user = $this->getUser();

        if ($request->isMethod("POST")) {
            $newBio = $request->request->get('biography');
            $user->setBiography($newBio);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash("success", "Biographie modifiée");
            return $this->redirectToRoute("user_current_profile");
        }
        return $this->render("users/profile/update_bio.html.twig", ["user" => $user]);
    }

}
