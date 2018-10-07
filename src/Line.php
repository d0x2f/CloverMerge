<?php

namespace d0x2f\CloverMerge;

/**
 * Represents a single lines coverage information.
 */
class Line
{
    /**
     * Number of hits on this line.
     *
     * @var int
     */
    private $count;

    /**
     * Other properties on the line.
     * E.g. name, visibility, complexity, crap.
     *
     * @var \Ds\Map $properties
     */
    private $properties;

    /**
     * Initialise with a hit count.
     *
     * @param integer $count
     */
    public function __construct(\Ds\Map $properties, int $count = 0)
    {
        $this->count = $count;
        $this->properties = $properties;
    }

    /**
     * Construct from XML.
     *
     * @param \SimpleXMLElement $xml
     * @return Line
     * @throws ParseException
     */
    public static function fromXML(\SimpleXMLElement $xml) : Line
    {
        $properties = new \Ds\Map($xml->attributes());
        $properties->apply(function ($_, $value) {
            return (string) $value;
        });
        if (!$properties->hasKey('count')) {
            throw new ParseException('Unable to parse line, missing count attribute.');
        }
        return new Line($properties, (int)$properties->remove('count'));
    }

    /**
     * Merge another line with this one.
     *
     * @param Line $other
     * @return void
     */
    public function merge($other) : void
    {
        // Merge in this order so that the fist set overrides the second.
        $this->properties = $other->getProperties()->merge($this->properties);
        $this->count += $other->getCount();
    }

    /**
     * Get the hit count.
     *
     * @return integer
     */
    public function getCount() : int
    {
        return $this->count;
    }

    /**
     * Get the other properties.
     *
     * @return \Ds\Map
     */
    public function getProperties() : \Ds\Map
    {
        return $this->properties;
    }
}
