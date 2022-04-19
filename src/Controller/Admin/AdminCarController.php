<?php

namespace App\Controller\Admin;

use App\Entity\Car;
use App\Form\CarType;
use App\Repository\CarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminCarController extends AbstractController
{
    /**
     * @Route("/admin/cars", name="admin_list_car")
     */
    public function adminListCar(CarRepository $carRepository)
    {
        $cars = $carRepository->findAll();

        return $this->render("admin/list_car.html.twig", ['cars' => $cars]);
    }

    /**
     * @Route("admin/car/{id}", name="admin_show_car")
     */
    public function adminShowCar($id, CarRepository $carRepository)
    {
        $car = $carRepository->find($id);

        return $this->render("admin/show_car.html.twig", ['car' => $car]);
    }

    /**
     * @Route("admin/update/car/{id}", name="admin_update_car")
     */
    public function updateCar(
        $id,
        CarRepository $carRepository,
        Request $request,
        EntityManagerInterface $entityManagerInterface
    ) {
        $car = $carRepository->find($id);

        $carForm = $this->createForm(CarType::class, $car);

        $carForm->handleRequest($request);

        if ($carForm->isSubmitted() && $carForm->isValid()) {
            $entityManagerInterface->persist($car);
            $entityManagerInterface->flush();

            return $this->redirectToRoute("admin_list_car");
        }

        return $this->render('admin/car_form.html.twig', ['carForm' => $carForm->createView()]);
    }

    /**
     *@Route("admin/create/car", name="admin_create_car")
     */
    public function createCar(Request $request, EntityManagerInterface $entityManagerInterface)
    {
        $car = new Car();

        $carForm = $this->createForm(CarType::class, $car);

        $carForm->handleRequest($request);

        if ($carForm->isSubmitted() && $carForm->isValid()) {
            $entityManagerInterface->persist($car);
            $entityManagerInterface->flush();

            return $this->redirectToRoute("admin_list_car");
        }

        return $this->render('admin/car_form.html.twig', ['carForm' => $carForm->createView()]);
    }

    /**
     * @Route("admin/delete/car/{id}", name="admin_delete_car")
     */
    public function carDelete(CarRepository $carRepository, $id, EntityManagerInterface $entityManagerInterface)
    {
        $car = $carRepository->find($id);

        $entityManagerInterface->remove($car);
        $entityManagerInterface->flush();

        return $this->redirectToRoute("admin_list_car");
    }
}
