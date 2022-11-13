<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $titles = ['Fallout', 'Don’t Starve', 'Baldur’s Gate', 'Icewind Dale', 'Bloodborne'];
        $prices = [199,299,399,499,599];
        for ($i = 0; $i < 5; $i++) {
            $product = new Product();
            $product->setTitle($titles[$i]);
            $product->setPrice($prices[$i]);
            $product->setCurrency('USD');
            $manager->persist($product);
        }

        $manager->flush();
    }
}
