<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Attribute\Infrastructure\Provider;

use Ergonode\Attribute\Domain\Entity\AbstractAttribute;
use Ergonode\SharedKernel\Domain\AggregateId;
use Symfony\Component\Validator\Constraint;

interface ContextAwareAttributeValueConstraintStrategyInterface extends AttributeValueConstraintStrategyInterface
{
    public function get(AbstractAttribute $attribute, ?AggregateId $aggregateId = null): Constraint;

    public function supports(AbstractAttribute $attribute): bool;
}
