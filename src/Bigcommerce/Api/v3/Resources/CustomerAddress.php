<?php

namespace Bigcommerce\Api\v3\Resources;

use Bigcommerce\Api\v3\Resource;

class CustomerAddress extends Resource
{
    /**
     * {@inheritdoc}
     * @var string
     */
    public $url = '/customers/addresses';
    
    /**
     * {@inheritdoc}
     * @var string
     */
    public $pluralOperationName = 'CustomerAddresses';
}
