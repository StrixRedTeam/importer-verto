<?php
/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\ImporterVerto\Infrastructure\Model;

use Ergonode\Core\Domain\ValueObject\Language;
use Ergonode\Core\Domain\ValueObject\TranslatableString;

class ProductModel extends AbstractModel
{
    protected string $sku;

    protected string $type;

    protected string $template;

    protected array $attributes = [];

    protected array $existingAttributes = [];

    /**
     * @var string[]
     */
    protected array $categories = [];

    public function __construct(
        string $sku,
        string $type,
        string $template
    ) {
        $this->sku = $sku;
        $this->type = $type;
        $this->template = $template;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function addAttribute(string $code, string $language, string $value): void
    {
        if (!array_key_exists($code, $this->attributes)) {
            $this->attributes[$code] = new TranslatableString([]);
        }

        $this->attributes[$code] = $this->attributes[$code]->add(new Language($language), $value);
    }

    /**
     * @param string $code
     * @param mixed $attributeValue
     *
     * @return void
     *
     */
    public function addExistingAttribute(string $code, $attributeValue): void
    {
        $this->existingAttributes[$code] = $attributeValue;
    }

    public function hasAttribute(string $code): bool
    {
        return isset($this->attributes[$code]);
    }

    /**
     * @return string[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getExistingAttributes(): array
    {
        return $this->existingAttributes;
    }

    public function addFullAttribute(string $code, $attributeValue): void
    {
        $this->attributes[$code] = $attributeValue;
    }
}
