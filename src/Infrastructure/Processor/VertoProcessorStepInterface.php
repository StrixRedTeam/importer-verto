<?php
declare(strict_types=1);

namespace Ergonode\ImporterVerto\Infrastructure\Processor;

use Ergonode\ImporterVerto\Infrastructure\Model\ProductModel;
use Ergonode\Importer\Domain\Entity\Import;
use Ergonode\ImporterVerto\Domain\Entity\VertoCsvSource;

interface VertoProcessorStepInterface
{
    public function process(
        Import $import,
        ProductModel $product,
        VertoCsvSource $source
    ): void;
}
