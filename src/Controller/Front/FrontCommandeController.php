<?php

namespace App\Controller\Front;

use App\Repository\CarRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

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
}
