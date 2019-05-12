<?php

namespace Bigcommerce\Api\v3\Resources;

use Bigcommerce\Api\v3\Resource;
use Bigcommerce\Api\v3\Client;

class Category extends Resource
{
    /**
     * {@inheritdoc}
     * @var string
     */
    public $url = '/catalog/categories';
    
    protected $ignoreOnCreate = array(
        'id',
        'parent_category_list',
    );
    
    protected $ignoreOnUpdate = array(
        'id',
        'parent_category_list',
    );
    
}
