<?php

namespace Bigcommerce\Api\v3\Resources;

use Bigcommerce\Api\v3\Resource;
use Bigcommerce\Api\v3\Client;

/**
 * A meta field on a product.
 */
class BrandMetafield extends Resource
{
    /**
     * {@inheritdoc}
     * @var string
     */
    public $url = '/catalog/brands/{id}/metafields';
}
