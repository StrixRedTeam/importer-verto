<?php
/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ImporterVerto\Infrastructure\Handler;

use Ergonode\Importer\Domain\Repository\SourceRepositoryInterface;
use Ergonode\ImporterVerto\Domain\Command\UpdateVertoCsvSourceCommand;
use Ergonode\ImporterVerto\Domain\Entity\VertoCsvSource;

class UpdateVertoCsvSourceCommandHandler
{
    private SourceRepositoryInterface $repository;

    public function __construct(SourceRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(UpdateVertoCsvSourceCommand $command): void
    {
        /** @var VertoCsvSource $source */
        $source = $this->repository->load($command->getId());
        $source->setName($command->getName());
        $source->setImport($command->getImport());

        $this->repository->save($source);
    }
}
