<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\Stock\Command;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\InventoryApi\Api\Data\StockSearchResultsInterface;

/**
 * Find Stocks by SearchCriteria command (Service Provider Interface - SPI)
 *
 * Separate command interface to which Repository proxies initial GetList call, could be considered as SPI - Interfaces
 * that you should extend and implement to customize current behaviour, but NOT expected to be used (called) in the code
 * of business logic directly
 *
 * @see \Magento\InventoryApi\Api\StockRepositoryInterface
 * @api
 */
interface GetListInterface
{
    /**
     * Find Stocks by given SearchCriteria
     *
     * SearchCriteria is not required because load all stocks is useful case
     *
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return StockSearchResultsInterface
     */
    public function execute(?SearchCriteriaInterface $searchCriteria = null): StockSearchResultsInterface;
}
