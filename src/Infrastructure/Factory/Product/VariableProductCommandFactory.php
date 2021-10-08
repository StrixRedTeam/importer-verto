<?php
/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ImporterVerto\Infrastructure\Factory\Product;

use Ergonode\ImporterVerto\Infrastructure\Model\ProductModel;
use Ergonode\ImporterVerto\Infrastructure\Model\VariableProductModel;
use Ergonode\Importer\Domain\Command\Import\ImportVariableProductCommand;
use Ergonode\Importer\Domain\Entity\Import;
use Ergonode\Importer\Infrastructure\Exception\ImportException;
use Ergonode\Product\Domain\Entity\VariableProduct;
use Ergonode\SharedKernel\Domain\Aggregate\ImportLineId;
use Ergonode\SharedKernel\Domain\DomainCommandInterface;

class VariableProductCommandFactory implements ProductCommandFactoryInterface
{
    public function supports(string $type): bool
    {
        return VariableProduct::TYPE === $type;
    }

    public function create(ImportLineId $id, Import $import, ProductModel $model): DomainCommandInterface
    {
        if (!$model instanceof VariableProductModel) {
            throw new ImportException('Invalid variable product for {sku} ', ['{sku}' => $model->getSku()]);
        }

        return new ImportVariableProductCommand(
            $id,
            $import->getId(),
            $model->getSku(),
            $model->getTemplate(),
            $model->getCategories(),
            [$model->getBindingAttribute()],
            $model->getChildren(),
            $model->getAttributes()
        );
    }
}
