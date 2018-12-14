<?php

namespace ShoppingFeed\Manager\Model\Sales\Order;

use ShoppingFeed\Manager\Api\Data\Account\StoreInterface;
use ShoppingFeed\Manager\Model\Account\Store\ConfigInterface as BaseConfig;

interface ConfigInterface extends BaseConfig
{
    /**
     * @param StoreInterface $store
     * @return bool
     */
    public function shouldUseItemReferenceAsProductId(StoreInterface $store);

    /**
     * @param StoreInterface $store
     * @return bool
     */
    public function shouldSyncNonImportedAddresses(StoreInterface $store);

    /**
     * @param StoreInterface $store
     * @return string
     */
    public function getDefaultEmailAddress(StoreInterface $store);

    /**
     * @param StoreInterface $store
     * @return string
     */
    public function getDefaultPhoneNumber(StoreInterface $store);

    /**
     * @param StoreInterface $store
     * @return string
     */
    public function getAddressFieldPlaceholder(StoreInterface $store);

    /**
     * @param StoreInterface $store
     * @return bool
     */
    public function shouldUseMobilePhoneNumberFirst(StoreInterface $store);

    /**
     * @param StoreInterface $store
     * @return bool
     */
    public function shouldCreateInvoice(StoreInterface $store);
}
