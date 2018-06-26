<?php

namespace ShoppingFeed\Manager\Model\Account\Store;

use ShoppingFeed\Manager\Api\Data\Account\StoreInterface;
use ShoppingFeed\Manager\Model\Config\FieldInterface;


interface ConfigInterface
{
    /**
     * @return string
     */
    public function getScope();

    /**
     * @return string[]
     */
    public function getScopeSubPath();

    /**
     * @param StoreInterface $store
     * @return FieldInterface[]
     */
    public function getFields(StoreInterface $store);

    /**
     * @param StoreInterface $store
     * @param string $name
     * @return FieldInterface|null
     */
    public function getField(StoreInterface $store, $name);

    /**
     * @return string
     */
    public function getFieldsetLabel();
}
