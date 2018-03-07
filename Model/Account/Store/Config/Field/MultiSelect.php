<?php

namespace ShoppingFeed\Manager\Model\Account\Store\Config\Field;

use Magento\Framework\Phrase;
use ShoppingFeed\Manager\Model\Account\Store\Config\Value\Handler\Option as OptionHandler;


class MultiSelect extends Select
{
    const NONE_OPTION_VALUE = '___sfm_none___';

    /**
     * @var int
     */
    private $size;

    /**
     * @param string $name
     * @param OptionHandler $valueHandler
     * @param string $label
     * @param bool $isRequired
     * @param mixed|null $defaultFormValue
     * @param mixed|null $defaultUseValue
     * @param Phrase|string $notice
     * @param array $dependencies
     * @param int $size
     */
    public function __construct(
        $name,
        OptionHandler $valueHandler,
        $label,
        $isRequired = false,
        $defaultFormValue = null,
        $defaultUseValue = null,
        $notice = '',
        array $dependencies = [],
        $size = 5
    ) {
        $this->size = $size;

        parent::__construct(
            $name,
            $valueHandler,
            $label,
            $isRequired,
            $defaultFormValue,
            $defaultUseValue,
            $notice,
            $dependencies
        );
    }

    protected function getEmptyOption()
    {
        return $this->isRequired() ? false : [ 'value' => self::NONE_OPTION_VALUE, 'label' => __('None') ];
    }

    public function getMetaConfig()
    {
        return array_merge(
            parent::getMetaConfig(),
            [ 'formElement' => 'multiselect', 'size' => $this->size ]
        );
    }

    protected function prepareRawValue($value, $defaultValue, $handlerPrepareMethod)
    {
        $isRequired = $this->isRequired();
        $valueHandler = $this->getValueHandler();

        if ($valueHandler->isUndefinedValue($value)) {
            $value = $defaultValue;
        }

        $value = (array) $value;

        foreach ($value as $key => $subValue) {
            $subValue = $valueHandler->$handlerPrepareMethod($subValue, null, $isRequired);

            if (null !== $subValue) {
                $value[$key] = $subValue;
            } else {
                unset($value[$key]);
            }
        }

        return $value;
    }

    public function prepareRawValueForForm($value)
    {
        $value = $this->prepareRawValue($value, [], 'prepareRawValueForForm');
        // Default values will be selected when an empty value is returned for a required field,
        // but returning an invalid value to avoid this allows the form to be saved without selecting any valid value.
        return empty($value) && !$this->isRequired() ? [ self::NONE_OPTION_VALUE ] : $value;
    }

    public function prepareRawValueForUse($value)
    {
        return $this->prepareRawValue($value, $this->getDefaultUseValue(), 'prepareRawValueForUse');
    }

    public function prepareFormValueForSave($value)
    {
        if (is_array($value)) {
            $isRequired = $this->isRequired();
            $valueHandler = $this->getValueHandler();

            foreach ($value as $key => $subValue) {
                if ($subValue !== self::NONE_OPTION_VALUE) {
                    $subValue = $valueHandler->prepareFormValueForSave($subValue, $isRequired);
                } else {
                    $subValue = null;
                }

                if (null !== $subValue) {
                    $value[$key] = $subValue;
                } else {
                    unset($value[$key]);
                }
            }

            return array_values($value);
        }

        return [];
    }
}
