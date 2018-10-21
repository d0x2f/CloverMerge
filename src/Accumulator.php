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
            // Ignore input metrics, we'll compute our own.
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
     * Returns a tuple of the xml string and a metrics object.
     *
     * @return array{0:string,1:Metrics}
     */
    public function toXml() : array
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

        $project_metrics = new Metrics();

        $packages = new \Ds\Map();

        foreach ($this->files as $name => $file) {
            [$xml_file, $file_metrics] = $file->toXml($xml_document, $name);
            $project_metrics->merge($file_metrics);
            $package_name = $file->getPackageName();
            if (is_null($package_name)) {
                $xml_project->appendChild($xml_file);
            } elseif ($packages->hasKey($package_name)) {
                $packages->get($package_name)[0]->appendChild($xml_file);
                $packages->get($package_name)[1]->merge($file_metrics);
            } else {
                $xml_package = $xml_document->createElement('package');
                $xml_package->setAttribute('name', $package_name);
                $xml_project->appendChild($xml_package);
                $xml_package->appendChild($xml_file);
                $package_metrics = new Metrics();
                $package_metrics->package_count = 1;
                $package_metrics->merge($file_metrics);
                $packages->put($package_name, [$xml_package, $package_metrics]);
            }
        }

        foreach ($packages as $package) {
            $package_xml = $package[0];
            $package_metrics = $package[1];
            $package_xml->appendChild($package_metrics->toPackageXml($xml_document));
        }

        $xml_project->appendChild($project_metrics->toProjectXml($xml_document));

        return [$xml_document->saveXML(), $project_metrics];
    }
}
