<?php

namespace Bigcommerce\Api\v3;

use \Exception as Exception;
use Firebase\JWT\JWT;

/**
 * Bigcommerce API Client.
 */
class Client
{
    /**
     * Full URL path to the configured store API.
     *
     * @var string
     */
    public static $api_path;
    /**
     * Full Store URL to connect to
     *
     * @var string
     */
    private static $store_url;
    /**
     * Username to connect to the store API with
     *
     * @var string
     */
    private static $username;
    /**
     * API key
     *
     * @var string
     */
    private static $api_key;
    /**
     * Connection instance
     *
     * @var Connection
     */
    private static $connection;
    /**
     * Resource class name
     *
     * @var string
     */
    private static $resource;
    /**
     * API path prefix to be added to store URL for requests
     *
     * @var string
     */
    private static $path_prefix = '/api/v3';
    private static $client_id;
    private static $store_hash;
    private static $auth_token;
    private static $client_secret;
    private static $stores_prefix = '/stores/%s/v3';
    private static $api_url = 'https://api.bigcommerce.com';
    private static $login_url = 'https://login.bigcommerce.com';
    
    /**
     * Configure the API client with the required settings to access
     * the API for a store.
     *
     * Accepts OAuth and (for now!) Basic Auth credentials
     *
     * @param array $settings
     */
    public static function configure($settings)
    {
        if (isset($settings['client_id'])) {
            self::configureOAuth($settings);
        } else {
            self::configureBasicAuth($settings);
        }
    }
    
    /**
     * Configure the API client with the required OAuth credentials.
     *
     * Requires a settings array to be passed in with the following keys:
     *
     * - client_id
     * - auth_token
     * - store_hash
     *
     * @param array $settings
     * @throws \Exception
     */
    public static function configureOAuth($settings)
    {
        if (!isset($settings['auth_token'])) {
            throw new Exception("'auth_token' must be provided");
        }
        
        if (!isset($settings['store_hash'])) {
            throw new Exception("'store_hash' must be provided");
        }
        
        self::$client_id = $settings['client_id'];
        self::$auth_token = $settings['auth_token'];
        self::$store_hash = $settings['store_hash'];
        
        self::$client_secret = isset($settings['client_secret']) ? $settings['client_secret'] : null;
        
        self::$api_path = self::$api_url . sprintf(self::$stores_prefix, self::$store_hash);
        self::$connection = false;
    }
    
    /**
     * Configure the API client with the required credentials.
     *
     * Requires a settings array to be passed in with the following keys:
     *
     * - store_url
     * - username
     * - api_key
     *
     * @param array $settings
     * @throws \Exception
     */
    public static function configureBasicAuth(array $settings)
    {
        if (!isset($settings['store_url'])) {
            throw new Exception("'store_url' must be provided");
        }
        
        if (!isset($settings['username'])) {
            throw new Exception("'username' must be provided");
        }
        
        if (!isset($settings['api_key'])) {
            throw new Exception("'api_key' must be provided");
        }
        
        self::$username = $settings['username'];
        self::$api_key = $settings['api_key'];
        self::$store_url = rtrim($settings['store_url'], '/');
        self::$api_path = self::$store_url . self::$path_prefix;
        self::$connection = false;
    }
    
    /**
     * Configure the API client to throw exceptions when HTTP errors occur.
     *
     * Note that network faults will always cause an exception to be thrown.
     *
     * @param bool $variants the value of this flag
     */
    public static function failOnError($option = true)
    {
        self::connection()->failOnError($option);
    }
    
    /**
     * Get an instance of the HTTP connection object. Initializes
     * the connection if it is not already active.
     *
     * @return Connection
     */
    private static function connection()
    {
        if (!self::$connection) {
            self::$connection = new Connection();
            if (self::$client_id) {
                self::$connection->authenticateOauth(self::$client_id, self::$auth_token);
            } else {
                self::$connection->authenticateBasic(self::$username, self::$api_key);
            }
        }
        
        return self::$connection;
    }
    
    /**
     * Return XML strings from the API instead of building objects.
     */
    public static function useXml()
    {
        self::connection()->useXml();
    }
    
    /**
     * Return JSON objects from the API instead of XML Strings.
     * This is the default behavior.
     */
    public static function useJson()
    {
        self::connection()->useXml(false);
    }
    
    /**
     * Switch SSL certificate verification on requests.
     *
     * @param bool $variants the value of this flag
     */
    public static function verifyPeer($option = false)
    {
        self::connection()->verifyPeer($option);
    }
    
    /**
     * Connect to the internet through a proxy server.
     *
     * @param string $host host server
     * @param int|bool $port port number to use, or false
     */
    public static function useProxy($host, $port = false)
    {
        self::connection()->useProxy($host, $port);
    }
    
    /**
     * Get error message returned from the last API request if
     * failOnError is false (default).
     *
     * @return string
     */
    public static function getLastError()
    {
        return self::connection()->getLastError();
    }
    
    /**
     * Convenience method to return instance of the connection
     *
     * @return Connection
     */
    public static function getConnection()
    {
        return self::connection();
    }
    
