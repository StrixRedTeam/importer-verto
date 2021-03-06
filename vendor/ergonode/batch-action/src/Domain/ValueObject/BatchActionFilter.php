<?php
/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\BatchAction\Domain\ValueObject;

class BatchActionFilter implements BatchActionFilterInterface
{
    private ?BatchActionIds $ids;

    private ?string $query;

    public function __construct(?BatchActionIds $ids = null, ?string $query = null)
    {
        if (null === $ids && null === $query) {
            throw new \InvalidArgumentException('At least query or ids should be passed.');
        }
        $this->ids = $ids;
        $this->query = $query;
    }

    public function getIds(): ?BatchActionIds
    {
        return $this->ids;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }
}
