<?php

namespace Bigcommerce\Api\v3\Resources;

use Bigcommerce\Api\v3\Resource;
use Bigcommerce\Api\v3\Client;

/**
 * An image which is displayed on the storefront for a product.
 */
class ProductImage extends Resource
{
    /**
     * {@inheritdoc}
     * @var string
     */
    public $url = '/catalog/products/{id}/images';
    
    protected $ignoreOnCreate = array(
        'id',
        'date_created',
        'product_id',
    );
    
    protected $ignoreOnUpdate = array(
        'id',
        'date_created',
        'product_id',
    );
}
