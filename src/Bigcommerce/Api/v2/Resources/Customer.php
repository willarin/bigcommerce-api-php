<?php

namespace Bigcommerce\Api\v2\Resources;

use Bigcommerce\Api\v2\Client;

class Customer extends \Bigcommerce\Api\Resources\Customer
{
    /**
     * {@inheritdoc }
     */
    protected $ignoreOnUpdate = array(
        'id',
        'date_created',
        'date_modified',
        'reset_pass_on_login',
        'accepts_marketing',
        'addresses',
        'form_fields'
    );
    
    /**
     * update customer data
     *
     * @return mixed
     */
    public function update()
    {
        return Client::updateCustomer($this->id, $this->getUpdateFields());
    }
}
