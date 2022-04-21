<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManager;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
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

    /**
     * @Route("create/user", name="create_user")
     */
    public function createUser(
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        UserPasswordHasherInterface $userPasswordHasherInterface,
        MailerInterface $mailerInterface
    ) {

        $user = new User();

        $userForm = $this->createForm(UserType::class, $user);

        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {

            $user->setRoles(["ROLE_USER"]);

            $plainPassword = $userForm->get('password')->getData();
            $hashedpasword = $userPasswordHasherInterface->hashPassword($user, $plainPassword);
            $user->setPassword($hashedpasword);

            // Je récupère l'email entré dans le formulaire
            $email_user = $userForm->get('email')->getData();

            $entityManagerInterface->persist($user);
            $entityManagerInterface->flush();

            // création de l'email
            $email = (new Email())
                ->from('test@test.com') // adresse d'envoie 
                ->to($email_user) // adresse de réception
                ->subject('inscription') // sujet du mail
                ->html('<h1>Bienvenue chez nous</h1>'); // contenu html du mail

            // envoie du mail
            $mailerInterface->send($email);

            return $this->redirectToRoute('home');
        }
        return $this->render("front/user_form.html.twig", ['userForm' => $userForm->createView()]);
    }
}
