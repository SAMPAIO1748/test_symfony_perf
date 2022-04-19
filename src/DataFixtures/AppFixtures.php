<?php

namespace App\DataFixtures;

use App\Entity\Car;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

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

        $manager->flush();
    }
}
