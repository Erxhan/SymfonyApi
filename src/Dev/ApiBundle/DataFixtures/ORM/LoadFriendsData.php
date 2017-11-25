<?php

namespace DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Dev\ApiBundle\Entity\User;


class LoadFriendsData implements FixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail('plop@plop.com')
            ->setPlainPassword('plop')
            ->setFirstname('plop')
            ->setLastname('plop')
            ->setGender('male')
            ->setLongitude('4,473538100000042')
            ->setLatitude('50,4760988')
            ->setBirthday(new \DateTime('1998-03-04'));

        $friend = new User();
        $friend->setEmail('ami@plop.com')
            ->setPlainPassword('amiplop')
            ->setFirstname('amiplop')
            ->setLastname('amiplop')
            ->setGender('male')
            ->setLongitude('6,473538100000042')
            ->setLatitude('59,4760988')
            ->setBirthday(new \DateTime('1995-03-04'));

        $user->addFriend($friend);
        $friend->addFriend($user);

        $manager->persist($user);
        $manager->persist($friend);
        $manager->flush();
    }
}