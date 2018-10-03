<?php

namespace d0x2f\CloverMerge;

/**
 * Represents a files coverage information.
 */
class File
{
    /**
     * The classes in this file indexed by name.
     *
     * @var \Ds\Map
     */
    private $classes;

    /**
     * The lines in this file indexed by line number.
     *
     * @var \Ds\Map
     */
    private $lines;

    /**
     * Package name this file belongs to.
     *
     * @var string|null
     */
    private $package_name;

    /**
     * Metrics element.
     *
     * @var Metrics|null
     */
    private $metrics;

    /**
     * Construct with the given package name.
     *
     * @param string|null $package_name
     */
    public function __construct(?string $package_name = null)
    {
        $this->classes = new \Ds\Map();
        $this->lines = new \Ds\Map();
        $this->package_name = $package_name;
        $this->metrics = null;
    }

    /**
     * Construct from XML.
     *
     * @param \SimpleXMLElement $xml
     * @param string|null $package_name
     * @return File
     */
    public static function fromXML(\SimpleXMLElement $xml, ?string $package_name = null) : File
    {
        $file = new File($package_name);
        $children = $xml->children();
        foreach ($children as $child) {
            self::parseChildXml($file, $child);
        }
        return $file;
    }

    /**
     * Parse a child element into an appropriate class.
     *
     * @param File $file
     * @param \SimpleXMLElement $child
     * @return void
     */
    private static function parseChildXML(File &$file, \SimpleXMLElement $child)
    {
        $name = $child->getName();
        $attributes = iterator_to_array($child->attributes());
        if ($name === 'class') {
            if (!array_key_exists('name', $attributes)) {
                Utilities::logWarning('Ignoring class with no name.');
                return;
            }
            $file->mergeClass($attributes['name'], ClassT::fromXml($child));
        } elseif ($name === 'metrics') {
            $file->mergeMetrics(Metrics::fromXml($child));
        } elseif ($name === 'line') {
            if (!array_key_exists('num', $attributes) ||
                !array_key_exists('count', $attributes)
            ) {
                Utilities::logWarning('Ignoring line with no num or count.');
                return;
            }
            $file->mergeLine((int)$attributes['num'], Line::fromXml($child));
        } else {
            Utilities::logWarning("Ignoring unknown element: {$name}.");
        }
    }

    /**
     * Merge another file with this one.
     *
     * @param File $other
     * @param string $merge_mode inclusive, exclusive or additive
     * @param bool $lock_lines Don't add new lines when true.
     * @return void
     */
    public function merge(File $other, string $merge_mode = 'inclusive', bool $lock_lines = false) : void
    {
        $this->classes = $other->getClasses()->merge($this->classes);
        $this->package_name = $this->package_name ?? $other->package_name;
        $this->metrics = $this->metrics ?? $other->metrics;

        $other_lines = $other->getLines();

        if ($merge_mode === 'exclusive') {
            $this->lines = $this->lines->intersect($other_lines);
            $other_lines = $other_lines->intersect($this->lines);
        }

        foreach ($other_lines as $number => $line) {
            $this->mergeLine($number, $line, $lock_lines);
        }
    }

    /**
     * Add or merge class.
     *
     * @param string $name
     * @param ClassT $class
     * @return void
     */
    public function mergeClass(string $name, ClassT $class) : void
    {
        if (!$this->classes->hasKey($name)) {
            $this->classes->put($name, $class);
        }
    }

    /**
     * Add or merge a line to this file.
     *
     * @param integer $number
     * @param Line $line
     * @param bool $lock_lines Don't add new lines when true.
     * @return void
     */
    public function mergeLine(int $number, Line $line, bool $lock_lines = false) : void
    {
        if ($this->lines->hasKey($number)) {
            $this->lines->get($number)->merge($line);
        } elseif (!$lock_lines) {
            $this->lines->put($number, $line);
        }
    }

    /**
     * Set metrics if not already set.
     *
     * @param Metrics $metrics
     * @return void
     */
    public function mergeMetrics(Metrics $metrics)
    {
        $this->metrics = $this->metrics ?? $metrics;
    }

    /**
     * Get the classes.
     *
     * @return \Ds\Map
     */
    public function getClasses() : \Ds\Map
    {
        return $this->classes;
    }

    /**
     * Get the lines.
     *
     * @return \Ds\Map
     */
    public function getLines() : \Ds\Map
    {
        return $this->lines;
    }

    /**
     * Get the package name.
     *
     * @return string|null
     */
    public function getPackageName() : ?string
    {
        return $this->package_name;
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
