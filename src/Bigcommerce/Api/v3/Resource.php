<?php

namespace Bigcommerce\Api\v3;

use Bigcommerce\Api\Filter;

class Resource extends \Bigcommerce\Api\Resource
{
    /**
     * @var
     */
    public $url;
    
    /**
     * @var array of url params
     */
    public $urlParams = [];
    
    /**
     * @var string parent field name
     */
    public $parentField;
    
    /**
     * {@inheritdoc}
     */
    public function __construct($object = false)
    {
        parent::__construct($object);
    
        /*
        if (typeof($object) == '') {
            if ($this->parentField) {
                $object->__set($this->parentField, $object->{$this->parentField});
            }
            if ($this->additionalParentField) {
                $object->__set($this->additionalParentField, $object->{$this->additionalParentField});
            }
        }
        */
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
        $result = Client::getCollection(self::getUrl() . $filter->toQuery(), self::getResourceName());
        return $result;
    }
    
    /**
     * API client connection headers
     *
     * @return array
     */
    public function getClientConnectionHeaders()
    {
        return Client::getConnection()->getHeaders();
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
     * @param array #urlParams
     * @return int
     */
    public function count($filter = [], $urlParams = [])
    {
        $this->urlParams = ($urlParams) ? $urlParams : [];
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
     * @param array #urlParams
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
     * @param array #urlParams
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
