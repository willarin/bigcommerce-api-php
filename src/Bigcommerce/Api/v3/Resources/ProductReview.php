<?php

namespace Bigcommerce\Api\v3\Resources;

use Bigcommerce\Api\v3\Resource;
use Bigcommerce\Api\v3\Client;

/**
 * A product review.
 */
class ProductReview extends Resource
{
    /**
     * {@inheritdoc}
     * @var string
     */
    public $url = '/catalog/products/{id}/reviews';
}
