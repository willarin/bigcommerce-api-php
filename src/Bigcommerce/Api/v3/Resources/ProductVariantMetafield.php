<?php

namespace Bigcommerce\Api\v3\Resources;

use Bigcommerce\Api\v3\Resource;
use Bigcommerce\Api\v3\Client;

/**
 * A variant option value on a product.
 */
class ProductVariantMetafield extends Resource
{
    /**
     * {@inheritdoc}
     * @var string
     */
    public $url = '/catalog/products/{product_id}/variants/{id}/metafields';
    
    protected $ignoreOnCreate = array(
        'id',
        'product_id',
        'variant_id',
    );
    
    protected $ignoreOnUpdate = array(
        'id',
        'product_id',
        'variant_id'
    );
}
