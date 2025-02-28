<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Nelmio\Alice\Loader\NativeLoader;

/**
 * Description of LoadFixtures.
 *
 * @author ibilbao
 */
class LoadFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $loader = new NativeLoader();
        $objectSet = $loader->loadFile(__DIR__ . '/fixtures.yml')->getObjects();
        foreach ($objectSet as $object) {
            $manager->persist($object);
        }
        $manager->flush();
    }

    public function izenak()
    {
        $izenak = [
            'Mikel',
            'Aitor',
            'Jon',
            'Unai',
            'Maider',
            'Saioa',
            'Nahia',
            'Nahiara',
            'Uxue',
            'Lander',
            'Erlantz',
        ];

        $key = array_rand($izenak);

        return $izenak[$key];
    }
}
