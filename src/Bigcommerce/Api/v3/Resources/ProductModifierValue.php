<?php

namespace Bigcommerce\Api\v3\Resources;

use Bigcommerce\Api\v3\Resource;
use Bigcommerce\Api\v3\Client;

/**
 * A modifier value on a product.
 */
class ProductModifierValue extends Resource
{
    /**
     * {@inheritdoc}
     * @var string
     */
    public $url = '/catalog/products/{product_id}/modifiers/{id}/values';
    
    protected $ignoreOnCreate = array(
        'id',
        'product_id',
        'modifier_id',
    );
    
    protected $ignoreOnUpdate = array(
        'id',
        'product_id',
        'modifier_id'
    );
}
