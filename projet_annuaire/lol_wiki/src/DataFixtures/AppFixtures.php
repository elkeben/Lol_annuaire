<?php

namespace App\DataFixtures;

namespace App\DataFixtures;


use App\Entity\Champion;
use App\Entity\Competence;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class AppFixtures extends Fixture
{
    private $champions=[];


    public function load(ObjectManager $manager)
    {
        $this->loadChampion($manager);
        $this->loadSkill($manager);
    }


    public function loadChampion(ObjectManager $manager)
    {
        $data = Yaml::parseFile(__DIR__ . '/champions.yml');


        foreach ($data as $championData) {
            $champion = new Champion();
            $champion->setNom($championData['nom'])
                ->setType($championData['type'])
                ->setRole($championData['role'])
                ->setPresentation($championData['presentation'])
                ->setHistoire($championData['histoire'])
                ->setImage($championData['image'])
                ->setDate( DateTime::createFromFormat('Y-m-d', $championData['date']));

            $this->champions[$championData['nom']]= $champion;
            $manager->persist($champion);
        }
        $manager->flush();
    }


    public function loadSkill(ObjectManager $manager)
    {
        $data = Yaml::parseFile(__DIR__ . '/competence.yml');


        foreach ($data as $skillData) {
            $skill = new Competence();
            $skill->setNom($skillData['nom'])
                ->setImage($skillData['image'])
                ->setDescription($skillData['description'])
                ->setChampion($this->champions[$skillData['champion']]);

            $manager->persist($skill);
        }
        $manager->flush();
    }

}
