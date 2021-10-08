<?php
declare(strict_types=1);

namespace Ergonode\ImporterVerto\Infrastructure\Processor\Step;

use Ergonode\ImporterVerto\Domain\Entity\VertoCsvSource;
use Ergonode\ImporterVerto\Infrastructure\Model\ProductModel;
use Ergonode\ImporterVerto\Infrastructure\Processor\VertoProcessorStepInterface;
use Ergonode\ImporterVerto\Infrastructure\Resolver\ProductCommandResolver;
use Ergonode\Importer\Domain\Entity\Import;
use Ergonode\Importer\Domain\Repository\ImportRepositoryInterface;
use Ergonode\Product\Domain\Entity\SimpleProduct;
use Ergonode\Product\Domain\Entity\VariableProduct;
use Ergonode\SharedKernel\Domain\Aggregate\ImportLineId;
use Ergonode\SharedKernel\Domain\Bus\CommandBusInterface;

class VertoVariableProductProcessorStep implements VertoProcessorStepInterface
{
    private CommandBusInterface $commandBus;
    private ProductCommandResolver $commandResolver;
    private ImportRepositoryInterface $importRepository;

    public function __construct(
        CommandBusInterface $commandBus,
        ProductCommandResolver $commandResolver,
        ImportRepositoryInterface $importRepository
    ) {
        $this->commandBus = $commandBus;
        $this->commandResolver = $commandResolver;
        $this->importRepository = $importRepository;
    }

    public function process(
        Import $import,
        ProductModel $product,
        VertoCsvSource $source
    ): void {
        if (!$source->import(VertoCsvSource::PRODUCTS) || !$product->getType() === VariableProduct::TYPE) {
            return;
        }

        $id = ImportLineId::generate();
        $command = $this->commandResolver->resolve($id, $import, $product);
        $this->importRepository->addLine($id, $import->getId(), 'PRODUCT');
        $this->commandBus->dispatch($command, true);
    }
}
