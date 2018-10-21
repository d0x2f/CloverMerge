<?php

namespace d0x2f\CloverMerge;

/**
 * Represents a class.
 */
class ClassT
{

    /**
     * Other properties on the line.
     * E.g. name, visibility, complexity, crap.
     *
     * @var \Ds\Map $properties
     */
    private $properties;

    /**
     * Constructor.
     *
     * @param \Ds\Map $properties Any properties on the XML node.
     */
    public function __construct(\Ds\Map $properties)
    {
        $this->properties = $properties;
    }

    /**
     * Construct from XML.
     *
     * @param \SimpleXMLElement $xml
     * @return ClassT
     */
    public static function fromXML(\SimpleXMLElement $xml) : ClassT
    {
        $properties = new \Ds\Map($xml->attributes());
        $properties->apply(function ($_, $value) {
            return (string) $value;
        });
        return new ClassT($properties);
    }

    /**
     * Produce an XML representation.
     *
     * @param \DomDocument $document The parent document.
     * @return \DOMElement
     */
    public function toXml(
        \DomDocument $document
    ) : \DOMElement {
        $xml_class = $document->createElement('class');
        foreach ($this->properties as $key => $value) {
            $xml_class->setAttribute($key, $value);
        }
        return $xml_class;
    }

    /**
     * Get the properties.
     *
     * @return \Ds\Map
     */
    public function getProperties() : \Ds\Map
    {
        return $this->properties;
    }
}
