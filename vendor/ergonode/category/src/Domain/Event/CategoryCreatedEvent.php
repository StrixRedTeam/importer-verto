<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Category\Domain\Event;

use Ergonode\SharedKernel\Domain\Aggregate\CategoryId;
use Ergonode\Category\Domain\ValueObject\CategoryCode;
use Ergonode\Core\Domain\ValueObject\TranslatableString;
use Ergonode\SharedKernel\Domain\AggregateEventInterface;
use Ergonode\Value\Domain\ValueObject\ValueInterface;

class CategoryCreatedEvent implements AggregateEventInterface
{
    private CategoryId $id;

    private CategoryCode $code;

    private string $type;

    private TranslatableString $name;

    /**
     * @var ValueInterface[]
     */
    private array $attributes;

    /**
     * @param ValueInterface[] $attributes
     */
    public function __construct(
        CategoryId $id,
        CategoryCode $code,
        string $type,
        TranslatableString $name,
        array $attributes = []
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->type = $type;
        $this->name = $name;
        $this->attributes = $attributes;
    }

    public function getAggregateId(): CategoryId
    {
        return $this->id;
    }

    public function getCode(): CategoryCode
    {
        return $this->code;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): TranslatableString
    {
        return $this->name;
    }

    /**
     * @return ValueInterface[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
