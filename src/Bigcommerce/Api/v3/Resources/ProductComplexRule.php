<?php

namespace Bigcommerce\Api\v3\Resources;

use Bigcommerce\Api\v3\Resource;
use Bigcommerce\Api\v3\Client;

/**
 * A variant of a product.
 */
class ProductComplexRule extends Resource
{
    /**
     * {@inheritdoc}
     * @var string
     */
    public $url = '/catalog/products/{id}/complex-rules';
    
    protected $ignoreOnCreate = array(
        'id',
        'product_id'
    );
    
    protected $ignoreOnUpdate = array(
        'id',
        'product_id'
    );
    
}
