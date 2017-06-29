<?php

namespace Spoof\Commands;

use GuzzleHttp\Client;
use Spoof\Template\Parser;
use Spoof\Tools\Signer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class PrMergeCommand extends Command
{
    /**
     * Configure this command.
     */
    protected function configure()
    {
        $this->setName('PrMerge')
            ->setDescription('Fake a pull request merge event from $from into $target.')
            ->addArgument(
                'repository',
                InputArgument::REQUIRED,
                'Full repository name, including the owner. Example: styxit/deployments'
            )
            ->addArgument(
                'from',
                InputArgument::REQUIRED,
                'The branch name that is merged into the target'
            )
            ->addArgument(
                'target',
                InputArgument::REQUIRED,
                'The branch name that is the target of the pr merge event'
            );
    }

    /**
     * Execute the command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int The exit code.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeLn('');

        list($repositoryOwner, $repositoryName) = explode('/', $input->getArgument('repository'));

        $output->writeLn('<info>About to spoof a pull request merge event with the following settings:</info>');
        $output->writeLn('');
        $output->writeLn(sprintf('Repository: <comment>%s/%s</comment>', $repositoryOwner, $repositoryName));
        $output->writeln(
            sprintf(
                'Merge <comment>%s</comment> into <comment>%s</comment>.',
                $input->getArgument('from'),
                $input->getArgument('target')
            )
        );
        $output->writeLn('');

        // Ask confirmation.
        if (!$this->confirm($input, $output, 'Is this correct?')) {
            $output->writeLn('User did not confirm. Quit.');

            return;
        }

        // Construct payload from template.
        $payload = (new Parser('pr-merge'))->parse(
            [
                'repoName' => $repositoryName,
                'repoOwner' => $repositoryOwner,
                'repoFullName' => $repositoryOwner.'/'.$repositoryName,
                'from' => $input->getArgument('from'),
                'target' => $input->getArgument('target'),
            ]
        );

        // Get the payload signature.
        $signature = (new Signer())->sign($payload);

        $output->writeLn('Spoofing the event...');

        // Construct and execute the request.
        $client = new Client();
        $client->post(
            $_ENV['DESTINATION_URL'],
            [
                'body' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Hub-Signature' => 'sha1='.$signature,
                    'X-GitHub-Event' => 'merge_pull_request',
                ]
            ]
        );

        $output->writeLn('Done.');
    }

    /**
     * Ask a question the user must answer with 'y' or 'n'.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $question The question to ask.
     *
     * @return bool True when the user entered 'y', False otherwise.
     */
    private function confirm(InputInterface $input, OutputInterface $output, $question = 'Ok?')
    {
        $helper = $this->getHelper('question');
        $confirmQuestion = new ConfirmationQuestion($question.' [y/n]: ', false);

        if (!$helper->ask($input, $output, $confirmQuestion)) {
            return false;
        }

        return true;
    }
}
