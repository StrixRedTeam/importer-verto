<?php
/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Workflow\Tests\Infrastructure\Handler\Workflow;

use Ergonode\Core\Infrastructure\Resolver\RelationshipsResolverInterface;
use Ergonode\SharedKernel\Domain\Bus\CommandBusInterface;
use Ergonode\Workflow\Infrastructure\Handler\Workflow\UpdateWorkflowCommandHandler;
use PHPUnit\Framework\TestCase;
use Ergonode\Workflow\Domain\Command\Workflow\UpdateWorkflowCommand;
use Ergonode\Workflow\Domain\Repository\WorkflowRepositoryInterface;
use Ergonode\Workflow\Domain\Entity\Workflow;

class UpdateWorkflowCommandHandlerTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testCommandHandling(): void
    {
        $command = $this->createMock(UpdateWorkflowCommand::class);
        $workflow = $this->createMock(Workflow::class);
        $relationshipsResolver = $this->createMock(RelationshipsResolverInterface::class);
        $commandBus = $this->createMock(CommandBusInterface::class);

        $repository = $this->createMock(WorkflowRepositoryInterface::class);
        $repository->expects(self::once())->method('load')->willReturn($workflow);
        $repository->expects(self::once())->method('save');

        $handler = new UpdateWorkflowCommandHandler($repository, $relationshipsResolver, $commandBus);
        $handler->__invoke($command);
    }
}
