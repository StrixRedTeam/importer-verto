<?php
/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Product\Domain\Updater;

use Ergonode\Product\Domain\Entity\AbstractProduct;
use Ergonode\Attribute\Domain\Entity\AbstractAttribute;
use Ergonode\Attribute\Infrastructure\Mapper\AttributeValueMapper;

use Ergonode\Attribute\Domain\ValueObject\AttributeType;

class ProductAttributeUpdater
{
    private AttributeValueMapper $mapper;

    public function __construct(AttributeValueMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function update(AbstractProduct $product, AbstractAttribute $attribute, array $value): AbstractProduct
    {
        $type = new AttributeType($attribute->getType());
        $code = $attribute->getCode();

        $newValue = $this->mapper->map($type, $value, $product->getId());

        if ($product->hasAttribute($code)) {
            $oldValue = $product->getAttribute($code);
            $calculatedValue = $oldValue->merge($newValue);
            $product->changeAttribute($code, $calculatedValue);
        } else {
            $product->addAttribute($code, $newValue);
        }

        return $product;
    }

    public function remove(AbstractProduct $product, AbstractAttribute $attribute, array $value): AbstractProduct
    {
        $type = new AttributeType($attribute->getType());
        $code = $attribute->getCode();
        if (!$product->hasAttribute($code)) {
            return $product;
        }

        $oldValue = $product->getAttribute($code);
        $translation = $oldValue->getValue();

        foreach (array_keys($value) as $language) {
            if (array_key_exists($language, $translation)) {
                unset($translation[$language]);
            }
        }

        if (!empty($translation)) {
            $newValue = $this->mapper->map($type, $translation, $product->getId());
            $product->changeAttribute($code, $newValue);
        } else {
            $product->removeAttribute($code);
        }

        return $product;
    }
}
