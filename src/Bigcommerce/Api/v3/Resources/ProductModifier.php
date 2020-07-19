<?php

namespace Bigcommerce\Api\v3\Resources;

use Bigcommerce\Api\v3\Resource;
use Bigcommerce\Api\v3\Client;

/**
 * A modifier on a product.
 */
class ProductModifier extends Resource
{
    /**
     * {@inheritdoc}
     */
    public $url = '/catalog/products/{id}/modifiers';
    
    /**
     * {@inheritdoc}
     */
    public $parentField = 'product_id';
    
    protected $ignoreOnCreate = array(
        'id',
        'product_id'
    );
    
    protected $ignoreOnUpdate = array(
        'id',
        'product_id'
    );
}
