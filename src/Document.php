<?php

namespace d0x2f\CloverMerge;

/**
 * Document functions.
 */
class Document
{
    /**
     * Parse the given documents into a map of files and metrics.
     *
     * @param \Ds\Vector $documents
     * @return \Ds\Map
     */
    public static function parseSet(\Ds\Vector $documents) : \Ds\Map
    {
        return $documents->reduce(
            function (\Ds\Map $items, \SimpleXMLElement $document) {
                if ($document->getName() !== 'coverage') {
                    return $items;
                }
                $projects = $document->children();
                foreach ($projects as $project) {
                    $name = $project->getName();
                    if ($name !== 'project') {
                        Utilities::logWarning("Ignoring unknown element: {$name}.");
                        continue;
                    }
                    self::parseItems($items, $project->children());
                }
                return $items;
            },
            new \Ds\Map()
        );
    }

    /**
     * Parse items from the given elements.
     *
     * @param \Ds\Map $items
     * @param \SimpleXMLElement $elements
     * @param string|null $package_name The package name new files should be given.
     * @return void
     */
    private static function parseItems(
        \Ds\Map &$items,
        \SimpleXMLElement $elements,
        ?string $package_name = null
    ) : void {
        foreach ($elements as $element) {
            $name = $element->getName();
            $attributes = iterator_to_array($element->attributes());
            if ($name === 'package') {
                if (!array_key_exists('name', $attributes)) {
                    Utilities::logWarning('Ignoring package with no name.');
                }
                self::parseItems($items, $element->children(), $attributes['name'] ?? null);
            } elseif ($name === 'file') {
                if (!array_key_exists('name', $attributes)) {
                    Utilities::logWarning('Ignoring file with no name.');
                    continue;
                }
                $file_name = (string)$attributes['name'];
                $file = File::fromXml($element, $package_name);
                if ($items->hasKey($file_name)) {
                    $items->get($file_name)->merge($file);
                } else {
                    $items->put($file_name, $file);
                }
            } elseif ($name === 'metrics') {
                $metrics = Metrics::fromXml($element);
                if (!$items->hasKey('metrics')) {
                    $items->put('metrics', $metrics);
                }
            } else {
                Utilities::logWarning("Ignoring unknown element: {$name}.");
            }
        }
    }

    /**
     * Build an XML document from the given array of items.
     *
     * @param \Ds\Map $items
     * @return string
     */
    public static function build(\Ds\Map $items) : string
    {
        // Sort items by name
        $items->ksort();

        $xml_document = new \DomDocument('1.0', 'UTF-8');
        $xml_document->formatOutput = true;

        $xml_coverage = $xml_document->createElement('coverage');
        $xml_coverage->setAttribute('generated', $_SERVER['REQUEST_TIME']);
        $xml_document->appendChild($xml_coverage);

        $xml_project = $xml_document->createElement('project');
        $xml_project->setAttribute('timestamp', $_SERVER['REQUEST_TIME']);
        $xml_coverage->appendChild($xml_project);

        $packages = new \Ds\Map();

        foreach ($items as $name => $item) {
            if ($item instanceof File) {
                $xml_file = Document::buildFile($xml_document, $name, $item);
                $package_name = $item->getPackageName();
                if (is_null($package_name)) {
                    $xml_project->appendChild($xml_file);
                } elseif (!$packages->hasKey($package_name)) {
                    $xml_package = $xml_document->createElement('package');
                    $xml_package->setAttribute('name', $package_name);
                    $xml_project->appendChild($xml_package);
                    $xml_package->appendChild($xml_file);
                    $packages->put($package_name, $xml_package);
                } else {
                    $packages->get($package_name)->appendChild($xml_file);
                }
            } elseif ($item instanceof Metrics) {
                $xml_project->appendChild(Document::buildMetrics($xml_document, $item));
            }
        }

        return $xml_document->saveXML();
    }

    private static function buildFile(\DomDocument $xml_document, string $name, File $file) : \DOMElement
    {
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
                $xml_metrics = Document::buildMetrics($xml_document, $metrics);
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
            $xml_metrics = Document::buildMetrics($xml_document, $metrics);
            $xml_file->appendChild($xml_metrics);
        }

        return $xml_file;
    }

    private static function buildMetrics(\DomDocument $xml_document, Metrics $metrics) : \DOMElement
    {
        $xml_metrics = $xml_document->createElement('metrics');
        foreach ($metrics->getProperties() as $name => $value) {
            $xml_metrics->setAttribute($name, $value);
        }
        return $xml_metrics;
    }
}
