<?php

namespace App\Controller\Front;

use App\Repository\CarRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontCarController extends AbstractController
{
    /**
     * @Route("cars", name="front_car_list")
     */
    public function carList(CarRepository $carRepository)
    {
        $cars = $carRepository->findAll();

        return $this->render("front/car_list.html.twig", ['cars' => $cars]);
    }

    /**
     * @Route("car/{id}", name="front_car_show")
     */
    public function carShow($id, CarRepository $carRepository)
    {
        $car = $carRepository->find($id);

        return $this->render("front/car_show.html.twig", ['car' => $car]);
    }
}
