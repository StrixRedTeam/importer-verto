<?php
/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Account\Application\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TokenAvailable extends Constraint
{
    public string $validMessage = 'This token is not available';
}
