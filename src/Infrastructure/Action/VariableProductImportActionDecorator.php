<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ImporterVerto\Infrastructure\Action;

use Ergonode\Attribute\Domain\ValueObject\AttributeCode;
use Ergonode\Importer\Infrastructure\Action\VariableProductImportAction;
use Ergonode\Importer\Infrastructure\Exception\ImportException;
use Ergonode\Product\Domain\Entity\AbstractProduct;
use Ergonode\Product\Domain\Entity\VariableProduct;
use Ergonode\Product\Domain\ValueObject\Sku;
use Ergonode\SharedKernel\Domain\Aggregate\ProductId;
use Ergonode\Value\Domain\ValueObject\ValueInterface;
use Webmozart\Assert\Assert;

class VariableProductImportActionDecorator extends VariableProductImportAction
{
    public function action(
        Sku $sku,
        string $template,
        array $categories,
        array $bindings,
        array $children,
        array $attributes = []
    ): VariableProduct {
        $templateId = $this->templateQuery->findTemplateIdByCode($template);
        if (null === $templateId) {
            throw new ImportException('Missing {template} template.', ['{template}' => $template]);
        }
        $productId = $this->productQuery->findProductIdBySku($sku);
        $categories = $this->getCategories($categories);
        $attributes = $this->builder->build($attributes);
        $bindings = $this->getBindings($bindings, $sku);
        $children = $this->getChildren($sku, $children);

        if (!$productId) {
            $productId = ProductId::generate();
            /** @var VariableProduct $product */
            $product = $this->productFactory->create(
                VariableProduct::TYPE,
                $productId,
                $sku,
                $templateId,
                $categories,
                $attributes,
            );
        } else {
            $product = $this->productRepository->load($productId);
            if (!$product instanceof VariableProduct) {
                throw new ImportException('Product {sku} is not a variable product', ['{sku}' => $sku]);
            }
            $product->changeTemplate($templateId);
            $product->changeCategories($categories);
            $attributes = $this->mergeSystemAttributes($product->getAttributes(), $attributes);
            $this->changeProductAttributes($product, $attributes);
        }

        $product->changeBindings($bindings);
        $product->changeChildren($children);

        $this->productRepository->save($product);

        return $product;
    }

    public function changeProductAttributes(AbstractProduct $product, array $attributes)
    {
        Assert::allString(array_keys($attributes));
        Assert::allIsInstanceOf($attributes, ValueInterface::class);

        foreach ($attributes as $code => $attribute) {
            $attributeCode = new AttributeCode($code);
            if ($product->hasAttribute($attributeCode)) {
                $product->changeAttribute($attributeCode, $attribute);
            } else {
                $product->addAttribute($attributeCode, $attribute);
            }
        }
    }
}
