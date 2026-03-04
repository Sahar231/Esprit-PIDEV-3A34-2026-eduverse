<?php

namespace App\Command;

use App\Entity\Club;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:seed:test-data',
    description: 'Seed database with test users and clubs',
)]
class SeedTestDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            // Create test students
            $students = [];
            for ($i = 1; $i <= 3; $i++) {
                $user = new User();
                $user->setEmail("student{$i}@test.com");
                $user->setFullName("Student {$i}");
                $user->setRole('ROLE_STUDENT');
                $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
                $user->setPassword($hashedPassword);
                $user->setIsApproved(true);
                $user->setIsVerified(true);
                $user->setCreatedAt(new \DateTimeImmutable());
                
                $this->em->persist($user);
                $students[] = $user;
            }
            
            // Create instructor/creator
            $creator = new User();
            $creator->setEmail('instructor@test.com');
            $creator->setFullName('Test Instructor');
            $creator->setRole('ROLE_INSTRUCTOR');
            $hashedPassword = $this->passwordHasher->hashPassword($creator, 'password123');
            $creator->setPassword($hashedPassword);
            $creator->setIsApproved(true);
            $creator->setIsVerified(true);
            $creator->setCreatedAt(new \DateTimeImmutable());
            
            $this->em->persist($creator);
            $this->em->flush();
            
            // Create test clubs
            $clubData = [
                ['name' => 'Chess Club', 'desc' => 'Learn and play chess with fellow enthusiasts. All skill levels welcome!'],
                ['name' => 'Art & Design', 'desc' => 'Explore digital and traditional art forms. Weekly workshops and exhibitions.'],
                ['name' => 'Coding Club', 'desc' => 'Learn programming, work on projects, and build cool applications together.'],
                ['name' => 'Photography Club', 'desc' => 'Share photography skills and go on photo walks together.'],
                ['name' => 'Music Club', 'desc' => 'For musicians of all levels. Jam sessions, workshops, and performances.'],
            ];
            
            foreach ($clubData as $data) {
                $club = new Club();
                $club->setName($data['name']);
                $club->setDescription($data['desc']);
                $club->setStatus(Club::STATUS_APPROVED);
                $club->setCreator($creator);
                $club->setCreatedAt(new \DateTimeImmutable());
                
                // Add first student as member to some clubs
                if (!empty($students)) {
                    $club->addMember($students[0]);
                }
                
                $this->em->persist($club);
            }
            
            $this->em->flush();
            
            $io->success('Test data seeded successfully!');
            $io->info([
                'Created:',
                '  ✓ 3 student users (student1@test.com - student3@test.com)',
                '  ✓ 1 instructor user (instructor@test.com)',
                '  ✓ 5 test clubs (all approved)',
                '',
                'Test Credentials:',
                '  Email: student1@test.com / Password: password123',
                '  Email: instructor@test.com / Password: password123',
                '',
                'Try accessing: http://localhost:8000/student/clubs/1',
            ]);
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error seeding data: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
