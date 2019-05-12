<?php

namespace Bigcommerce\Api\v3\Resources;

use Bigcommerce\Api\v3\Resource;
use Bigcommerce\Api\v3\Client;

/**
 * A custom field on a product.
 */
class ProductCustomField extends Resource
{
    /**
     * {@inheritdoc}
     * @var string
     */
    public $url = '/catalog/products/{id}/custom-fields';
    
    protected $ignoreOnCreate = array(
        'id',
        'product_id'
    );
    
    protected $ignoreOnUpdate = array(
        'id',
        'product_id'
    );
}
