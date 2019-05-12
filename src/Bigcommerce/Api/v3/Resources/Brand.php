<?php

namespace Bigcommerce\Api\v3\Resources;

use Bigcommerce\Api\v3\Resource;
use Bigcommerce\Api\v3\Client;

class Brand extends Resource
{
    /**
     * {@inheritdoc}
     * @var string
     */
    public $url = '/catalog/brands';
    
    protected $ignoreOnCreate = array(
        'id',
    );
    
    protected $ignoreOnUpdate = array(
        'id',
    );
}
