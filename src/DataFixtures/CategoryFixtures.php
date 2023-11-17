<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoryFixtures extends Fixture
{
    private $counter = 1;

    public function __construct(private SluggerInterface $slugger) {}

    public function load(ObjectManager $manager): void
    {
        //* Ecriture avancée avec création d'une fonction "createCategory" pour simplifier le code
        $parent = $this->createCategory('Informatique', null, $manager);

        $this->createCategory('Ordinateurs portables', $parent, $manager);
        $this->createCategory('Ecrans', $parent, $manager);
        $this->createCategory('Souris', $parent, $manager);
        $this->createCategory('Claviers', $parent, $manager);

        $parent = $this->createCategory('Mode', null, $manager);

        $this->createCategory('Homme', $parent, $manager);
        $this->createCategory('Femme', $parent, $manager);
        $this->createCategory('Enfant', $parent, $manager);

        $manager->flush();

        //* Ecriture classique des fixtures - beaucoup de doublons de lignes de code à écrire
        // $parent = new Category();
        // $parent->setName('Informatique');
        // $parent->setSlug($this->slugger->slug($parent->getName())->lower());
        // $manager->persist($parent);

        // $category = new Category();
        // $category->setName('Ordinateurs portables');
        // $category->setSlug($this->slugger->slug($category->getName())->lower());
        // $category->setParent($parent);
        // $manager->persist($category);

        // $category = new Category();
        // $category->setName('Ecrans');
        // $category->setSlug($this->slugger->slug($category->getName())->lower());
        // $category->setParent($parent);
        // $manager->persist($category);

        // $manager->flush();
    }

    public function createCategory(string $name, Category $parent = null, ObjectManager $manager)
    {
        $category = new Category();
        $category->setName($name);
        $category->setSlug($this->slugger->slug($category->getName())->lower());
        $category->setParent($parent);
        $manager->persist($category);

        $this->addReference('cat-' .$this->counter, $category);
        $this->counter++;

        return $category;
    }

}
