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
     * Construct with the given package name.
     *
     * @param string|null $package_name
     */
    public function __construct(?string $package_name = null)
    {
        $this->classes = new \Ds\Map();
        $this->lines = new \Ds\Map();
        $this->package_name = $package_name;
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
        $attributes = $child->attributes();
        if ($name === 'class') {
            if (!Utilities::xmlHasAttributes($child, ['name'])) {
                Utilities::logWarning('Ignoring class with no name.');
                return;
            }
            $file->mergeClass($attributes['name'] ?? '', ClassT::fromXml($child));
        } elseif ($name === 'metrics') {
            // Ignore input metrics, we'll compute our own.
        } elseif ($name === 'line') {
            if (!Utilities::xmlHasAttributes($child, ['num', 'count'])) {
                Utilities::logWarning('Ignoring line with no num or count.');
                return;
            }
            $file->mergeLine((int)$attributes['num'], Line::fromXml($child));
        } else {
            Utilities::logWarning("Ignoring unknown element: {$name}.");
        }
    }

    /**
     * Produce an XML element representing this file.
     *
     * @param \DomDocument $xml_document The parent document.
     * @param string $name The name of the file.
     * @return array{0:\DOMElement,1:Metrics}
     */
    public function toXml(
        \DomDocument $xml_document,
        string $name
    ) : array {
        $xml_file = $xml_document->createElement('file');
        $xml_file->setAttribute('name', $name);

        // Metric counts
        $statement_count = 0;
        $covered_statement_count = 0;
        $conditional_count = 0;
        $covered_conditional_count = 0;
        $method_count = 0;
        $covered_method_count = 0;
        $class_count = $this->classes->count();

        // Classes
        foreach ($this->classes as $class) {
            $xml_file->appendChild($class->toXml($xml_document));
        }

        // Lines
        foreach ($this->lines as $line) {
            $xml_file->appendChild($line->toXml($xml_document));
            $properties = $line->getProperties();

            $covered = $line->getCount() > 0;
            $type = $properties['type'] ?? 'stmt';

            if ($type === 'method') {
                $method_count ++;
                if ($covered) {
                    $covered_method_count ++;
                }
            } elseif ($type === 'stmt') {
                $statement_count ++;
                if ($covered) {
                    $covered_statement_count ++;
                }
            } elseif ($type === 'cond') {
                $conditional_count ++;
                if ($covered) {
                    $covered_conditional_count ++;
                }
            } else {
                Utilities::logWarning("Ignoring unexpected line type: {$type}.");
            }
        }

        // Metrics
        $metrics = new Metrics(
            $statement_count,
            $covered_statement_count,
            $conditional_count,
            $covered_conditional_count,
            $method_count,
            $covered_method_count,
            $class_count,
            1
        );
        $xml_file->appendChild($metrics->toFileXml($xml_document));

        // Return a tuple of the XML node and the metrics to carry over.
        return [$xml_file, $metrics];
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
}
