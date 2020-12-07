<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker;

class ActorFixtures extends Fixture implements DependentFixtureInterface
{
    const ACTORS =[
        'tom jedusor',
        'jack bogdanoff',
        'thierry jacko',
        'atchoum plouff',
    ];
    public function load(ObjectManager $manager)
    {
        foreach(self::ACTORS as $key => $name) {
            $actor = new Actor();
            $actor->setName($name);
            $actor->addProgram($this->getReference('program_' . rand(1, 5), $actor));
            $this->setReference('actor' . $key, $actor);
            $manager->persist($actor);
        }
        $faker = Faker\Factory::create('en_US');
        for($i=4;$i<=50;$i++) {
            $actor = new Actor();
            $actor->setName($faker->name());
            $actor->addProgram($this->getReference('program_' . rand(1, 5), $actor));
            $manager->persist($actor);
        }
        $manager->flush();
    }


    public function getDependencies()  
    {
        return [ProgramFixtures::class];  
    }
}