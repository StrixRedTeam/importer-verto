<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\ModelDescriber\Annotations;

use Doctrine\Common\Annotations\Reader;
use EXSyst\Component\Swagger\Schema;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @internal
 */
class SymfonyConstraintAnnotationReader
{
    /**
     * @var Reader
     */
    private $annotationsReader;

    /**
     * @var Schema
     */
    private $schema;

    public function __construct(Reader $annotationsReader)
    {
        $this->annotationsReader = $annotationsReader;
    }

    /**
     * Update the given property and schema with defined Symfony constraints.
     *
     * @param \ReflectionProperty|\ReflectionMethod $reflection
     */
    public function updateProperty($reflection, Schema $property)
    {
        foreach ($this->getAnnotations($reflection) as $annotation) {
            if ($annotation instanceof Assert\NotBlank || $annotation instanceof Assert\NotNull) {
                // To support symfony/validator < 4.3
                if ($annotation instanceof Assert\NotBlank && \property_exists($annotation, 'allowNull') && $annotation->allowNull) {
                    // The field is optional
                    continue;
                }

                // The field is required
                if (null === $this->schema) {
                    continue;
                }

                $propertyName = $this->getSchemaPropertyName($property);
                if (null === $propertyName) {
                    continue;
                }

                $existingRequiredFields = $this->schema->getRequired() ?? [];
                $existingRequiredFields[] = $propertyName;

                $this->schema->setRequired(array_values(array_unique($existingRequiredFields)));
            } elseif ($annotation instanceof Assert\Length) {
                $property->setMinLength($annotation->min);
                $property->setMaxLength($annotation->max);
            } elseif ($annotation instanceof Assert\Regex) {
                $this->appendPattern($property, $annotation->getHtmlPattern());
            } elseif ($annotation instanceof Assert\Count) {
                $property->setMinItems($annotation->min);
                $property->setMaxItems($annotation->max);
            } elseif ($annotation instanceof Assert\Choice) {
                $values = $annotation->callback ? call_user_func(is_array($annotation->callback) ? $annotation->callback : [$reflection->class, $annotation->callback]) : $annotation->choices;
                $property->setEnum(array_values($values));
            } elseif ($annotation instanceof Assert\Range) {
                $property->setMinimum($annotation->min);
                $property->setMaximum($annotation->max);
            } elseif ($annotation instanceof Assert\LessThan) {
                $property->setExclusiveMaximum($annotation->value);
            } elseif ($annotation instanceof Assert\LessThanOrEqual) {
                $property->setMaximum($annotation->value);
            } elseif ($annotation instanceof Assert\GreaterThan) {
                $property->setExclusiveMinimum($annotation->value);
            } elseif ($annotation instanceof Assert\GreaterThanOrEqual) {
                $property->setMinimum($annotation->value);
            }
        }
    }

    public function setSchema($schema)
    {
        $this->schema = $schema;
    }

    /**
     * Get assigned property name for property schema.
     */
    private function getSchemaPropertyName(Schema $property)
    {
        if (null === $this->schema) {
            return null;
        }

        foreach ($this->schema->getProperties() as $name => $schemaProperty) {
            if ($schemaProperty === $property) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Append the pattern from the constraint to the existing pattern.
     */
    private function appendPattern(Schema $property, $newPattern)
    {
        if (null === $newPattern) {
            return;
        }

        if (null !== $property->getPattern()) {
            $property->setPattern(sprintf('%s, %s', $property->getPattern(), $newPattern));
        } else {
            $property->setPattern($newPattern);
        }
    }

    /**
     * @param \ReflectionProperty|\ReflectionMethod $reflection
     */
    private function getAnnotations($reflection): \Traversable
    {
        if (\PHP_VERSION_ID >= 80000 && class_exists(Constraint::class)) {
            foreach ($reflection->getAttributes(Constraint::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                yield $attribute->newInstance();
            }
        }

        if ($reflection instanceof \ReflectionProperty) {
            yield from $this->annotationsReader->getPropertyAnnotations($reflection);
        } elseif ($reflection instanceof \ReflectionMethod) {
            yield from $this->annotationsReader->getMethodAnnotations($reflection);
        }
    }
}