    /**
     * Set the HTTP connection object. DANGER: This can screw up your Client!
     *
     * @param Connection $connection The connection to use
     */
    public static function setConnection(Connection $connection = null)
    {
        self::$connection = $connection;
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
        $response = self::connection()->get(self::$api_path . $path);
        
        return self::mapResource($resource, $response);
    }
    
    /**
     * Map a single object to a resource class.
     *
     * @param string $resource name of the resource class
     * @param \stdClass $object
     * @return Resource
     */
    private static function mapResource($resource, $object)
    {
        if ($object == false || is_string($object)) {
            return $object;
        }
        
        $baseResource = __NAMESPACE__ . '\\' . $resource;
        $class = (class_exists($baseResource)) ? $baseResource : 'Bigcommerce\\Api\\v3\\Resources\\' . $resource;
        return new $class($object->data);
    }
    
    /**
     * Get a count value from the specified endpoint.
     *
     * @param string $path api endpoint
     * @return mixed int|string count value or XML string if useXml is true
     */
    public static function getCount($path)
    {
        $response = self::connection()->get(self::$api_path . $path);
        
        if ($response == false || is_string($response)) {
            return $response;
        }
        
        return $response->meta->pagination->total;
    }
    
    /**
     * Send a post request to create a resource on the specified collection.
     *
     * @param string $path api endpoint
     * @param mixed $object object or XML string to create
     * @return mixed
     */
    public static function createResource($path, $object, $resource = 'Resource')
    {
        if (is_array($object)) {
            $object = (object)$object;
        }
        
        $response = self::connection()->post(self::$api_path . $path, $object);
        
        return self::mapResource($resource, $response);
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
        
        $response = self::connection()->put(self::$api_path . $path, $object);
        
        return self::mapResource($resource, $response);
    }
    
    /**
     * Send a delete request to remove the specified resource.
     *
     * @param string $path api endpoint
     * @return mixed
     */
    public static function deleteResource($path)
    {
        return self::connection()->delete(self::$api_path . $path);
    }
    
    /**
     * Swaps a temporary access code for a long expiry auth token.
     *
     * @param \stdClass|array $object
     * @return \stdClass
     */
    public static function getAuthToken($object)
    {
        $context = array_merge(array('grant_type' => 'authorization_code'), (array)$object);
        $connection = new Connection();
        
        return $connection->post(self::$login_url . '/oauth2/token', $context);
    }
    
    /**
     * @param int $id
     * @param string $redirectUrl
     * @param string $requestIp
     * @return string
     */
    public static function getCustomerLoginToken($id, $redirectUrl = '', $requestIp = '')
    {
        if (empty(self::$client_secret)) {
            throw new Exception('Cannot sign customer login tokens without a client secret');
        }
        
        $payload = array(
            'iss' => self::$client_id,
            'iat' => time(),
            'jti' => bin2hex(random_bytes(32)),
            'operation' => 'customer_login',
            'store_hash' => self::$store_hash,
            'customer_id' => $id
        );
        
        if (!empty($redirectUrl)) {
            $payload['redirect_to'] = $redirectUrl;
        }
        
        if (!empty($requestIp)) {
            $payload['request_ip'] = $requestIp;
        }
        
        return JWT::encode($payload, self::$client_secret, 'HS256');
    }
    
    /**
     * Pings the time endpoint to test the connection to a store.
     *
     * @return \DateTime
     */
    public static function getTime()
    {
        $response = self::connection()->get(self::$api_path . '/time');
        
        if ($response == false || is_string($response)) {
            return $response;
        }
        
        return new \DateTime("@{$response->time}");
    }
    
    /**
     * Returns the collection of summary.
     *
     * @param array $filter
     * @return array
     */
    public static function getSummary($filter = false)
    {
        return self::getCollection('/catalog/summary', 'Variant');
    }
    
    /**
     * Get a collection result from the specified endpoint.
     *
     * @param string $path api endpoint
     * @param string $resource resource class to map individual items
     * @return mixed array|string mapped collection or XML string if useXml is true
     */
    public static function getCollection($path, $resource = 'Resource')
    {
        $response = self::connection()->get(self::$api_path . $path);
        
        return self::mapCollection($resource, $response);
    }
    
    /**
     * Internal method to wrap items in a collection to resource classes.
     *
     * @param string $resource name of the resource class
     * @param array $object object collection
     * @return array
     */
    private static function mapCollection($resource, $object)
    {
        if ($object == false || is_string($object)) {
            return $object;
        }
        
        $baseResource = __NAMESPACE__ . '\\' . $resource;
        self::$resource = (class_exists($baseResource)) ? $baseResource : 'Bigcommerce\\Api\\v3\\Resources\\' . $resource;
        
        return array_map(array('self', 'mapCollectionObject'), $object->data);
    }
    
    /**
     * Callback for mapping collection objects resource classes.
     *
     * @param \stdClass $object
     * @return Resource
     */
    private static function mapCollectionObject($object)
    {
        $class = self::$resource;
        
        return new $class($object);
    }
    
