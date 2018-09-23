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
        return new Metrics(new \Ds\Map($xml->attributes()));
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

    /**
     * Set properties.
     *
     * @param \Ds\Map $properties
     * @return void
     */
    public function setProperties(\Ds\Map $properties) : void
    {
        $this->properties = $properties;
    }
}
