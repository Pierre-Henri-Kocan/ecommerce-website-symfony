<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHash, private SluggerInterface $slugger){}

    public function load(ObjectManager $manager): void
    {
        $admin = new User;
        $admin->setEmail('admin@demo.fr');
        $admin->setLastname(('Kocan'));
        $admin->setFirstname('Pierre-Henri');
        $admin->setAddress('19 allée du parc');
        $admin->setZipcode('95220');
        $admin->setCity('Herblay-sur-Seine');
        $admin->setPassword($this->passwordHash->hashPassword($admin, 'admin'));
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        $faker = Factory::create('fr_FR');
        for ($usr=1; $usr < 10; $usr++) { 
            $user = new User;
            $user->setEmail($faker->email);
            $user->setLastname($faker->lastName);
            $user->setFirstname($faker->firstName);
            $user->setAddress($faker->streetAddress);
            $user->setZipcode(str_replace(' ', '', $faker->postcode));
            $user->setCity($faker->city);
            $user->setPassword($this->passwordHash->hashPassword($user, 'user'));
            // dump($user);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
