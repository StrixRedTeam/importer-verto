<?php
declare(strict_types=1);

namespace Ergonode\ImporterVerto\Infrastructure\Model;

class VariableProductModel extends ProductModel
{
    public const IDENTIFYING_ATTRIBUTE = 'modelokolor';
    protected const UNIQUE_ATTRIBUTES = ['idarticle', 'index','ean_code'];

    protected string $bindingAttribute;

    protected array $children;

    public function __construct(
        string $sku,
        string $type,
        string $template,
        string $bindingAttribute,
        array $attributes,
        string $childSku
    ) {
        parent::__construct($sku, $type, $template);
        $this->bindingAttribute = $bindingAttribute;
        $this->attributes = $this->trimUniqueAttributes($attributes);
        $this->addChildren($childSku);
    }

    public function getBindingAttribute(): string
    {
        return $this->bindingAttribute;
    }

    private function trimUniqueAttributes(array $attributes): array
    {
        foreach ($attributes as $attributeKey => $value) {
            if (in_array($attributeKey, self::UNIQUE_ATTRIBUTES, true)) {
                unset($attributes[$attributeKey]);
            }
        }

        return $attributes;
    }

    public function addChildren(string $childSku): void
    {
        $this->children[] = $childSku;
    }

    public function getChildren(): array
    {
        return $this->children;
    }
}
