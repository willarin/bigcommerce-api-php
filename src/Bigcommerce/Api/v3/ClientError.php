<?php

namespace Bigcommerce\Api\v3;

/**
 * Raised when a client error (400+) is returned from the API.
 */
class ClientError extends Error
{
    public function __toString()
    {
        return "Client Error ({$this->code}): " . $this->message;
    }
}
