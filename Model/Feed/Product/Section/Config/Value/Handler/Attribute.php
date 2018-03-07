<?php

namespace ShoppingFeed\Manager\Model\Feed\Product\Section\Config\Value\Handler;

use ShoppingFeed\Manager\Model\Account\Store\Config\Value\Handler\Option as OptionHandler;
use ShoppingFeed\Manager\Model\Feed\Product\Section\Config\Attribute\SourceInterface as AttributeSourceInterface;


class Attribute extends OptionHandler
{
    /**
     * @var AttributeSourceInterface
     */
    private $attributeSource;

    public function __construct(AttributeSourceInterface $attributeSource)
    {
        $this->attributeSource = $attributeSource;
        parent::__construct('text', $attributeSource->getAttributeOptionArray(false));
    }

    public function prepareRawValueForUse($value, $defaultValue, $isRequired)
    {
        $attributeCode = parent::prepareRawValueForUse($value, $defaultValue, $isRequired);
        return (null !== $attributeCode) ? $this->attributeSource->getAttributeByCode($attributeCode) : null;
    }
}