<?php

declare(strict_types=1);

namespace MellowApiBundle\Command\Freelancer;

use Mellow\Api\Freelancer\Response\FreelancerResponse;
use MellowApiBundle\ClientFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @internal
 */
#[AsCommand(
    name: 'mellow:freelancer:retrieve',
    description: 'Retrieve a freelancer',
)]
class RetrieveFreelancerCommand extends Command
{
    public function __construct(
        private readonly ClientFactory $clientFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'id',
                InputArgument::REQUIRED,
                'Freelancer ID',
            )
            ->addArgument(
                'companyId',
                InputArgument::OPTIONAL,
                'Company ID to filter candidate by',
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Retrieve Freelancer');

        $companyId = $input->getArgument('companyId');
        if (null !== $companyId) {
            $companyId = (int) $companyId;
        }
        $api = $this->clientFactory->create($companyId);

        /** @var int|string $freelancerId */
        $freelancerId = $input->getArgument('id');

        /** @var FreelancerResponse $freelancer */
        $freelancer = $api->freelancer()->retrieve($freelancerId);

        $rows = [
            ['ID', (string) $freelancer->id],
            ['UUID', $freelancer->uuid],
            ['Name', $this->formatNullableString($freelancer->name)],
            ['Email', $this->formatNullableString($freelancer->email)],
            ['Phone', $this->formatNullableString($freelancer->phone)],
            ['Country', $this->formatNullableString($freelancer->country)],
            ['Verified', $this->formatBool($freelancer->isVerified)],
            ['Registered', $this->formatBool($freelancer->isRegistered)],
            ['Invite Sent', $this->formatBool($freelancer->isInviteSent)],
            ['Tax Payment Allowed', $this->formatBool($freelancer->isTaxPaymentAllowed)],
            ['Taxation Status ID', (string) $freelancer->taxationStatusId],
            ['Email Confirmation Status', (string) $freelancer->emailConfirmationStatus],
            ['Phone Confirmation Status', (string) $freelancer->phoneConfirmationStatus],
            ['Invite Sent At', $this->formatDate($freelancer->inviteSentAt)],
            ['Actual Registration Date', $this->formatDate($freelancer->actualRegDate)],
            ['Date Verified', $this->formatDate($freelancer->dateVerified)],
            ['Taxation Blocked Till', $this->formatDate($freelancer->taxationBlockedTill)],
            ['Category Title', $this->formatNullableString($freelancer->categoryTitle)],
            ['Category Title (EN)', $this->formatNullableString($freelancer->categoryTitleEn)],
        ];

        if ([] !== $freelancer->details) {
            foreach ($freelancer->details as $key => $value) {
                $rows[] = [sprintf('Details: %s', $key), $this->formatNullableString($value)];
            }
        }

        $this->renderKeyValueTable($output, $rows);

        return Command::SUCCESS;
    }

    /**
     * @param list<array{0:string,1:string}> $rows
     */
    private function renderKeyValueTable(OutputInterface $output, array $rows): void
    {
        $table = new Table($output);
        $table->setHeaders(['Field', 'Value']);
        $table->setRows($rows);
        $table->render();
    }

    private function formatNullableString(?string $value): string
    {
        if (null === $value || '' === trim($value)) {
            return '<comment>n/a</comment>';
        }

        return $value;
    }

    private function formatBool(bool $value): string
    {
        return $value ? '<info>yes</info>' : '<comment>no</comment>';
    }

    private function formatDate(?\DateTimeInterface $value): string
    {
        if (null === $value) {
            return '<comment>n/a</comment>';
        }

        return sprintf(
            '%s (%s)',
            $value->format('Y-m-d H:i:s'),
            $value->getTimezone()->getName(),
        );
    }
}
