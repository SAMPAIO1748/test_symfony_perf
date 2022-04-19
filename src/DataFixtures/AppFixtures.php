<?php

namespace App\DataFixtures;

use Faker;
use App\Entity\Car;
use App\Entity\Commande;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $faker = Faker\Factory::create();

        for ($i = 0; $i < 10; $i++) {

            $car = new Car;

            $car->setModel($faker->word);
            $car->setMarque($faker->word);
            $car->setPrice($faker->randomFloat($nbMaxDecimals = 2, $min = 15000, $max = 150000));
            $car->setStock($faker->randomDigit);

            $manager->persist($car);
        }

        for ($i = 0; $i < 15; $i++) {

            $commande = new Commande();

            $commande->setDate($faker->dateTime($max = 'now', $timezone = null));
            $commande->setPrice($faker->randomFloat($nbMaxDecimals = 2, $min = 15000, $max = 1500000));
            $commande->setAdress($faker->streetAddress);
            $commande->setCity($faker->city);
            $commande->setName($faker->lastName);
            $commande->setFirstname($faker->firstName($gender = null));

            $manager->persist($commande);
        }

        $manager->flush();

        // Remplir la table commande avec des données aléatoires.
    }
}
