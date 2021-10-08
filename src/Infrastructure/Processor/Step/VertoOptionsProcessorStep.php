<?php
/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ImporterVerto\Infrastructure\Processor\Step;

use Ergonode\ImporterVerto\Domain\Entity\VertoCsvSource;
use Ergonode\ImporterVerto\Infrastructure\Model\ProductModel;
use Ergonode\ImporterVerto\Infrastructure\Processor\VertoProcessorStepInterface;
use Ergonode\ImporterVerto\Infrastructure\Reader\VertoProductReader;
use Ergonode\Attribute\Domain\Entity\Attribute\MultiSelectAttribute;
use Ergonode\Attribute\Domain\Entity\Attribute\SelectAttribute;
use Ergonode\Attribute\Domain\Query\AttributeQueryInterface;
use Ergonode\Attribute\Domain\ValueObject\AttributeCode;
use Ergonode\Core\Domain\ValueObject\Language;
use Ergonode\Core\Domain\ValueObject\TranslatableString;
use Ergonode\Importer\Domain\Command\Import\ImportOptionCommand;
use Ergonode\Importer\Domain\Entity\Import;
use Ergonode\Importer\Domain\Repository\ImportRepositoryInterface;
use Ergonode\Product\Domain\Entity\SimpleProduct;
use Ergonode\SharedKernel\Domain\Aggregate\ImportLineId;
use Ergonode\SharedKernel\Domain\Bus\CommandBusInterface;

class VertoOptionsProcessorStep implements VertoProcessorStepInterface
{
    protected AttributeQueryInterface $attributeQuery;

    private CommandBusInterface $commandBus;

    private ImportRepositoryInterface $importRepository;

    public function __construct(
        CommandBusInterface $commandBus,
        ImportRepositoryInterface $importRepository,
        AttributeQueryInterface $attributeQuery
    ) {
        $this->commandBus = $commandBus;
        $this->importRepository = $importRepository;
        $this->attributeQuery = $attributeQuery;
    }

    public function process(Import $import, ProductModel $product, VertoCsvSource $source): void
    {
        if (!$source->import(VertoCsvSource::PRODUCTS) || !$product->getType() === SimpleProduct::TYPE) {
            return;
        }

        /**
         * @var string $attributeCode
         * @var TranslatableString $value
         */
        foreach ($product->getAttributes() as $attributeCode => $value) {
            $attribute = $this->attributeQuery->findAttributeByCode(new AttributeCode($attributeCode));
            if (!$attribute
                || !in_array(
                    $attribute->getType(),
                    [SelectAttribute::TYPE, MultiSelectAttribute::TYPE],
                    true
                )) {
                continue;
            }

            $optionValue = $value->get(new Language(VertoProductReader::DEFAULT_LANGUAGE));
            if (!$optionValue) {
                continue;
            }

            $id = ImportLineId::generate();
            $command = new ImportOptionCommand(
                $id,
                $import->getId(),
                $attributeCode,
                $optionValue,
                new TranslatableString([VertoProductReader::DEFAULT_LANGUAGE => $optionValue])
            );
            $this->importRepository->addLine($id, $import->getId(), 'OPTION');
            $this->commandBus->dispatch($command, true);
        }
    }
}
