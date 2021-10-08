<?php

declare(strict_types=1);

namespace Ergonode\ImporterVerto\Infrastructure\Processor;

use Ergonode\ImporterVerto\Domain\Entity\VertoCsvSource;
use Ergonode\ImporterVerto\Infrastructure\Model\ProductModel;
use Ergonode\ImporterVerto\Infrastructure\Model\VariableProductModel;
use Ergonode\ImporterVerto\Infrastructure\Reader\VertoProductReader;
use Ergonode\Core\Domain\ValueObject\Language;
use Ergonode\Core\Domain\ValueObject\TranslatableString;
use Ergonode\Designer\Domain\Repository\TemplateRepositoryInterface;
use Ergonode\Importer\Domain\Entity\Import;
use Ergonode\Importer\Domain\Repository\SourceRepositoryInterface;
use Ergonode\Importer\Infrastructure\Exception\ImportException;
use Ergonode\Importer\Infrastructure\Processor\SourceImportProcessorInterface;
use Ergonode\Product\Domain\Entity\VariableProduct;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Webmozart\Assert\Assert;

class VertoImportProcess implements SourceImportProcessorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected VertoProductReader $reader;

    protected TemplateRepositoryInterface $templateRepository;

    private SourceRepositoryInterface $repository;

    /**
     * @var VertoProcessorStepInterface[]
     */
    private array $steps;

    public function __construct(
        array $steps,
        SourceRepositoryInterface $repository,
        VertoProductReader $reader,
        TemplateRepositoryInterface $templateRepository
    ) {
        Assert::allIsInstanceOf($steps, VertoProcessorStepInterface::class);
        $this->steps = $steps;
        $this->repository = $repository;
        $this->reader = $reader;
        $this->templateRepository = $templateRepository;
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

            $childSku = $product->getSku();
            $variableProducts[$childSku] = $this->getVariableProduct($product, $variableProducts[$childSku] ?? null);
        }

        foreach ($variableProducts as $product) {
            foreach ($this->steps as $step) {
                $step->process($import, $product, $source);
            }
        }
    }

    private function getVariableProduct(
        ProductModel $childProduct,
        ?VariableProductModel $variableProductModel = null
    ): VariableProductModel {
        $childSku = $childProduct->getSku();
        if ($variableProductModel) {
            $variableProductModel->addChildren($childSku);

            return $variableProductModel;
        }

        $parentIdentifier = $childProduct->getAttributes()[VariableProductModel::IDENTIFYING_ATTRIBUTE] ?? null;
        if ($parentIdentifier instanceof TranslatableString) {
            $sku = $parentIdentifier->get(new Language(VertoProductReader::DEFAULT_LANGUAGE));

            $templateAttribute = $childProduct->getAttributes()[VertoProductReader::TEMPLATE_ATTRIBUTE];
            if (!$templateAttribute
                || !$templateCode = $templateAttribute->get(
                    new Language(VertoProductReader::DEFAULT_LANGUAGE)
                )) {
                throw new ImportException(
                    'Missing template attribute for product {productId}',
                    ['{productId}' => $childSku]
                );
            }

            return new VariableProductModel(
                $sku,
                VariableProduct::TYPE,
                $templateCode,
                VertoProductReader::BINDING_ATTRIBUTE,
                $childProduct->getAttributes(),
                $childSku
            );
        }

        throw new ImportException(
            'Missing template attribute for product {productId}',
            ['{productId}' => $childSku]
        );
    }
}
