<?php

namespace ShoppingFeed\Manager\Model\ResourceModel\Feed;

use ShoppingFeed\Manager\Model\ResourceModel\AbstractDb;


class Product extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('sfm_feed_product', 'product_id');
    }

    /**
     * @param int $productId
     * @param int[] $storeIds
     * @return array[]
     */
    public function getProductFeedAttributes($productId, array $storeIds = [])
    {
        $connection = $this->getConnection();

        $attributesSelect = $connection->select()
            ->from(
                $this->tableDictionary->getFeedProductTableName(),
                [ 'store_id', 'is_selected', 'selected_category_id' ]
            )
            ->where('product_id = ?', $productId);

        if (!empty($storeIds)) {
            $attributesSelect->where('store_id IN (?)', $storeIds);
        }

        $storeAttributes = [];

        foreach ($connection->fetchAll($attributesSelect) as $attributes) {
            $storeAttributes[$attributes['store_id']] = [
                'is_selected' => (bool) $attributes['is_selected'],
                'selected_category_id' => empty($attributes['selected_category_id'])
                    ? null
                    : (int) $attributes['selected_category_id'],
            ];
        }

        return $storeAttributes;
    }

    /**
     * @param int $productId
     * @param int $storeId
     * @param bool $isSelected
     * @param int|null $selectedCategoryId
     */
    public function updateProductFeedAttributes($productId, $storeId, $isSelected, $selectedCategoryId)
    {
        $connection = $this->getConnection();

        $connection->update(
            $this->tableDictionary->getFeedProductTableName(),
            [
                'is_selected' => (bool) $isSelected,
                'selected_category_id' => empty($selectedCategoryId) ? null : (int) $selectedCategoryId,
            ],
            $connection->quoteInto('product_id = ?', $productId)
            . ' AND '
            . $connection->quoteInto('store_id = ?', $storeId)
        );
    }

    /**
     * @param int $productId
     * @param int $storeId
     * @param int $baseExportState
     * @param int $childExportState
     * @param int $refreshState
     * @param bool $renewRetentionStart
     * @param bool $resetRetentionStart
     * @return $this
     */
    public function updateProductExportStates(
        $productId,
        $storeId,
        $baseExportState,
        $childExportState,
        $refreshState,
        $resetRetentionStart,
        $renewRetentionStart
    ) {
        $connection = $this->getConnection();
        $now = $this->timeHelper->utcDate();

        $values = [
            'export_state' => $baseExportState,
            'child_export_state' => $childExportState,
            'export_state_refreshed_at' => $now,
            'export_state_refresh_state' => $refreshState,
            'export_state_refresh_state_updated_at' => $now,
        ];

        if ($resetRetentionStart) {
            $values['export_retention_started_at'] = null;
        } elseif ($renewRetentionStart) {
            $values['export_retention_started_at'] = $now;
        }

        $connection->update(
            $this->tableDictionary->getFeedProductTableName(),
            $values,
            $connection->quoteInto('product_id = ?', $productId)
            . ' AND '
            . $connection->quoteInto('store_id = ?', $storeId)
        );

        return $this;
    }
}
