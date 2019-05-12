<?php

namespace Bigcommerce\Api\v2;

use Bigcommerce\Api\Filter;

/**
 * Bigcommerce API Client.
 */
class Client extends \Bigcommerce\Api\Client
{
    /**
     * check if method exists
     *
     * @param string $classMethodName
     * @return boolean
     */
    public static function classMethodExists($classMethodName)
    {
        return method_exists(__CLASS__, $classMethodName);
    }
    
    /**
     * Returns the total number of customer groups in the collection.
     *
     * @param array $filter
     * @return int|string number of products or XML string if useXml is true
     */
    public static function getCustomerGroupsCount($filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCount('/customer_groups/count' . $filter->toQuery());
    }
    
    /**
     * Returns the total number of customer addresses in the collection.
     *
     * @param array $filter
     * @return int|string number of products or XML string if useXml is true
     */
    public static function getCustomerAddressesCount($filter = [])
    {
        $customerId = $filter['id'];
        $filter = Filter::create($filter);
        return self::getCount('/customers/' . $customerId . '/addresses/count' . $filter->toQuery());
    }
    
    /**
     * Returns the total number of order coupons in the collection.
     *
     * Placeholder function always return 1 to pass control for objects retrieval
     * @param array $filter
     * @return int|string number of products or XML string if useXml is true
     */
    public static function getOrderCouponsCount($filter = [])
    {
        return 1;
    }
    
    /**
     * Returns the total number of order statuses in the collection.
     *
     * @param array $filter
     * @return int|string number of products or XML string if useXml is true
     */
    public static function getOrderStatusesCount($filter = [])
    {
        return 1;
    }
    
    /**
     * The total number of order products in the collection.
     *
     * @param array $filterIncoming
     * @param array $filter
     * @return mixed
     */
    public static function getOrderProductsCount($filterIncoming, $filter = array())
    {
        $orderId = @$filterIncoming['id'];
        unset($filterIncoming['id']);
        return parent::getOrderProductsCount($orderId, $filterIncoming);
    }
    
    
    /**
     * Get order coupons for a given order
     *
     * @param $orderID
     * @param array $filter
     * @return mixed
     */
    public static function getOrderCoupons($orderID, $filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCollection('/orders/' . $orderID . '/coupons' . $filter->toQuery(), 'OrderCoupons');
    }
}
