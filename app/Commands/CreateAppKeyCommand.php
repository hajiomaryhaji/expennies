<?php

declare(strict_types=1);

namespace App\Commands;

use App\ConfigParser;
use App\Entities\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

#[AsCommand(
    name: 'app:create-app-key',
    description: 'Creates a new app key.',
    hidden: false,
    aliases: ['app:add-app-key']
)]
class CreateAppKeyCommand extends Command
{
    public function __construct(private readonly ConfigParser $configParser)
    {
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $hasKey = $this->configParser->get('app_key');

        if ($hasKey) {
            $helper = $this->getHelper('question');

            $question = new ConfirmationQuestion(
                'Generating a new APP_KEY will invalidate any signatures associated with the old key. Are you sure you want to proceed? (y/n)',
                false
            );

            if (!$helper->ask($input, $output, $question)) {
                return Command::SUCCESS;
            }
        }

        $key = base64_encode(random_bytes(32));

        $envFilePath = __DIR__ . '/../../.env';

        if (!file_exists($envFilePath)) {
            throw new \RuntimeException('.env file not found');
        }

        $envFileContent = file_get_contents($envFilePath);

        $pattern = '/^APP_KEY=.*/m';

        if (preg_match($pattern, $envFileContent)) {
            $envFileContent = preg_replace($pattern, 'APP_KEY=' . $key, $envFileContent);
        } else {
            $envFileContent .= PHP_EOL . 'APP_KEY=' . $key;
        }

        file_put_contents($envFilePath, $envFileContent);

        $output->writeln([
            'You have been successfully created an APP_KEY.',
            ''
        ]);

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command allows you to create a unique key only known by your application and not shared.')
        ;
    }
}