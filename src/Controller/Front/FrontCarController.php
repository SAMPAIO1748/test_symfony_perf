<?php

namespace App\Controller\Front;

use App\Entity\Like;
use App\Repository\CarRepository;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;
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

    /**
     * @Route("like/car/{id}", name="car_like")
     */
    public function likeCar(
        $id,
        CarRepository $carRepository,
        EntityManagerInterface $entityManagerInterface,
        LikeRepository $likeRepository
    ) {

        $car = $carRepository->find($id);
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'code' => 403,
                'message' => "Vous devez vous connecter"
            ], 403);
        }

        if ($car->isLikeByUser($user)) {
            $like = $likeRepository->findOneBy(
                [
                    'car' => $car,
                    'user' => $user
                ]
            );

            $entityManagerInterface->remove($like);
            $entityManagerInterface->flush();

            return $this->json([
                'code' => 200,
                'message' => "Like supprimé",
                'likes' => $likeRepository->count(['car' => $car])
            ], 200);
        }

        $like = new Like();

        $like->setCar($car);
        $like->setUser($user);

        $entityManagerInterface->persist($like);
        $entityManagerInterface->flush();

        return $this->json([
            'code' => 200,
            'message' => "Like enregistré",
            'likes' => $likeRepository->count(['car' => $car])
        ], 200);
    }
}
