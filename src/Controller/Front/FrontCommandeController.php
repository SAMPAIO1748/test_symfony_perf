<?php

namespace App\Controller\Front;

use DateTime;
use App\Entity\Card;
use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CarRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;

class FrontCommandeController extends AbstractController
{
    /**
     * @Route("cart/add/{id}", name="add_cart")
     */
    public function addCart($id, SessionInterface $sessionInterface)
    {
        // On récupère dans la session l'élément qui s'appelle cart et si il n'existe pas
        // on le créer comme étant un tableau vide.
        $cart = $sessionInterface->get('cart', []);

        /*
        [ 1 => 2,
        17 => 3,
        56 => 4]
        */

        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $sessionInterface->set('cart', $cart);

        return $this->redirectToRoute('front_car_show', ['id' => $id]);
    }

    /**
     * @Route("cart", name="front_show_cart")
     */
    public function showCart(SessionInterface $sessionInterface, CarRepository $carRepository)
    {
        $cart = $sessionInterface->get('cart', []);
        $cartWithData = [];

        foreach ($cart as $id => $quantity) {
            $cartWithData[] = [
                'car' => $carRepository->find($id),
                'quantity' => $quantity
            ];
        }

        return $this->render('front/cart.html.twig', ['items' => $cartWithData]);
    }

    /**
     * @Route("cart/delete/{id}", name="delete_cart")
     */
    public function deleteCart($id, SessionInterface $sessionInterface)
    {
        $cart = $sessionInterface->get('cart', []);

        if (!empty($cart[$id] && $cart[$id] == 1)) {
            unset($cart[$id]);
        } else {
            $cart[$id]--;
        }

        $sessionInterface->set('cart', $cart);

        return $this->redirectToRoute('front_show_cart');
    }

    /**
     * @Route("create/commande", name="create_command")
     */
    public function createCommand(
        SessionInterface $sessionInterface,
        CarRepository $carRepository,
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        UserRepository $userRepository,
        MailerInterface $mailerInterface
    ) {

        $commande = new Commande();

        $commandeForm = $this->createForm(CommandeType::class, $commande);

        $commandeForm->handleRequest($request);

        $cart = $sessionInterface->get('cart', []);

        $price_commande = 0;

        $commande->setDate(new \DateTime("NOW"));
        $commande->setPrice($price_commande);

        if ($commandeForm->isSubmitted() && $commandeForm->isValid()) {

            $entityManagerInterface->persist($commande);
            $entityManagerInterface->flush();

            foreach ($cart as $id_car => $quantity) {
                $card = new Card();
                $card->setCommande($commande);
                $car = $carRepository->find($id_car);
                $card->setCar($car);
                $card->setQuantity($quantity);
                $price_car = $car->getPrice();
                $card->setPriceCar($price_car);
                $price_commande = $price_commande + ($price_car * $quantity);
                $car_stock = $car->getStock();
                $car_stock_final = $car_stock - $quantity;
                $car->setStock($car_stock_final);
                $entityManagerInterface->persist($car);
                $entityManagerInterface->persist($card);
                $entityManagerInterface->flush();
                unset($cart[$id_car]);
                $sessionInterface->set('cart', $cart);
            }

            $commande->setPrice($price_commande);

            $user = $this->getUser();

            if ($user) {
                $user_mail = $user->getUserIdentifier();
                $user_true = $userRepository->findOneBy(['email' => $user_mail]);
                $commande->setUser($user_true);

                // création de l'email
                $email = (new TemplatedEmail())
                    ->from("test@test.com") // origine de l'envoie
                    ->to($user_mail) // destination
                    ->subject('Commande') // sujet du mail
                    ->htmlTemplate('front/email.html.twig') // fichier twig qui contiendra le code html du mail
                    ->context([
                        'price' => $price_commande // variable contenu dans le fichier twig
                    ]);

                $mailerInterface->send($email);
            } else {
                $commande->setUser(NULL);
            }

            $entityManagerInterface->persist($commande);
            $entityManagerInterface->flush();

            return $this->redirectToRoute("home");
        }

        return $this->render("front/commande_create.html.twig", ['commandeForm' => $commandeForm->createView()]);
    }
}
