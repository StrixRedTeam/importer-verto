<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Workflow\Domain\Command\Status;

use Ergonode\Core\Domain\ValueObject\Color;
use Ergonode\Core\Domain\ValueObject\TranslatableString;
use Ergonode\SharedKernel\Domain\Aggregate\StatusId;
use Ergonode\Workflow\Domain\Command\WorkflowCommandInterface;

class UpdateStatusCommand implements WorkflowCommandInterface
{
    private StatusId $id;

    private Color $color;

    private TranslatableString $name;

    private TranslatableString $description;

    public function __construct(StatusId $id, Color $color, TranslatableString $name, TranslatableString $description)
    {
        $this->id = $id;
        $this->color = $color;
        $this->name = $name;
        $this->description = $description;
    }

    public function getId(): StatusId
    {
        return $this->id;
    }

    public function getColor(): Color
    {
        return $this->color;
    }

    public function getName(): TranslatableString
    {
        return $this->name;
    }

    public function getDescription(): TranslatableString
    {
        return $this->description;
    }
}
