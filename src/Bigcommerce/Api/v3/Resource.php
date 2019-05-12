<?php

namespace Bigcommerce\Api\v3;

class Resource
{
    /**
     * @var
     */
    public $url;
    /**
     * @var
     */
    public $urlParams = [];
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
     * Resource constructor.
     * @param bool $object
     */
    public function __construct($object = false)
    {
        if (is_array($object)) {
            $object = (isset($object[0])) ? $object[0] : false;
        }
        $this->fields = ($object) ? $object : new \stdClass;
        $this->id = ($object && isset($object->id)) ? $object->id : 0;
        
        if ($object) {
            if ($this->parentField) {
                $object->__set($this->parentField, $object->{$this->parentField});
            }
            if ($this->additionalParentField) {
                $object->__set($this->additionalParentField, $object->{$this->additionalParentField});
            }
        }
        
    }
    
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
     * The collection of resources.
     *
     * @param array $filter
     * @return array
     */
    public function all($filter = [])
    {
        $filter = Filter::create($filter);
        return Client::getCollection(self::getUrl() . $filter->toQuery(), self::getResourceName());
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
    
    public function getResourceName()
    {
        return (new \ReflectionClass($this))->getShortName();
    }
    
    /**
     * The number of resources in the collection.
     *
     * @param array $filter
     * @return int
     */
    public function count($filter = [])
    {
        $filter = Filter::create($filter);
        return Client::getCount(self::getUrl() . $filter->toQuery());
    }
    
    /**
     * A single resource by given id.
     *
     * @param int $id resource id
     * @return Resources\Category
     */
    public function get($id)
    {
        return Client::getResource(self::getUrl() . '/' . $id, self::getResourceName());
    }
    
    /**
     * Create a new resource from the given data.
     *
     * @param mixed $object
     * @return mixed
     */
    public function create($object, $urlParams = [])
    {
        $this->urlParams = ($urlParams) ? $urlParams : [];
        return Client::createResource(self::getUrl(), $object, self::getResourceName());
    }
    
    /**
     * Update the given resource.
     *
     * @param int $id resource id
     * @param mixed $object
     * @return mixed
     */
    public function update($id, $object, $urlParams = [])
    {
        $this->urlParams = ($urlParams) ? $urlParams : [];
        return Client::updateResource(self::getUrl() . '/' . $id, $object, self::getResourceName());
    }
    
    /**
     * Delete the given resource.
     *
     * @param int $id resource id
     * @return mixed
     */
    public function delete($id)
    {
        return Client::deleteResource(self::getUrl() . '/' . $id);
    }
    
    /**
     * Delete all resources.
     *
     * @return mixed
     */
    public function deleteAll()
    {
        return Client::deleteResource(self::getUrl());
    }
}
