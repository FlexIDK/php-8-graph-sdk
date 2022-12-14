<?php

namespace One23\GraphSdk\GraphNodes;

class GraphLocation extends GraphNode
{
    /**
     * Returns the street component of the location
     */
    public function getStreet(): ?string
    {
        return self::mapType(
            $this->getField('street'),
            'str'
        );
    }

    /**
     * Returns the city component of the location
     */
    public function getCity(): ?string
    {
        return self::mapType(
            $this->getField('city'),
            'str'
        );
    }

    /**
     * Returns the state component of the location
     */
    public function getState(): ?string
    {
        return self::mapType(
            $this->getField('state'),
            'str'
        );
    }

    /**
     * Returns the country component of the location
     */
    public function getCountry(): ?string
    {
        return self::mapType(
            $this->getField('country'),
            'str'
        );
    }

    /**
     * Returns the zipcode component of the location
     */
    public function getZip(): ?string
    {
        return self::mapType(
            $this->getField('zip'),
            'str'
        );
    }

    /**
     * Returns the latitude component of the location
     */
    public function getLatitude(): ?float
    {
        return self::mapType(
            $this->getField('latitude'),
            'float'
        );
    }

    /**
     * Returns the street component of the location
     */
    public function getLongitude(): ?float
    {
        return self::mapType(
            $this->getField('longitude'),
            'float'
        );
    }
}
