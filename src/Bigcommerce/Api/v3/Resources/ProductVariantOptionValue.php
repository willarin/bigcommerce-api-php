<?php

namespace Bigcommerce\Api\v3\Resources;

use Bigcommerce\Api\v3\Resource;
use Bigcommerce\Api\v3\Client;

/**
 * A variant option value on a product.
 */
class ProductVariantOptionValue extends Resource
{
    /**
     * {@inheritdoc}
     * @var string
     */
    public $url = '/catalog/products/{product_id}/options/{id}/values';
    
    protected $ignoreOnCreate = array(
        'id',
        'product_id',
        'option_id',
    );
    
    protected $ignoreOnUpdate = array(
        'id',
        'product_id',
        'option_id'
    );
}