    /**
     * Map object representing a count to an integer value.
     *
     * @param \stdClass $object
     * @return int
     */
    private static function mapCount($object)
    {
        if ($object == false || is_string($object)) {
            return $object;
        }
        
        return $object->meta->pagination->total;
    }

//    /**
//     * Returns the collection of variants.
//     *
//     * @param array $filter
//     * @return array
//     */
//    public static function getVariants($filter = array())
//    {
//        $filter = Filter::create($filter);
//        return self::getCollection('/catalog/variants' . $filter->toQuery(), 'Variant');
//    }
//
//    /**
//     * Returns the total number of variants in the collection.
//     *
//     * @param array $filter
//     * @return int|string number of products or XML string if useXml is true
//     */
//    public static function getVariantsCount($filter = array())
//    {
//        $filter = Filter::create($filter);
//        return self::getCount('/catalog/variants' . $filter->toQuery());
//    }
//
//    /**
//     * Create Variants
//     *
//     * @param $object
//     * @return mixed
//     */
//    public static function createVariant($object)
//    {
//        return self::createResource('/catalog/variants', $object);
//    }
//
//    /**
//     * Creates variant options
//     * @param $parentId
//     * @param $object
//     * @return mixed
//     */
//    public static function createVariantOption($parentId, $object)
//    {
//        return parent::createVariantOption($object, $parentId);
//    }
//
//    /**
//     * A single variant by given id.
//     *
//     * @param int $id variant id
//     * @return Resources\Variant
//     */
//    public static function getVariant($id)
//    {
//        return self::getResource('/catalog/variants/' . $id, 'Variant');
//    }
//
//    /**
//     * Update the given variant.
//     *
//     * @param int $id variant id
//     * @param mixed $object
//     * @return mixed
//     */
//    public static function updateVariant($id, $object)
//    {
//        return self::updateResource('/catalog/variants/' . $id, $object);
//    }
//
//    /**
//     * Delete the given variant.
//     *
//     * @param int $id option id
//     * @return mixed
//     */
//    public static function deleteVariant($id)
//    {
//        Client::deleteResource('/catalog/variants/' . $id);
//    }
//
//    /**
//     * The collection of categories.
//     *
//     * @param array $filter
//     * @return array
//     */
//    public static function getCategories($filter = array())
//    {
//        $filter = Filter::create($filter);
//        return self::getCollection('/catalog/categories' . $filter->toQuery(), 'Category');
//    }
//
//    /**
//     * The number of categories in the collection.
//     *
//     * @param array $filter
//     * @return int
//     */
//    public static function getCategoriesCount($filter = array())
//    {
//        $filter = Filter::create($filter);
//        return self::getCount('/catalog/categories' . $filter->toQuery());
//    }
//
//    /**
//     * A single category by given id.
//     *
//     * @param int $id category id
//     * @return Resources\Category
//     */
//    public static function getCategory($id)
//    {
//        return self::getResource('/catalog/categories/' . $id, 'Category');
//    }
//
//    /**
//     * Create a new category from the given data.
//     *
//     * @param mixed $object
//     * @return mixed
//     */
//    public static function createCategory($object)
//    {
//        return self::createResource('/catalog/categories', $object);
//    }
//
//    /**
//     * Update the given category.
//     *
//     * @param int $id category id
//     * @param mixed $object
//     * @return mixed
//     */
//    public static function updateCategory($id, $object)
//    {
//        return self::updateResource('/catalog/categories/' . $id, $object);
//    }
//
//    /**
//     * Delete the given category.
//     *
//     * @param int $id category id
//     * @return mixed
//     */
//    public static function deleteCategory($id)
//    {
//        return self::deleteResource('/catalog/categories/' . $id);
//    }
//
//    /**
//     * Delete all categories.
//     *
//     * @return mixed
//     */
//    public static function deleteAllCategories()
//    {
//        return self::deleteResource('/catalog/categories');
//    }
//
//    /**
//     * Returns the default collection of products.
//     *
//     * @param array $filter
//     * @return mixed array|string list of products or XML string if useXml is true
//     */
//    public static function getProducts($filter = array())
//    {
//        $filter = Filter::create($filter);
//        return self::getCollection('/catalog/products' . $filter->toQuery(), 'Product');
//    }
//
//    /**
//     * Returns the total number of products in the collection.
//     *
//     * @param array $filter
//     * @return int|string number of products or XML string if useXml is true
//     */
//    public static function getProductsCount($filter = array())
//    {
//        $filter = Filter::create($filter);
//        return self::getCount('/catalog/products' . $filter->toQuery());
//    }
//
//    /**
//     * Returns a single product resource by the given id.
//     *
//     * @param int $id product id
//     * @return Resources\Product|string
//     */
//    public static function getProduct($id)
//    {
//        return self::getResource('/catalog/products/' . $id, 'Product');
//    }
//
//    /**
//     * Create a new product.
//     *
//     * @param mixed $object fields to create
//     * @return mixed
//     */
//    public static function createProduct($object)
//    {
//        return self::createResource('/catalog/products', $object);
//    }
//
//    /**
//     * Update the given product.
//     *
//     * @param int $id product id
//     * @param mixed $object fields to update
//     * @return mixed
//     */
//    public static function updateProduct($id, $object)
//    {
//        return self::updateResource('/catalog/products/' . $id, $object);
//    }
//
//    /**
//     * Delete the given product.
//     *
//     * @param int $id product id
//     * @return mixed
//     */
//    public static function deleteProduct($id)
//    {
//        return self::deleteResource('/catalog/products/' . $id);
//    }
//
//    /**
//     * Delete all products.
//     *
//     * @return mixed
//     */
//    public static function deleteAllProducts()
//    {
//        return self::deleteResource('/catalog/products');
//    }
//
//    /**
//     * Gets collection of custom fields for a product.
//     *
//     * @param int $id product ID
//     * @return array|string list of products or XML string if useXml is true
//     */
//    public static function getProductCustomFields($id)
//    {
//        return self::getCollection('/catalog/products/' . $id . '/custom-fields', 'ProductCustomField');
//    }
//
//    /**
//     * Returns a single custom field by given id
//     * @param  int $product_id product id
//     * @param  int $id custom field id
//     * @return Resources\ProductCustomField|bool Returns ProductCustomField if exists, false if not exists
//     */
//    public static function getProductCustomField($product_id, $id)
//    {
//        return self::getResource('/catalog/products/' . $product_id . '/custom-fields/' . $id, 'ProductCustomField');
//    }
//
//    /**
//     * Create a new custom field for a given product.
//     *
//     * @param int $product_id product id
//     * @param mixed $object fields to create
//     * @return Object Object with `id`, `product_id`, `name` and `text` keys
//     */
//    public static function createProductCustomField($product_id, $object)
//    {
//        return self::createResource('/catalog/products/' . $product_id . '/custom-fields', $object);
//    }
//
//    /**
//     * Update the given custom field.
//     *
//     * @param int $product_id product id
//     * @param int $id custom field id
//     * @param mixed $object custom field to update
//     * @return mixed
//     */
//    public static function updateProductCustomField($product_id, $id, $object)
//    {
//        return self::updateResource('/catalog/products/' . $product_id . '/custom-fields/' . $id, $object);
//    }
//
//    /**
//     * Delete the given custom field.
//     *
//     * @param int $product_id product id
//     * @param int $id custom field id
//     * @return mixed
//     */
//    public static function deleteProductCustomField($product_id, $id)
//    {
//        return self::deleteResource('/catalog/products/' . $product_id . '/custom-fields/' . $id);
//    }
//
//    /**
//     * Gets collection of meta fields for a product.
//     *
//     * @param int $id product ID
//     * @return array|string list of products or XML string if useXml is true
//     */
//    public static function getProductMetaFields($id)
//    {
//        return self::getCollection('/catalog/products/' . $id . '/metafields', 'ProductMetaField');
//    }
//
//    /**
//     * Returns a single meta field by given id
//     * @param  int $product_id product id
//     * @param  int $id meta field id
//     * @return Resources\ProductMetaField|bool Returns ProductMetaField if exists, false if not exists
//     */
//    public static function getProductMetaField($product_id, $id)
//    {
//        return self::getResource('/catalog/products/' . $product_id . '/metafields/' . $id, 'ProductMetaField');
//    }
//
//    /**
//     * Create a new meta field for a given product.
//     *
//     * @param int $product_id product id
//     * @param mixed $object fields to create
//     * @return Object Object with `id`, `product_id`, `name` and `text` keys
//     */
//    public static function createProductMetaField($product_id, $object)
//    {
//        return self::createResource('/catalog/products/' . $product_id . '/metafields', $object);
//    }
//
//    /**
//     * Update the given meta field.
//     *
//     * @param int $product_id product id
//     * @param int $id meta field id
//     * @param mixed $object meta field to update
//     * @return mixed
//     */
//    public static function updateProductMetaField($product_id, $id, $object)
//    {
//        return self::updateResource('/catalog/products/' . $product_id . '/metafields/' . $id, $object);
//    }
//
//    /**
//     * Delete the given meta field.
//     *
//     * @param int $product_id product id
//     * @param int $id meta field id
//     * @return mixed
//     */
//    public static function deleteProductMetaField($product_id, $id)
//    {
//        return self::deleteResource('/catalog/products/' . $product_id . '/metafields/' . $id);
//    }
//
//    /**
//     * Gets collection of bulk pricing rules for a product.
//     *
//     * @param int $id product ID
//     * @return array|string list of products or XML string if useXml is true
//     */
//    public static function getProductBulkPricingRules($id)
//    {
//        return self::getCollection('/catalog/products/' . $id . '/bulk-pricing-rules', 'ProductBulkPricingRule');
//    }
//
//    /**
//     * Returns a single bulk pricing rule by given id
//     * @param  int $product_id product id
//     * @param  int $id bulk pricing rule id
//     * @return Resources\ProductBulkPricingRule|bool Returns ProductBulkPricingRule if exists, false if not exists
//     */
//    public static function getProductBulkPricingRule($product_id, $id)
//    {
//        return self::getResource('/catalog/products/' . $product_id . '/bulk-pricing-rules/' . $id, 'ProductBulkPricingRule');
//    }
//
//    /**
//     * Create a new bulk pricing rule for a given product.
//     *
//     * @param int $product_id product id
//     * @param mixed $object fields to create
//     * @return Object Object with `id`, `product_id`, `name` and `text` keys
//     */
//    public static function createProductBulkPricingRule($product_id, $object)
//    {
//        return self::createResource('/catalog/products/' . $product_id . '/bulk-pricing-rules', $object);
//    }
//
//    /**
//     * Update the given bulk pricing rule.
//     *
//     * @param int $product_id product id
//     * @param int $id bulk pricing rule id
//     * @param mixed $object bulk pricing rule to update
//     * @return mixed
//     */
//    public static function updateProductBulkPricingRule($product_id, $id, $object)
//    {
//        return self::updateResource('/catalog/products/' . $product_id . '/bulk-pricing-rules/' . $id, $object);
//    }
//
//    /**
//     * Delete the given bulk pricing rule.
//     *
//     * @param int $product_id product id
//     * @param int $id bulk pricing rule id
//     * @return mixed
//     */
//    public static function deleteProductBulkPricingRule($product_id, $id)
//    {
//        return self::deleteResource('/catalog/products/' . $product_id . '/bulk-pricing-rules/' . $id);
//    }
//
//    /**
//     * Gets collection of images for a product.
//     *
//     * @param int $id product ID
//     * @return array|string list of products or XML string if useXml is true
//     */
//    public static function getProductImages($id)
//    {
//        return self::getCollection('/catalog/products/' . $id . '/images', 'ProductImage');
//    }
//
//    /**
//     * Create a new product image.
//     *
//     * @param string $productId
//     * @param mixed $object
//     * @return mixed
//     */
//    public static function createProductImage($productId, $object)
//    {
//        return self::createResource('/catalog/products/' . $productId . '/images', $object);
//    }
//
//    /**
//     * Update a product image.
//     *
//     * @param string $productId
//     * @param string $imageId
//     * @param mixed $object
//     * @return mixed
//     */
//    public static function updateProductImage($productId, $imageId, $object)
//    {
//        return self::updateResource('/catalog/products/' . $productId . '/images/' . $imageId, $object);
//    }
//
//    /**
//     * Returns a product image resource by the given product id.
//     *
//     * @param int $productId
//     * @param int $imageId
//     * @return Resources\ProductImage|string
//     */
//    public static function getProductImage($productId, $imageId)
//    {
//        return self::getResource('/catalog/products/' . $productId . '/images/' . $imageId, 'ProductImage');
//    }
//
//    /**
//     * Delete the given product image.
//     *
//     * @param int $productId
//     * @param int $imageId
//     * @return mixed
//     */
//    public static function deleteProductImage($productId, $imageId)
//    {
//        return self::deleteResource('/catalog/products/' . $productId . '/images/' . $imageId);
//    }
//
//    /**
//     * Gets collection of variant options for a product.
//     *
//     * @param int $id product ID
//     * @return array|string list of products or XML string if useXml is true
//     */
//    public static function getProductVariantOptions($id)
//    {
//        return self::getCollection('/catalog/products/' . $id . '/options', 'ProductVariantOption');
//    }
//
//    /**
//     * Returns a single variant option by given id
//     * @param  int $product_id product id
//     * @param  int $id variant option id
//     * @return Resources\ProductVariantOption|bool Returns ProductVariantOption if exists, false if not exists
//     */
//    public static function getProductVariantOption($product_id, $id)
//    {
//        return self::getResource('/catalog/products/' . $product_id . '/options/' . $id, 'ProductVariantOption');
//    }
//
//    /**
//     * Create a new variant option for a given product.
//     *
//     * @param int $product_id product id
//     * @param mixed $object fields to create
//     * @return Object Object with `id`, `product_id`, `name` and `text` keys
//     */
//    public static function createProductVariantOption($product_id, $object)
//    {
//        return self::createResource('/catalog/products/' . $product_id . '/options', $object);
//    }
//
//    /**
//     * Update the given variant option.
//     *
//     * @param int $product_id product id
//     * @param int $id variant option id
//     * @param mixed $object variant option to update
//     * @return mixed
//     */
//    public static function updateProductVariantOption($product_id, $id, $object)
//    {
//        return self::updateResource('/catalog/products/' . $product_id . '/options/' . $id, $object);
//    }
//
//    /**
//     * Delete the given variant option.
//     *
//     * @param int $product_id product id
//     * @param int $id variant option id
//     * @return mixed
//     */
//    public static function deleteProductVariantOption($product_id, $id)
//    {
//        return self::deleteResource('/catalog/products/' . $product_id . '/options/' . $id);
//    }
//
//    /**
//     * Gets collection of variant option values for a product.
//     *
//     * @param int $product_id product id
//     * @param int $option_id option id
//     * @return array|string list of products or XML string if useXml is true
//     */
//    public static function getProductVariantOptionValues($option_id, $product_id)
//    {
//        return self::getCollection('/catalog/products/' . $product_id . '/options/' . $option_id . '/values', 'ProductVariantOptionValue');
//    }
//
//    /**
//     * Returns a single variant option value by given id
//     * @param int $option_id option id
//     * @param int $product_id product id
//     * @param  int $id variant option value id
//     * @return Resources\ProductVariantOptionValue|bool Returns ProductVariantOptionValue if exists, false if not exists
//     */
//    public static function getProductVariantOptionValue($option_id, $product_id, $id)
//    {
//        return self::getResource('/catalog/products/' . $product_id . '/options/' . $option_id . '/values/' . $id, 'ProductVariantOptionValue');
//    }
//
//    /**
//     * Create a new variant option value for a given product.
//     *
//     * @param int $option_id option id
//     * @param int $product_id product id
//     * @param mixed $object fields to create
//     * @return Object Object with `id`, `product_id`, `name` and `text` keys
//     */
//    public static function createProductVariantOptionValue($option_id, $product_id, $object)
//    {
//        return self::createResource('/catalog/products/' . $product_id . '/options/' . $option_id . '/values', $object);
//    }
//
//    /**
//     * Update the given variant option value.
//     *
//     * @param int $option_id option id
//     * @param int $product_id product id
//     * @param int $id variant option value id
//     * @param mixed $object variant option value to update
//     * @return mixed
//     */
//    public static function updateProductVariantOptionValue($option_id, $product_id, $id, $object)
//    {
//        return self::updateResource('/catalog/products/' . $product_id . '/options/' . $option_id . '/values/' . $id, $object);
//    }
//
//    /**
//     * Delete the given variant option value.
//     *
//     * @param int $option_id option id
//     * @param int $product_id product id
//     * @param int $id variant option value id
//     * @return mixed
//     */
//    public static function deleteProductVariantOptionValue($option_id, $product_id, $id)
//    {
//        return self::deleteResource('/catalog/products/' . $product_id . '/options/' . $option_id . '/values/' . $id);
//    }
//
//    /**
//     * Gets collection of variants for a product.
//     *
//     * @param int $id product ID
//     * @return array|string list of products or XML string if useXml is true
//     */
//    public static function getProductVariants($id)
//    {
//        return self::getCollection('/catalog/products/' . $id . '/variants', 'ProductVariant');
//    }
//
//    /**
//     * Returns a single variant by given id
//     * @param  int $product_id product id
//     * @param  int $id variant id
//     * @return Resources\ProductVariant|bool Returns ProductVariant if exists, false if not exists
//     */
//    public static function getProductVariant($product_id, $id)
//    {
//        return self::getResource('/catalog/products/' . $product_id . '/variants/' . $id, 'ProductVariant');
//    }
//
//    /**
//     * Create a new variant for a given product.
//     *
//     * @param int $product_id product id
//     * @param mixed $object fields to create
//     * @return Object Object with `id`, `product_id`, `name` and `text` keys
//     */
//    public static function createProductVariant($product_id, $object)
//    {
//        return self::createResource('/catalog/products/' . $product_id . '/variants', $object);
//    }
//
//    /**
//     * Update the given variant.
//     *
//     * @param int $product_id product id
//     * @param int $id variant id
//     * @param mixed $object variant to update
//     * @return mixed
//     */
//    public static function updateProductVariant($product_id, $id, $object)
//    {
//        return self::updateResource('/catalog/products/' . $product_id . '/variants/' . $id, $object);
//    }
//
//    /**
//     * Delete the given variant.
//     *
//     * @param int $product_id product id
//     * @param int $id variant id
//     * @return mixed
//     */
//    public static function deleteProductVariant($product_id, $id)
//    {
//        return self::deleteResource('/catalog/products/' . $product_id . '/variants/' . $id);
//    }
//
//    /**
//     * Gets collection of products for a product.
//     *
//     * @param int $id product ID
//     * @return array|string list of products or XML string if useXml is true
//     */
//    public static function getProductModifiers($id)
//    {
//        return self::getCollection('/catalog/products/' . $id . '/modifiers', 'ProductModifier');
//    }
//
//    /**
//     * Returns a single product by given id
//     * @param  int $product_id product id
//     * @param  int $id product id
//     * @return Resources\ProductModifier|bool Returns ProductModifier if exists, false if not exists
//     */
//    public static function getProductModifier($product_id, $id)
//    {
//        return self::getResource('/catalog/products/' . $product_id . '/modifiers/' . $id, 'ProductModifier');
//    }
//
//    /**
//     * Create a new product for a given product.
//     *
//     * @param int $product_id product id
//     * @param mixed $object fields to create
//     * @return Object Object with `id`, `product_id`, `name` and `text` keys
//     */
//    public static function createProductModifier($product_id, $object)
//    {
//        return self::createResource('/catalog/products/' . $product_id . '/modifiers', $object);
//    }
//
//    /**
//     * Update the given product.
//     *
//     * @param int $product_id product id
//     * @param int $id product id
//     * @param mixed $object product to update
//     * @return mixed
//     */
//    public static function updateProductModifier($product_id, $id, $object)
//    {
//        return self::updateResource('/catalog/products/' . $product_id . '/modifiers/' . $id, $object);
//    }
//
//    /**
//     * Delete the given product.
//     *
//     * @param int $product_id product id
//     * @param int $id product id
//     * @return mixed
//     */
//    public static function deleteProductModifier($product_id, $id)
//    {
//        return self::deleteResource('/catalog/products/' . $product_id . '/modifiers/' . $id);
//    }
//
//    /**
//     * Gets collection of modifier values for a product.
//     *
//     * @param int $product_id product id
//     * @param int $option_id option id
//     * @return array|string list of products or XML string if useXml is true
//     */
//    public static function getProductModifierValues($modifier_id, $product_id)
//    {
//        return self::getCollection('/catalog/products/' . $product_id . '/modifiers/' . $modifier_id . '/values', 'ProductModifierValue');
//    }
//
//    /**
//     * Returns a single modifier value by given id
//     * @param int $modifier_id option id
//     * @param int $product_id product id
//     * @param  int $id modifier value id
//     * @return Resources\ProductModifierValue|bool Returns ProductModifierValue if exists, false if not exists
//     */
//    public static function getProductModifierValue($modifier_id, $product_id, $id)
//    {
//        return self::getResource('/catalog/products/' . $product_id . '/modifiers/' . $modifier_id . '/values/' . $id, 'ProductModifierValue');
//    }
//
//    /**
//     * Create a new modifier value for a given product.
//     *
//     * @param int $modifier_id option id
//     * @param int $product_id product id
//     * @param mixed $object fields to create
//     * @return Object Object with `id`, `product_id`, `name` and `text` keys
//     */
//    public static function createProductModifierValue($modifier_id, $product_id, $object)
//    {
//        return self::createResource('/catalog/products/' . $product_id . '/modifiers/' . $modifier_id . '/values', $object);
//    }
//
//    /**
//     * Update the given modifier value.
//     *
//     * @param int $modifier_id option id
//     * @param int $product_id product id
//     * @param int $id modifier value id
//     * @param mixed $object modifier value to update
//     * @return mixed
//     */
//    public static function updateProductModifierValue($modifier_id, $product_id, $id, $object)
//    {
//        return self::updateResource('/catalog/products/' . $product_id . '/modifiers/' . $modifier_id . '/values/' . $id, $object);
//    }
//
//    /**
//     * Delete the given modifier value.
//     *
//     * @param int $modifier_id option id
//     * @param int $product_id product id
//     * @param int $id modifier value id
//     * @return mixed
//     */
//    public static function deleteProductModifierValue($modifier_id, $product_id, $id)
//    {
//        return self::deleteResource('/catalog/products/' . $product_id . '/modifiers/' . $modifier_id . '/values/' . $id);
//    }
//
//    /**
//     * Gets collection of complex rules for a product.
//     *
//     * @param int $id product ID
//     * @return array|string list of products or XML string if useXml is true
//     */
//    public static function getProductComplexRules($id)
//    {
//        return self::getCollection('/catalog/products/' . $id . '/complex-rules', 'ProductComplexRule');
//    }
//
//    /**
//     * Returns a single complex rule by given id
//     * @param  int $product_id product id
//     * @param  int $id complex rule id
//     * @return Resources\ProductComplexRule|bool Returns ProductComplexRule if exists, false if not exists
//     */
//    public static function getProductComplexRule($product_id, $id)
//    {
//        return self::getResource('/catalog/products/' . $product_id . '/complex-rules/' . $id, 'ProductComplexRule');
//    }
//
//    /**
//     * Create a new complex rule for a given product.
//     *
//     * @param int $product_id product id
//     * @param mixed $object fields to create
//     * @return Object Object with `id`, `product_id`, `name` and `text` keys
//     */
//    public static function createProductComplexRule($product_id, $object)
//    {
//        return self::createResource('/catalog/products/' . $product_id . '/complex-rules', $object);
//    }
//
//    /**
//     * Update the given complex rule.
//     *
//     * @param int $product_id product id
//     * @param int $id complex rule id
//     * @param mixed $object complex rule to update
//     * @return mixed
//     */
//    public static function updateProductComplexRule($product_id, $id, $object)
//    {
//        return self::updateResource('/catalog/products/' . $product_id . '/complex-rules/' . $id, $object);
//    }
//
//    /**
//     * Delete the given complex rule.
//     *
//     * @param int $product_id product id
//     * @param int $id complex rule id
//     * @return mixed
//     */
//    public static function deleteProductComplexRule($product_id, $id)
//    {
//        return self::deleteResource('/catalog/products/' . $product_id . '/complex-rules/' . $id);
//    }
//
//    /**
//     * Gets collection of videos for a product.
//     *
//     * @param int $id product ID
//     * @return array|string list of products or XML string if useXml is true
//     */
//    public static function getProductVideos($id)
//    {
//        return self::getCollection('/catalog/products/' . $id . '/videos', 'ProductVideo');
//    }
//
//    /**
//     * Returns a single video by given id
//     * @param  int $product_id product id
//     * @param  int $id video id
//     * @return Resources\ProductVideo|bool Returns ProductVideo if exists, false if not exists
//     */
//    public static function getProductVideo($product_id, $id)
//    {
//        return self::getResource('/catalog/products/' . $product_id . '/videos/' . $id, 'ProductVideo');
//    }
//
//    /**
//     * Create a new video for a given product.
//     *
//     * @param int $product_id product id
//     * @param mixed $object fields to create
//     * @return Object Object with `id`, `product_id`, `name` and `text` keys
//     */
//    public static function createProductVideo($product_id, $object)
//    {
//        return self::createResource('/catalog/products/' . $product_id . '/videos', $object);
//    }
//
//    /**
//     * Update the given video.
//     *
//     * @param int $product_id product id
//     * @param int $id video id
//     * @param mixed $object video to update
//     * @return mixed
//     */
//    public static function updateProductVideo($product_id, $id, $object)
//    {
//        return self::updateResource('/catalog/products/' . $product_id . '/videos/' . $id, $object);
//    }
//
//    /**
//     * Delete the given video.
//     *
//     * @param int $product_id product id
//     * @param int $id video id
//     * @return mixed
//     */
//    public static function deleteProductVideo($product_id, $id)
//    {
//        return self::deleteResource('/catalog/products/' . $product_id . '/videos/' . $id);
//    }
//
//    /**
//     * Gets collection of reviews for a product.
//     *
//     * @param int $id product ID
//     * @return array|string list of products or XML string if useXml is true
//     */
//    public static function getProductReviews($id)
//    {
//        return self::getCollection('/catalog/products/' . $id . '/reviews', 'ProductReview');
//    }
//
//    /**
//     * Returns a single review by given id
//     * @param  int $product_id product id
//     * @param  int $id review id
//     * @return Resources\ProductReview|bool Returns ProductReview if exists, false if not exists
//     */
//    public static function getProductReview($product_id, $id)
//    {
//        return self::getResource('/catalog/products/' . $product_id . '/reviews/' . $id, 'ProductReview');
//    }
//
//    /**
//     * Create a new review for a given product.
//     *
//     * @param int $product_id product id
//     * @param mixed $object fields to create
//     * @return Object Object with `id`, `product_id`, `name` and `text` keys
//     */
//    public static function createProductReview($product_id, $object)
//    {
//        return self::createResource('/catalog/products/' . $product_id . '/reviews', $object);
//    }
//
//    /**
//     * Update the given review.
//     *
//     * @param int $product_id product id
//     * @param int $id review id
//     * @param mixed $object review to update
//     * @return mixed
//     */
//    public static function updateProductReview($product_id, $id, $object)
//    {
//        return self::updateResource('/catalog/products/' . $product_id . '/reviews/' . $id, $object);
//    }
//
//    /**
//     * Delete the given review.
//     *
//     * @param int $product_id product id
//     * @param int $id review id
//     * @return mixed
//     */
//    public static function deleteProductReview($product_id, $id)
//    {
//        return self::deleteResource('/catalog/products/' . $product_id . '/reviews/' . $id);
//    }
//
//    /**
//     * The collection of categories.
//     *
//     * @param array $filter
//     * @return array
//     */
//    public static function getBrands($filter = array())
//    {
//        $filter = Filter::create($filter);
//        return self::getCollection('/catalog/brands' . $filter->toQuery(), 'Brand');
//    }
//
//    /**
//     * The number of categories in the collection.
//     *
//     * @param array $filter
//     * @return int
//     */
//    public static function getBrandsCount($filter = array())
//    {
//        $filter = Filter::create($filter);
//        return self::getCount('/catalog/brands' . $filter->toQuery());
//    }
//
//    /**
//     * A single brand by given id.
//     *
//     * @param int $id brand id
//     * @return Resources\Brand
//     */
//    public static function getBrand($id)
//    {
//        return self::getResource('/catalog/brands/' . $id, 'Brand');
//    }
//
//    /**
//     * Create a new brand from the given data.
//     *
//     * @param mixed $object
//     * @return mixed
//     */
//    public static function createBrand($object)
//    {
//        return self::createResource('/catalog/brands', $object);
//    }
//
//    /**
//     * Update the given brand.
//     *
//     * @param int $id brand id
//     * @param mixed $object
//     * @return mixed
//     */
//    public static function updateBrand($id, $object)
//    {
//        return self::updateResource('/catalog/categories/' . $id, $object);
//    }
//
//    /**
//     * Delete the given brand.
//     *
//     * @param int $id brand id
//     * @return mixed
//     */
//    public static function deleteBrand($id)
//    {
//        return self::deleteResource('/catalog/brands' . $id);
//    }
//
//    /**
//     * Delete all categories.
//     *
//     * @return mixed
//     */
//    public static function deleteAllBrands()
//    {
//        return self::deleteResource('/catalog/brands');
//    }
}
