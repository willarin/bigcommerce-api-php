<?php

namespace Bigcommerce\Api\v3\Resources;

use Bigcommerce\Api\v3\Resource;
use Bigcommerce\Api\v3\Client;

/**
 * A variant of a product.
 */
class ProductVariant extends Resource
{
    /**
     * {@inheritdoc}
     * @var string
     */
    public $url = '/catalog/products/{id}/variants';
    
    protected $ignoreOnCreate = array(
        'id',
        'product_id'
    );
    
    protected $ignoreOnUpdate = array(
        'id',
        'product_id'
    );
    
}
