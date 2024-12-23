<?php

declare(strict_types=1);

namespace App\Commands;

use App\Entities\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Creates a new user.',
    hidden: false,
    aliases: ['app:add-user']
)]
class CreateUserCommand extends Command
{
    public function __construct(private readonly EntityManager $entityManager, private readonly User $user)
    {
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->user
            ->setName('Test User')
            ->setEmail('test@example.com')
            ->setPassword(password_hash('test@1234', PASSWORD_BCRYPT));

        $this->entityManager->persist($this->user);
        $this->entityManager->flush();

        $output->writeln([
            'You have been successfully created a user.',
            '',
            'NOTE: "Please don\'t run this command again because',
            'you will get an error unless you delete a created',
            'user (name: Test User) in a MySQL Database."',
            '',
            'Hooray! Now you can repeat to run app:create-user',
            ' or app:add-user command after deleting that user.'
        ]);

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command allows you to create a user...')
        ;
    }
}