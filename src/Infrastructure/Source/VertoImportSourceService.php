<?php
/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ImporterVerto\Infrastructure\Source;

use Ergonode\Importer\Infrastructure\Provider\ImportSourceInterface;

class VertoImportSourceService implements ImportSourceInterface
{
    public const TYPE = 'verto-csv';

    public static function getType(): string
    {
        return self::TYPE;
    }

    public function supported(string $type): bool
    {
        return self::TYPE === $type;
    }
}
