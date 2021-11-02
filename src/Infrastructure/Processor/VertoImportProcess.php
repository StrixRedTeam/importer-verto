<?php

declare(strict_types=1);

namespace Ergonode\ImporterVerto\Infrastructure\Processor;

use Ergonode\Core\Domain\ValueObject\Language;
use Ergonode\Core\Domain\ValueObject\TranslatableString;
use Ergonode\Importer\Domain\Entity\Import;
use Ergonode\Importer\Domain\Repository\SourceRepositoryInterface;
use Ergonode\Importer\Infrastructure\Exception\ImportException;
use Ergonode\Importer\Infrastructure\Processor\SourceImportProcessorInterface;
use Ergonode\ImporterVerto\Domain\Entity\VertoCsvSource;
use Ergonode\ImporterVerto\Infrastructure\Model\ProductModel;
use Ergonode\ImporterVerto\Infrastructure\Model\VariableProductModel;
use Ergonode\ImporterVerto\Infrastructure\Reader\VertoProductReader;
use Ergonode\Product\Domain\Entity\VariableProduct;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Webmozart\Assert\Assert;

class VertoImportProcess implements SourceImportProcessorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected VertoProductReader $reader;

    private SourceRepositoryInterface $repository;

    /**
     * @var VertoProcessorStepInterface[]
     */
    private array $steps;

    public function __construct(
        array $steps,
        SourceRepositoryInterface $repository,
        VertoProductReader $reader
    ) {
        Assert::allIsInstanceOf($steps, VertoProcessorStepInterface::class);
        $this->steps = $steps;
        $this->repository = $repository;
        $this->reader = $reader;
    }

    public function supported(string $type): bool
    {
        return $type === VertoCsvSource::TYPE;
    }

    public function start(Import $import): void
    {
        /** @var VertoCsvSource $source */
        $source = $this->repository->load($import->getSourceId());
        Assert::notNull($source);

        $this->reader->open($import->getFile());
        $variableProducts = [];
        while ($product = $this->reader->read()) {
            foreach ($this->steps as $step) {
                $step->process($import, $product, $source);
            }

            $parentSku = $this->getParentSku($product);
            $variableProducts[$parentSku] = $this->getVariableProduct($product, $variableProducts[$parentSku] ?? null);
        }

        foreach ($variableProducts as $product) {
            foreach ($this->steps as $step) {
                $step->process($import, $product, $source);
            }
        }
    }

    /**
     * @param ProductModel $childProduct
     * @param VariableProductModel|null $variableProductModel
     * @return VariableProductModel
     * @throws ImportException
     */
    private function getVariableProduct(
        ProductModel $childProduct,
        ?VariableProductModel $variableProductModel = null
    ): VariableProductModel {
        $childSku = $childProduct->getSku();
        if ($variableProductModel) {
            $variableProductModel->addChildren($childSku);

            return $variableProductModel;
        }

        $templateAttribute = $childProduct->getAttributes()[VertoProductReader::TEMPLATE_ATTRIBUTE];
        if (!$templateAttribute
            || !$templateCode = $templateAttribute->get(
                new Language(VertoProductReader::DEFAULT_LANGUAGE)
            )) {
            throw new ImportException(
                sprintf(
                    'Missing %s attribute for product %s',
                    VertoProductReader::TEMPLATE_ATTRIBUTE,
                    $childSku
                )
            );
        }

        $parentSku = $this->getParentSku($childProduct);

        return new VariableProductModel(
            $parentSku,
            VariableProduct::TYPE,
            $templateCode,
            VertoProductReader::BINDING_ATTRIBUTE,
            $childProduct->getAttributes(),
            $childSku
        );
    }

    /**
     * @param ProductModel $product
     * @return string
     * @throws ImportException
     */
    private function getParentSku(ProductModel $product): string
    {
        $parentIdentifier = $product->getAttributes()[VariableProductModel::IDENTIFYING_ATTRIBUTE] ?? null;
        if ($parentIdentifier instanceof TranslatableString) {
            $sku = $parentIdentifier->get(new Language(VertoProductReader::DEFAULT_LANGUAGE));
            if ($sku) {
                return $sku;
            }
        }

        throw new ImportException(
            'Missing attribute {attribute} for product {productId}',
            ['{attribute}' => VariableProductModel::IDENTIFYING_ATTRIBUTE, '{productId}' => $product->getSku()]
        );
    }
}
