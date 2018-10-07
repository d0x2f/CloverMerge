<?php

namespace d0x2f\CloverMerge;

/**
 * Represents a set of metrics.
 */
class Metrics
{
    /**
     * Properties.
     *
     * @var \Ds\Map
     */
    private $properties;

    /**
     * Constructor.
     *
     * @param \Ds\Map $properties
     */
    public function __construct(\Ds\Map $properties)
    {
        $this->properties = $properties;
    }

    /**
     * Construct from XML.
     *
     * @param \SimpleXMLElement $xml
     * @return Metrics
     */
    public static function fromXML(\SimpleXMLElement $xml) : Metrics
    {
        $properties = new \Ds\Map($xml->attributes());
        $properties->apply(function ($key, $value) {
            return (string) $value;
        });
        return new Metrics($properties);
    }

    /**
     * Get properties.
     *
     * @return \Ds\Map
     */
    public function getProperties() : \Ds\Map
    {
        return $this->properties;
    }
}
