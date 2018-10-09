<?php

namespace d0x2f\CloverMerge;

/**
 * Provides a parsing context and methods to build merged coverage state.
 */
class Accumulator
{
    /**
     * inclusive, exclusive or additive.
     *
     * @var string
     */
    private $merge_mode;

    /**
     * When true, no new lines will be added.
     *
     * @var boolean
     */
    private $lock_lines = false;

    /**
     * Accumulated files.
     *
     * @var \Ds\Map
     */
    private $files;

    /**
     * Document metrics.
     *
     * @var ?Metrics
     */
    private $metrics = null;

    /**
     * If this accumulator is empty.
     *
     * @var bool
     */
    private $empty = true;

    /**
     * Constructor
     *
     * @param string $merge_mode inclusive, exclusive or additive.
     * @return void
     */
    public function __construct(string $merge_mode)
    {
        $this->merge_mode = $merge_mode;
        $this->files = new \Ds\Map();
    }

    /**
     * Get the accumulated files.
     *
     * @return \Ds\Map
     */
    public function getFiles() : \Ds\Map
    {
        return $this->files;
    }

    /**
     * Get the top level metrics object.
     *
     * @return Metrics|null
     */
    public function getMetrics() : ?Metrics
    {
        return $this->metrics;
    }

    /**
     * Get the number of files discovered.
     *
     * @return integer
     */
    public function getFileCount() : int
    {
        return $this->files->count();
    }

    /**
     * Sum coverage information.
     *
     * @return array{0:int,1:int}
     */
    public function getCoverage() : array
    {
        return $this->files->reduce(
            function (array $carry, string $_, File $file) {
                [$covered, $total] = $file->getCoverage();
                $carry[0] += $covered;
                $carry[1] += $total;
                return $carry;
            },
            [0, 0]
        );
    }

    /**
     * Parse each document in the given collection.
     *
     * @param \Traversable $documents
     * @return void
     */
    public function parseAll(\Traversable $documents) : void
    {
        foreach ($documents as $document) {
            $this->parseXml($document);
        }
    }

    /**
     * Parse the given document.
     *
     * @param \SimpleXMLElement $document
     * @return void
     */
    public function parseXml(\SimpleXMLElement $document) : void
    {
        $preexisting_files = $this->files->keys();

        $name = $document->getName();
        if ($name !== 'coverage') {
            Utilities::logWarning("Ignoring unexpected element: {$name}.");
            return;
        }

        foreach ($document->children() as $project) {
            $this->parseProject($project);
        }

        // Prevent subsequent documents from adding new lines.
        if ($this->merge_mode === 'additive') {
            $this->lock_lines = true;
        } elseif ($this->merge_mode === 'exclusive' && !$this->empty) {
            $this->files = $this->files->filter(function ($key) use ($preexisting_files) {
                return $preexisting_files->contains($key);
            });
        }

        $this->empty = false;
    }

    /**
     * Parse a project element.
     *
     * @param \SimpleXMLElement $project
     * @return void
     */
    private function parseProject(\SimpleXMLElement $project) : void
    {
        $name = $project->getName();
        if ($name !== 'project') {
            Utilities::logWarning("Ignoring unexpected element: {$name}.");
            return;
        }
        $this->parseItems($project->children());
    }

    /**
     * Parse a set of items.
     *
     * @param \SimpleXMLElement $items
     * @param string|null $package_name
     * @return void
     */
    private function parseItems(\SimpleXMLElement $items, ?string $package_name = null) : void
    {
        foreach ($items as $item) {
            $this->parseItem($item, $package_name);
        }
    }

    /**
     * Parse an item.
     *
     * @param \SimpleXMLElement $element
     * @param string|null $package_name The package name new files should be given.
     * @return void
     */
    private function parseItem(
        \SimpleXMLElement $element,
        ?string $package_name = null
    ) : void {
        $name = $element->getName();
        if ($name === 'package') {
            $attributes = $element->attributes();
            // Don't return here so that the package's files are still parsed regardless.
            if (!Utilities::xmlHasAttributes($element, ['name'])) {
                Utilities::logWarning('Ignoring package with no name.');
            }
            $this->parseItems($element->children(), $attributes['name'] ?? null);
        } elseif ($name === 'file') {
            $this->parseFile($element, $package_name);
        } elseif ($name === 'metrics') {
            $metrics = Metrics::fromXml($element);
            if (is_null($this->metrics)) {
                $this->metrics = $metrics;
            }
        } else {
            Utilities::logWarning("Ignoring unexpected element: {$name}.");
        }
    }

