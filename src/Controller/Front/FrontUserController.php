<?php

namespace App\Controller\Front;

use App\Form\UserType;
use Doctrine\ORM\EntityManager;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class FrontUserController extends AbstractController
{
    /**
     * @Route("update/user", name="front_user_update")
     */
    public function userUpdate(
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        UserRepository $userRepository,
        UserPasswordHasherInterface $userPasswordHasherInterface
    ) {
        // getUser récupère le user connecté 
        $user_connect = $this->getUser();

        $user_email = $user_connect->getUserIdentifier();

        $user = $userRepository->findOneBy(['email' => $user_email]);

        $userForm = $this->createForm(UserType::class, $user);

        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {

            $plainPassword = $userForm->get('password')->getData();
            $hashedpasword = $userPasswordHasherInterface->hashPassword($user, $plainPassword);
            $user->setPassword($hashedpasword);

            $entityManagerInterface->persist($user);
            $entityManagerInterface->flush();

            return $this->redirectToRoute('home');
        }
        return $this->render("front/user_form.html.twig", ['userForm' => $userForm->createView()]);
    }
}
