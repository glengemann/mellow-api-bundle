<?php

declare(strict_types=1);

namespace MellowApiBundle\Command\Login;

use Mellow\Api\Login\Response\TwoFactorEnabledResponse;
use Mellow\LoginInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'mellow:api:login',
    description: 'Login to Mellow API',
)]
class LoginCommand extends Command
{
    public function __construct(
        private readonly LoginInterface $loginService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'two-factor-code',
            null,
            InputOption::VALUE_OPTIONAL,
            'Optional 2FA code (6-digit token).',
        );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $twoFactorCode = $input->getOption('two-factor-code');

        if (null !== $twoFactorCode && !is_numeric($twoFactorCode)) {
            $output->writeln('<error>The two-factor-code argument must be numeric.</error>');

            return Command::INVALID;
        }

        $response = $this->loginService->login(
            null === $twoFactorCode ? null : (int) $twoFactorCode,
        );

        if ($response->requiresTwoFactor()) {
            $output->writeln('<comment>Two-factor authentication is required.</comment>');

            if ($response instanceof TwoFactorEnabledResponse) {
                $maskedNumber = $response->number ?? 'unknown';
                $output->writeln(sprintf('Method: %s', $response->type));
                $output->writeln(sprintf('Number: %s', $maskedNumber));
            }

            return Command::SUCCESS;
        }

        $output->writeln('<info>Login successful.</info>');

        return Command::SUCCESS;
    }
}
