<?php

namespace Bigcommerce\Api\v3\Resources;

use Bigcommerce\Api\v3\Resource;
use Bigcommerce\Api\v3\Client;

/**
 * Represents a single product.
 */
class Product extends Resource
{
    /**
     * {@inheritdoc}
     * @var string
     */
    public $url = '/catalog/products';
    
    protected $ignoreOnCreate = array(
        'date_created',
        'date_modified',
    );
    
    /**
     * @see https://developer.bigcommerce.com/display/API/Products#Products-ReadOnlyFields
     * @var array
     */
    protected $ignoreOnUpdate = array(
        'id',
        'rating_total',
        'rating_count',
        'date_created',
        'date_modified',
        'date_last_imported',
        'number_sold',
        'brand',
        'images',
        'discount_rules',
        'configurable_fields',
        'custom_fields',
        'videos',
        'skus',
        'rules',
        'option_set',
        'options',
        'tax_class',
    );
    
    protected $ignoreIfZero = array(
        'tax_class_id',
    );
    
    /**
     * retrieve current product options
     * @return mixed array|string mapped collection or XML string if useXml is true
     */
    public function options()
    {
        return Client::getCollection($this->url . '/' . $this->id . '/options', 'ProductVariantOption');
    }
    
}
