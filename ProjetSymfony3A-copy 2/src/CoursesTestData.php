<?php

namespace App;

use App\Entity\Cours;
use App\Entity\Chapitre;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Test script to verify Courses and Chapters system
 * Run with: php bin/console app:test:courses
 */
class CoursesTestData
{
    public static function createTestData(ManagerRegistry $doctrine): void
    {
        $em = $doctrine->getManager();
        $userRepo = $doctrine->getRepository(User::class);
        
        // Find a user to be the creator
        $creator = $userRepo->findOneBy(['roles' => '["ROLE_INSTRUCTOR"]']) 
            ?? $userRepo->findOneBy(['email' => 'instructor@example.com'])
            ?? $userRepo->findOneBy([]);
        
        if (!$creator) {
            echo "No user found. Please create a user first.\n";
            return;
        }
        
        // Create test course 1
        $cours1 = new Cours();
        $cours1->setTitle('PHP Fundamentals')
            ->setDescription('Learn the basics of PHP programming language including syntax, variables, functions, and control structures.')
            ->setCategory('Programming')
            ->setCreator($creator)
            ->setStatus(Cours::STATUS_PENDING);
        
        // Add chapters to course 1
        $chapter1 = new Chapitre();
        $chapter1->setTitle('Introduction to PHP')
            ->setContent('PHP is a popular general-purpose scripting language that is especially suited to web development. It was created in 1995 by Rasmus Lerdorf. PHP code may be embedded into HTML code, or it can be used in combination with various template systems, web content management systems and web frameworks.')
            ->setCreator($creator)
            ->setCours($cours1);
        
        $chapter2 = new Chapitre();
        $chapter2->setTitle('Variables and Data Types')
            ->setContent('Variables are containers for storing data values. PHP is a loosely typed language, meaning you don\'t need to declare the type of a variable; PHP automatically associates a type with the variable based on its value.')
            ->setCreator($creator)
            ->setCours($cours1);
        
        $cours1->addChapitre($chapter1);
        $cours1->addChapitre($chapter2);
        
        // Create test course 2 (admin-approved)
        $cours2 = new Cours();
        $cours2->setTitle('Web Development with Symfony')
            ->setDescription('Master the Symfony framework for building robust and scalable web applications following PHP best practices and design patterns.')
            ->setCategory('Web Development')
            ->setCreator($creator)
            ->setStatus(Cours::STATUS_APPROVED);
        
        $chapter3 = new Chapitre();
        $chapter3->setTitle('Getting Started with Symfony')
            ->setContent('Symfony is a robust PHP framework for building web applications. It follows the Model-View-Controller (MVC) architectural pattern and emphasizes code reusability and modularity.')
            ->setCreator($creator)
            ->setCours($cours2);
        
        $cours2->addChapitre($chapter3);
        
        // Persist entities
        $em->persist($cours1);
        $em->persist($cours2);
        
        $em->flush();
        
        echo "✓ Test courses created successfully!\n";
        echo "  - Course 1 (Pending): PHP Fundamentals\n";
        echo "  - Course 2 (Approved): Web Development with Symfony\n\n";
    }
}
