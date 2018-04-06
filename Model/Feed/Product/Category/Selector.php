<?php

namespace ShoppingFeed\Manager\Model\Feed\Product\Category;

use Magento\Catalog\Model\Category as CatalogCategory;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use ShoppingFeed\Manager\Api\Data\Account\StoreInterface;
use ShoppingFeed\Manager\Model\Feed\Product\Category as FeedCategory;
use ShoppingFeed\Manager\Model\Feed\Product\CategoryFactory as FeedCategoryFactory;


class Selector implements SelectorInterface
{
    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var FeedCategoryFactory
     */
    private $feedCategoryFactory;

    /**
     * @var FeedCategory[][]
     */
    private $storeCategoryList = [];

    /**
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param FeedCategoryFactory $feedCategoryFactory
     */
    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        FeedCategoryFactory $feedCategoryFactory
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->feedCategoryFactory = $feedCategoryFactory;
    }

    /**
     * @param StoreInterface $store
     * @return FeedCategory[]
     */
    private function getStoreCategoryList(StoreInterface $store)
    {
        $storeId = $store->getBaseStoreId();

        if (!isset($this->storeCategoryList[$storeId])) {
            $this->storeCategoryList[$storeId] = [];
            $categoryCollection = $this->categoryCollectionFactory->create();
            $categoryCollection->addIsActiveFilter();
            $categoryCollection->addNameToResult();
            $categoryCollection->addUrlRewriteToResult();

            /** @var CatalogCategory $category */
            foreach ($categoryCollection as $category) {
                $feedCategory = $this->feedCategoryFactory->create();
                $feedCategory->setCatalogCategory($category);
                $this->storeCategoryList[$storeId][$category->getId()] = $feedCategory;
            }
        }

        return $this->storeCategoryList[$storeId];
    }

    /**
     * @param FeedCategory $category
     * @param FeedCategory[] $categories
     * @return FeedCategory[]
     */
    protected function getCategoryPath(FeedCategory $category, array $categories)
    {
        $categoryPath = [ $category ];
        $parentLevel = $category->getLevel() - 1;
        $parentId = $category->getParentId();

        while ($parentId && ($parentLevel >= 2) && isset($categories[$parentId])) {
            $categoryPath[] = $categories[$parentId];
            $parentId = $categories[$parentId]->getParentId();
            $parentLevel--;
        }

        return $categoryPath;
    }

    /**
     * @param int $categoryId
     * @param int[] $selectionIds
     * @param string $selectionMode
     * @return bool
     */
    private function isSelectableCategory($categoryId, array $selectionIds, $selectionMode)
    {
        $isSelected = in_array($categoryId, $selectionIds, true);
        return ($selectionMode === self::SELECTION_MODE_INCLUDE) ? $isSelected : !$isSelected;
    }

    public function getCatalogProductCategoryPath(
        CatalogProduct $product,
        StoreInterface $store,
        $preselectedCategoryId,
        array $selectionIds,
        $selectionMode,
        $maximumLevel = PHP_INT_MAX,
        $levelWeightMultiplier = 1,
        $useParentCategories = false,
        $includableParentCount = 1,
        $minimumParentLevel = 1,
        $parentWeightMultiplier = 1
    ) {
        $categories = $this->getStoreCategoryList($store);
        $categoryIds = $product->getCategoryIds();
        $selectedCategoryId = null;

        if (!empty($preselectedCategoryId)
            && isset($categoryIds[$preselectedCategoryId])
            && $this->isSelectableCategory((int) $preselectedCategoryId, $selectionIds, $selectionMode)
        ) {
            $selectedCategoryId = $preselectedCategoryId;
        } else {
            $categoryWeights = [];

            foreach ($categoryIds as $categoryId) {
                if (isset($categories[$categoryId])
                    && ($categories[$categoryId]->getLevel() <= $maximumLevel)
                    && $this->isSelectableCategory((int) $categoryId, $selectionIds, $selectionMode)
                ) {
                    $categoryWeights[$categoryId] = $categories[$categoryId]->getLevel() * $levelWeightMultiplier;
                }
            }

            if ($useParentCategories) {
                foreach ($categoryIds as $categoryId) {
                    if (isset($categories[$categoryId])) {
                        $parentLevel = $categories[$categoryId]->getLevel();
                        $parentCount = 0;
                        $parentId = $categories[$categoryId]->getParentId();

                        while ($parentId
                            && (--$parentLevel >= $minimumParentLevel)
                            && (++$parentCount <= $includableParentCount)
                            && isset($categories[$parentId])
                        ) {
                            if (!isset($categoryWeights[$parentId])
                                && $this->isSelectableCategory($categoryId, $selectionIds, $selectionMode)
                            ) {
                                $categoryWeights[$parentId] = $parentLevel
                                    * $levelWeightMultiplier
                                    * $parentWeightMultiplier;
                            }

                            $parentId = $categories[$parentId]->getParentId();
                        }
                    }
                }
            }

            if (empty($categoryWeights)) {
                return null;
            }

            arsort($categoryWeights, SORT_NUMERIC);
            reset($categoryWeights);
            $selectedCategoryId = key($categoryWeights);
        }

        return $this->getCategoryPath($categories[$selectedCategoryId], $categories);
    }
}
