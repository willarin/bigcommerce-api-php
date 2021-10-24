<?php

namespace Bigcommerce\Api;

use Bigcommerce\Api\Resource;

/**
 * Bigcommerce API Client.
 */
class BasicClient extends \Bigcommerce\Api\Client
{
    /**
     * Full Store URL to connect to
     *
     * @var string
     */
    protected static $store_url;
    
    /**
     * Connection instance
     *
     * @var Connection
     */
    protected static $connection;
    
    /**
     * Resource class name
     *
     * @var string
     */
    protected static $resource;
    
    /**
     * Resource path
     *
     * @var string
     */
    protected static $resource_path = '\\Bigcommerce\\Api\\';
    
    /**
     * API path prefix to be added to store URL for requests
     *
     * @var string
     */
    protected static $path_prefix = '/api/v2';
    
    /**
     * Username to connect to the store API with
     *
     * @var string
     */
    protected static $username;
    
    /**
     * API key
     *
     * @var string
     */
    protected static $api_key;
    
    /**
     * API client identifier
     *
     * @var string
     */
    protected static $client_id;
    
    /**
     * API authentication token
     *
     * @var string
     */
    protected static $auth_token;
    
    /**
     * Store API Hash
     *
     * @var string
     */
    protected static $store_hash;
    
    /**
     * API client secret
     *
     * @var string
     */
    protected static $client_secret;
    
    /**
     * API URL
     *
     * @var string
     */
    protected static $api_url = 'https://api.bigcommerce.com';
    
    /**
     * prefix for stores in url request
     *
     * @var string
     */
    protected static $stores_prefix = '/stores/%s/v2';
    
    /**
     * {@inheritdoc }
     */
    public static function configure($settings)
    {
        if (isset($settings['client_id'])) {
            static::configureOAuth($settings);
        } else {
            static::configureBasicAuth($settings);
        }
    }
    
    /**
     * {@inheritdoc }
     */
    public static function configureOAuth($settings)
    {
        if (!isset($settings['auth_token'])) {
            throw new \Exception("'auth_token' must be provided");
        }
        
        if (!isset($settings['store_hash'])) {
            throw new \Exception("'store_hash' must be provided");
        }
        
        static::$client_id = $settings['client_id'];
        static::$auth_token = $settings['auth_token'];
        static::$store_hash = $settings['store_hash'];
        
        static::$client_secret = isset($settings['client_secret']) ? $settings['client_secret'] : null;
        
        static::$api_path = static::$api_url . sprintf(static::$stores_prefix, static::$store_hash);
        static::$connection = false;
    }
    
    /**
     * {@inheritdoc }
     */
    public static function configureBasicAuth(array $settings)
    {
        if (!isset($settings['store_url'])) {
            throw new \Exception("'store_url' must be provided");
        }
        
        if (!isset($settings['username'])) {
            throw new \Exception("'username' must be provided");
        }
        
        if (!isset($settings['api_key'])) {
            throw new \Exception("'api_key' must be provided");
        }
        
        static::$username = $settings['username'];
        static::$api_key = $settings['api_key'];
        static::$store_url = rtrim($settings['store_url'], '/');
        static::$api_path = static::$store_url . static::$path_prefix;
        static::$connection = false;
    }
    
    /**
     * {@inheritdoc}
     */
    public static function getCollection($path, $resource = 'Resource')
    {
        $response = static::connection()->get(static::$api_path . $path);
        return static::mapCollection($resource, $response);
    }
    
    /**
     * Get an instance of the HTTP connection object. Initializes
     * the connection if it is not already active.
     *
     * @return Connection
     */
    protected static function connection()
    {
        if (!static::$connection) {
            static::$connection = new Connection();
            if (static::$client_id) {
                static::$connection->authenticateOauth(static::$client_id, static::$auth_token);
            } else {
                static::$connection->authenticateBasic(static::$username, static::$api_key);
            }
        }
        
        return static::$connection;
    }
    
    /**
     * Internal method to wrap items in a collection to resource classes.
     *
     * @param string $resource name of the resource class
     * @param array $object object collection
     * @return array
     */
    protected static function mapCollection($resource, $object)
    {
        if ($object == false || is_string($object)) {
            return $object;
        }
        
        static::$resource = static::locateResourceClass($resource);
        if ((isset($object->data)) and (is_array($object->data))) {
            $result = array_map(array('static', 'mapCollectionObject'), $object->data);
        } else {
            $result = array_map(array('static', 'mapCollectionObject'), $object);
        }
        
        return $result;
    }
    
    /**
     * Locate resource class
     *
     * @param string $resource resource class to map individual items
     * @return string path to resource object
     */
    public static function locateResourceClass($resource = 'Resource')
    {
        $baseResource = self::$resource_path . 'Resources\\' . $resource;
        $versionedResource = static::$resource_path . 'Resources\\' . $resource;
        return (class_exists($versionedResource)) ? $versionedResource : ((class_exists($baseResource)) ? $baseResource : static::$resource_path . 'Resource');
    }
    
    /**
     * Callback for mapping collection objects resource classes.
     *
     * @param \stdClass $object
     * @return Resource
     */
    public static function mapCollectionObject($object)
    {
        $class = static::$resource;
        
        return new $class($object);
    }
    
    /**
     * {@inheritdoc}
     */
    public static function getCustomer($id)
    {
        return static::getResource('/customers/' . $id, 'Customer');
    }
    
    /**
     * Get a resource entity from the specified endpoint.
     *
     * @param string $path api endpoint
     * @param string $resource resource class to map individual items
     * @return mixed Resource|string resource object or XML string if useXml is true
     */
    public static function getResource($path, $resource = 'Resource')
    {
        $response = static::connection()->get(static::$api_path . $path);
        
        return static::mapResource($resource, $response);
    }
    
    /**
     * Map a single object to a resource class.
     *
     * @param string $resource name of the resource class
     * @param \stdClass $object
     * @return mixed|Resource
     */
    protected static function mapResource($resource, $object)
    {
        if ($object == false || is_string($object)) {
            return $object;
        }
        
        $class = static::$resource = static::locateResourceClass($resource);
        $result = new $class($object);
        
        return $result;
    }
    
    /**
     * {@inheritdoc}
     */
    public static function updateCustomer($id, $object)
    {
        return static::updateResource('/customers/' . $id, $object);
    }
    
    /**
     * Send a put request to update the specified resource.
     *
     * @param string $path api endpoint
     * @param mixed $object object or XML string to update
     * @return mixed
     */
    public static function updateResource($path, $object, $resource = 'Resource')
    {
        if (is_array($object)) {
            $object = (object)$object;
        }
        
        $response = static::connection()->put(self::$api_path . $path, $object);
        
        return static::mapResource($resource, $response);
    }
}
