<?php
declare(strict_types=1);

namespace Ergonode\ImporterVerto\Infrastructure\Reader;

use Ergonode\ImporterVerto\Infrastructure\Model\ProductModel;
use Ergonode\Product\Domain\Entity\SimpleProduct;

class VertoProductReader extends AbstractVertoReader
{
    public const DEFAULT_LANGUAGE = 'pl_PL';
    public const TEMPLATE_ATTRIBUTE = 'kat_2';
    public const SIMPLE_PRODUCT_TEMPLATE = 'Produkty proste';
    public const BINDING_ATTRIBUTE = 'rozmiar';
    public const IDENTIFYING_ATTRIBUTE = 'ean_code';

    private const KEYS = [
        self::IDENTIFYING_ATTRIBUTE,
    ];

    public function read(): ?ProductModel
    {
        $item = null;
        $attributes = $this->prepareAttributes();

        while ($this->records->valid()) {
            $record = $this->records->current();

            if (null === $item) {
                $item = new ProductModel(
                    $record[self::IDENTIFYING_ATTRIBUTE],
                    SimpleProduct::TYPE,
                    self::SIMPLE_PRODUCT_TEMPLATE
                );
            } elseif ($item->getSku() !== $record[self::IDENTIFYING_ATTRIBUTE]) {
                break;
            }

            foreach ($attributes as $attribute) {
                $value = str_replace("\\n", "\n", $record[$attribute]);
                $item->addAttribute($attribute, self::DEFAULT_LANGUAGE, $value);
            }

            foreach ($record as $key => $value) {
                if ('' !== $value && !array_key_exists($key, self::KEYS) && !array_key_exists($key, $attributes)) {
                    $item->addParameter($key, $value);
                }
            }

            $this->records->next();
        }

        return $item;
    }

    private function prepareAttributes(): array
    {
        return array_filter($this->headers, static function ($item) {
            return '_' !== $item[0];
        });
    }
}
