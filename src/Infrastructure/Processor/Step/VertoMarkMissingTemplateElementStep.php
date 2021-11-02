<?php
declare(strict_types=1);

namespace Ergonode\ImporterVerto\Infrastructure\Processor\Step;

use Ergonode\ImporterVerto\Domain\Entity\VertoCsvSource;
use Ergonode\ImporterVerto\Infrastructure\Model\ProductModel;
use Ergonode\ImporterVerto\Infrastructure\Processor\VertoProcessorStepInterface;
use Ergonode\ImporterVerto\Infrastructure\Reader\VertoProductReader;
use Ergonode\Attribute\Domain\Query\AttributeQueryInterface;
use Ergonode\Attribute\Domain\Repository\AttributeRepositoryInterface;
use Ergonode\Attribute\Domain\ValueObject\AttributeCode;
use Ergonode\Core\Domain\ValueObject\Language;
use Ergonode\Designer\Domain\Entity\Element\AttributeTemplateElement;
use Ergonode\Designer\Domain\Query\TemplateQueryInterface;
use Ergonode\Designer\Domain\Repository\TemplateRepositoryInterface;
use Ergonode\Importer\Domain\Entity\Import;
use Ergonode\Importer\Domain\Repository\ImportRepositoryInterface;
use Ergonode\Importer\Infrastructure\Exception\ImportException;
use Ergonode\Product\Domain\Entity\SimpleProduct;

class VertoMarkMissingTemplateElementStep implements VertoProcessorStepInterface
{
    protected TemplateRepositoryInterface $templateRepository;

    protected TemplateQueryInterface $templateQuery;

    protected AttributeRepositoryInterface $attributeRepository;

    protected AttributeQueryInterface $attributeQuery;

    private ImportRepositoryInterface $importRepository;

    public function __construct(
        ImportRepositoryInterface $importRepository,
        TemplateRepositoryInterface $templateRepository,
        TemplateQueryInterface $templateQuery,
        AttributeRepositoryInterface $attributeRepository,
        AttributeQueryInterface $attributeQuery
    ) {
        $this->importRepository = $importRepository;
        $this->templateRepository = $templateRepository;
        $this->templateQuery = $templateQuery;
        $this->attributeRepository = $attributeRepository;
        $this->attributeQuery = $attributeQuery;
    }

    public function process(
        Import $import,
        ProductModel $product,
        VertoCsvSource $source
    ): void {
        if (!$source->import(VertoCsvSource::PRODUCTS) || !$product->getType() === SimpleProduct::TYPE) {
            return;
        }

        $templateAttribute = $product->getAttributes()[VertoProductReader::TEMPLATE_ATTRIBUTE] ?? null;
        if (!$templateAttribute
            || !$templateCode = $templateAttribute->get(
                new Language(VertoProductReader::DEFAULT_LANGUAGE)
            )) {
            throw new ImportException(
                sprintf(
                    'Missing %s attribute for product %s',
                    VertoProductReader::TEMPLATE_ATTRIBUTE,
                    $product->getSku()
                )
            );
        }

        $templateId = $this->templateQuery->findTemplateIdByCode($templateCode);
        if (!$templateId || !$template = $this->templateRepository->load($templateId)) {
            throw new ImportException(
                sprintf(
                    'Missing %s attribute for product %s',
                    VertoProductReader::TEMPLATE_ATTRIBUTE,
                    $product->getSku()
                )
            );
        }

        foreach ($product->getAttributes() as $attributeCode => $value) {
            $attributeId = $this->attributeQuery->findAttributeIdByCode(new AttributeCode($attributeCode));
            if (!$attributeId) {
                continue;
            }

            $exist = false;
            foreach ($template->getElements() as $element) {
                if ($element instanceof AttributeTemplateElement
                    && $element->getAttributeId()->getValue() === $attributeId->getValue()) {
                    $exist = true;
                }
            }

            if (!$exist) {
                $this->importRepository->addError(
                    $import->getId(),
                    'Missing attribute {{attribute}} on template {{template}}',
                    ['{{attribute}}' => $attributeCode, '{{template}}' => $templateCode]
                );
            }
        }
    }
}
