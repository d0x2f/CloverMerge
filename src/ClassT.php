<?php

namespace d0x2f\CloverMerge;

/**
 * Represents a class.
 */
class ClassT
{
    /**
     * Class namespace.
     *
     * @var string|null
     */
    private $namespace;

    /**
     * Class metrics
     *
     * @var Metrics|null
     */
    private $metrics;

    /**
     * Constructor.
     *
     * @param string|null $namespace
     * @param Metrics|null $metrics
     */
    public function __construct(?string $namespace = null, ?Metrics $metrics = null)
    {
        $this->namespace = $namespace;
        $this->metrics = $metrics;
    }

    /**
     * Construct from XML.
     *
     * @param \SimpleXMLElement $xml
     * @return ClassT
     */
    public static function fromXML(\SimpleXMLElement $xml) : ClassT
    {
        $attributes = iterator_to_array($xml->attributes());
        $class = new ClassT($attributes['namespace'] ?? null);
        $children = $xml->children();
        foreach ($children as $child) {
            $name = $child->getName();
            if ($name === 'metrics') {
                $class->mergeMetrics(Metrics::fromXml($child));
            } else {
                Utilities::logWarning("Ignoring unknown element: {$name}");
            }
        }
        return $class;
    }

    /**
     * Set metrics.
     *
     * @param Metrics $metrics
     * @return void
     */
    public function mergeMetrics(Metrics $metrics) : void
    {
        $this->metrics = $this->metrics ?? $metrics;
    }

    /**
     * Get the namespace.
     *
     * @return string|null
     */
    public function getNamespace() : ?string
    {
        return $this->namespace;
    }

    /**
     * Get the metrics.
     *
     * @return Metrics|null
     */
    public function getMetrics() : ?Metrics
    {
        return $this->metrics;
    }
}
