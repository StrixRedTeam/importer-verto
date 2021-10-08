<?php
declare(strict_types=1);

namespace Ergonode\ImporterVerto\Infrastructure\Handler;

use Ergonode\Importer\Domain\Repository\SourceRepositoryInterface;
use Ergonode\ImporterVerto\Domain\Command\CreateVertoCsvSourceCommand;
use Ergonode\ImporterVerto\Domain\Entity\VertoCsvSource;

class CreateVertoCsvSourceCommandHandler
{
    private SourceRepositoryInterface $repository;

    public function __construct(SourceRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(CreateVertoCsvSourceCommand $command): void
    {
        $source = new VertoCsvSource(
            $command->getId(),
            $command->getName(),
            $command->getImport()
        );

        $this->repository->save($source);
    }
}
