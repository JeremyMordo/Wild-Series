<?php

namespace App\DataFixtures;

use App\Entity\Episode;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('en_US');
        for($i=1;$i<=50;$i++) {
            $episode = new Episode();
            $episode->setSeason($this->getReference("season_" . rand(1, 50)));
            $episode->setTitle($faker->sentence());
            $episode->setNumber($faker->numberBetween($min=1, $max=12));
            $episode->setSummary($faker->text());
            $manager->persist($episode);
        }
        $manager->flush();
    }


    public function getDependencies()  
    {
        return [SeasonFixtures::class];  
    }
}