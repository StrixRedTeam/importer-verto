<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Account\Application\Validator;

use Ergonode\Account\Domain\Query\UserQueryInterface;
use Ergonode\SharedKernel\Domain\ValueObject\Email;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UserUniqueValidator extends ConstraintValidator
{
    private UserQueryInterface $query;

    public function __construct(UserQueryInterface $query)
    {
        $this->query = $query;
    }

    /**
     * @param mixed                 $value
     * @param UserUnique|Constraint $constraint
     *
     * @throws \Exception
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UserUnique) {
            throw new UnexpectedTypeException($constraint, UserUnique::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $value = (string) $value;

        $userId = null;
        if (Email::isValid($value)) {
            $userId = $this->query->findIdByEmail(new Email($value));
        }

        if ($userId) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
