<?php

namespace Bigcommerce\Api\v2;

use Bigcommerce\Api\Error;

class Resource extends \Bigcommerce\Api\Resource
{
    /**
     * @var string
     */
    public $url;
    /**
     * @var array
     */
    public $urlParams = [];
    /**
     * @var string
     */
    public $pluralOperationName = '';
    /**
     * @var \stdClass
     */
    protected $fields;
    /**
     * @var int
     */
    protected $id;
    /**
     * @var array
     */
    protected $ignoreOnCreate = [];
    /**
     * @var array
     */
    protected $ignoreOnUpdate = [];
    /**
     * @var array
     */
    protected $ignoreIfZero = [];
    /**
     * @var array
     */
    protected $fieldMap = [];
    
    /**
     * @param $field
     * @return null
     */
    public function __get($field)
    {
        // first, find the field we should actually be examining
        $fieldName = isset($this->fieldMap[$field]) ? $this->fieldMap[$field] : $field;
        // then, if a method exists for the specified field and the field we should actually be examining
        // has a value, call the method instead
        if (method_exists($this, $field) && isset($this->fields->$fieldName)) {
            return $this->$field();
        }
        // otherwise, just return the field directly (or null)
        return (isset($this->fields->$field)) ? $this->fields->$field : null;
    }
    
    /**
     * @param $field
     * @param $value
     */
    public function __set($field, $value)
    {
        $this->fields->$field = $value;
    }
    
    /**
     * @param $field
     * @return bool
     */
    public function __isset($field)
    {
        return (isset($this->fields->$field));
    }
    
    /**
     * @return bool|mixed|\stdClass
     */
    public function getCreateFields()
    {
        $resource = clone $this->fields;
        
        foreach ($this->ignoreOnCreate as $field) {
            unset($resource->$field);
        }
        
        return $resource;
    }
    
    /**
     * @return bool|mixed|\stdClass
     */
    public function getUpdateFields()
    {
        $resource = clone $this->fields;
        
        foreach ($this->ignoreOnUpdate as $field) {
            unset($resource->$field);
        }
        
        foreach ($resource as $field => $value) {
            if ($this->isIgnoredField($field, $value)) {
                unset($resource->$field);
            }
        }
        
        return $resource;
    }
    
    /**
     * @param $field
     * @param $value
     * @return bool
     */
    private function isIgnoredField($field, $value)
    {
        if ($value === null) {
            return true;
        }
        
        if ($value === "" && strpos($field, "date") !== false) {
            return true;
        }
        
        if ($value === 0 && in_array($field, $this->ignoreIfZero, true)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @return mixed
     */
    public function getUrl()
    {
        $url = $this->url;
        foreach ($this->urlParams as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }
        return $url;
    }
    
    /**
     * The collection of resources.
     *
     * @param array $filter
     * @throws Error
     * @throws \ReflectionException
     * @return array
     */
    public function all($filter = [])
    {
        if (isset($this->urlParams['id'])) {
            $filter = $this->urlParams['id'];
        }
        $method = 'get' . self::getResourceName(true);
        if (!Client::classMethodExists($method)) {
            throw new Error('Client method with name ' . $method . ' not found', 404);
        }
        return Client::{$method}($filter);
    }
    
    /**
     * @param bool $plural
     * @return string
     * @throws \ReflectionException
     */
    public function getResourceName($plural = false)
    {
        $reflection = (new \ReflectionClass($this));
        if ($plural) {
            return $reflection->getProperty('pluralOperationName')->getValue($this) ? $reflection->getProperty('pluralOperationName')->getValue($this) : $reflection->getShortName() . 's';
        } else {
            return $reflection->getShortName();
        }
    }
    
    /**
     * The number of resources in the collection.
     *
     * @param array $filter
     * @throws Error
     * @throws \ReflectionException
     * @return int
     */
    public function count($filter = [])
    {
        $filter = array_merge_recursive($filter, $this->urlParams);
        $method = 'get' . self::getResourceName(true) . 'Count';
        if (!Client::classMethodExists($method)) {
            throw new Error('Client method with name ' . $method . ' not found', 404);
        }
        
        return Client::{$method}($filter);
    }
    
    /**
     * A single resource by given id.
     *
     * @param int $id resource id
     * @throws Error
     * @throws \ReflectionException
     * @return \Bigcommerce\Api\Resource
     */
    public function get($id)
    {
        $method = 'get' . self::getResourceName();
        if (!Client::classMethodExists($method)) {
            throw new Error('Client method with name ' . $method . ' not found', 404);
        }
        return Client::{$method}($id);
    }
    
    /**
     * Create a new resource from the given data.
     *
     * @param mixed $object
     * @throws Error
     * @throws \ReflectionException
     * @return mixed
     */
    public function create($object)
    {
        $method = 'create' . self::getResourceName();
        if (!Client::classMethodExists($method)) {
            throw new Error('Client method with name ' . $method . ' not found', 404);
        }
        return Client::{$method}($object);
    }
    
    /**
     * Update the given resource.
     *
     * @param int $id resource id
     * @param mixed $object
     * @throws Error
     * @throws \ReflectionException
     * @return mixed
     */
    public function update($id, $object)
    {
        $method = 'update' . self::getResourceName();
        if (!Client::classMethodExists($method)) {
            throw new Error('Client method with name ' . $method . ' not found', 404);
        }
        return Client::{$method}($id, $object);
    }
    
    /**
     * Delete the given resource.
     *
     * @param int $id resource id
     * @throws Error
     * @throws \ReflectionException
     * @return mixed
     */
    public function delete($id)
    {
        $method = 'delete' . self::getResourceName();
        if (!Client::classMethodExists($method)) {
            throw new Error('Client method with name ' . $method . ' not found', 404);
        }
        return Client::{$method}($id);
    }
    
    /**
     * Delete all resources.
     *
     * @throws Error
     * @throws \ReflectionException
     * @return mixed
     */
    public function deleteAll()
    {
        $method = 'deleteAll' . self::getResourceName(true);
        if (!Client::classMethodExists($method)) {
            throw new Error('Client method with name ' . $method . ' not found', 404);
        }
        return Client::{$method}();
    }
}