    /**
     * Parse a file xml element.
     *
     * @param \SimpleXMLElement $element
     * @param string|null $package_name
     * @return void
     */
    private function parseFile(
        \SimpleXMLElement $element,
        ?string $package_name = null
    ) : void {
        $attributes = $element->attributes();
        if (!Utilities::xmlHasAttributes($element, ['name'])) {
            Utilities::logWarning('Ignoring file with no name.');
            return;
        }
        $file_name = (string)$attributes['name'];
        $file = File::fromXml($element, $package_name);
        if ($this->files->hasKey($file_name)) {
            $this->files->get($file_name)->merge($file, $this->merge_mode, $this->lock_lines);
        } elseif (!$this->lock_lines) {
            $this->files->put($file_name, $file);
        }
    }

    /**
     * Build an XML representation.
     *
     * @return string
     */
    public function toXml() : string
    {
        // Sort files by name
        $this->files->ksort();

        $xml_document = new \DomDocument('1.0', 'UTF-8');
        $xml_document->formatOutput = true;

        $xml_coverage = $xml_document->createElement('coverage');
        $xml_coverage->setAttribute('generated', $_SERVER['REQUEST_TIME']);
        $xml_document->appendChild($xml_coverage);

        $xml_project = $xml_document->createElement('project');
        $xml_project->setAttribute('timestamp', $_SERVER['REQUEST_TIME']);
        $xml_coverage->appendChild($xml_project);

        $packages = new \Ds\Map();

        foreach ($this->files as $name => $item) {
            $xml_file = Accumulator::buildFile($xml_document, $name, $item);
            $package_name = $item->getPackageName();
            if (is_null($package_name)) {
                $xml_project->appendChild($xml_file);
            } elseif ($packages->hasKey($package_name)) {
                $packages->get($package_name)->appendChild($xml_file);
            } else {
                $xml_package = $xml_document->createElement('package');
                $xml_package->setAttribute('name', $package_name);
                $xml_project->appendChild($xml_package);
                $xml_package->appendChild($xml_file);
                $packages->put($package_name, $xml_package);
            }
        }

        if (!is_null($this->metrics)) {
            $xml_project->appendChild(Accumulator::buildMetrics($xml_document, $this->metrics));
        }

        return $xml_document->saveXML();
    }

    /**
     * Build an xml representation of a file.
     *
     * @param \DomDocument $xml_document
     * @param string $name
     * @param File $file
     * @return \DOMElement
     */
    private static function buildFile(
        \DomDocument $xml_document,
        string $name,
        File $file
    ) : \DOMElement {
        $xml_file = $xml_document->createElement('file');
        $xml_file->setAttribute('name', $name);

        foreach ($file->getClasses() as $name => $class) {
            $xml_class = $xml_document->createElement('class');
            $xml_class->setAttribute('name', $name);
            $namespace = $class->getNamespace();
            if (!is_null($namespace)) {
                $xml_class->setAttribute('namespace', $namespace);
            }
            $metrics = $class->getMetrics();
            if (!is_null($metrics)) {
                $xml_metrics = Accumulator::buildMetrics($xml_document, $metrics);
                $xml_class->appendChild($xml_metrics);
            }
            $xml_file->appendChild($xml_class);
        }

        foreach ($file->getLines() as $number => $line) {
            $xml_line = $xml_document->createElement('line');
            $xml_line->setAttribute('num', $number);
            foreach ($line->getProperties() as $name => $value) {
                $xml_line->setAttribute($name, $value);
            }
            $xml_line->setAttribute('count', $line->getCount());
            $xml_file->appendChild($xml_line);
        }

        $metrics = $file->getMetrics();
        if (!is_null($metrics)) {
            $xml_metrics = Accumulator::buildMetrics($xml_document, $metrics);
            $xml_file->appendChild($xml_metrics);
        }

        return $xml_file;
    }

    /**
     * Build an xml representation of a set of metrics.
     *
     * @param \DomDocument $xml_document
     * @param Metrics $metrics
     * @return \DOMElement
     */
    private static function buildMetrics(
        \DomDocument $xml_document,
        Metrics $metrics
    ) : \DOMElement {
        $xml_metrics = $xml_document->createElement('metrics');
        foreach ($metrics->getProperties() as $name => $value) {
            $xml_metrics->setAttribute($name, $value);
        }
        return $xml_metrics;
    }
}
