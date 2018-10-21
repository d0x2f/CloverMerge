<?php

namespace d0x2f\CloverMerge;

/**
 * Represents a set of metrics.
 */
class Metrics
{
    /** @var integer */
    public $statement_count = 0;

    /** @var integer */
    public $covered_statement_count = 0;

    /** @var integer */
    public $conditional_count = 0;

    /** @var integer */
    public $covered_conditional_count = 0;

    /** @var integer */
    public $method_count = 0;

    /** @var integer */
    public $covered_method_count = 0;

    /** @var integer */
    public $class_count = 0;

    /** @var integer */
    public $file_count = 0;

    /** @var integer */
    public $package_count = 0;

    /**
     * Constructor
     *
     * @param integer $statement_count
     * @param integer $covered_statement_count
     * @param integer $conditional_count
     * @param integer $covered_conditional_count
     * @param integer $method_count
     * @param integer $covered_method_count
     * @param integer $class_count
     * @param integer $file_count
     * @param integer $package_count
     */
    public function __construct(
        int $statement_count = 0,
        int $covered_statement_count = 0,
        int $conditional_count = 0,
        int $covered_conditional_count = 0,
        int $method_count = 0,
        int $covered_method_count = 0,
        int $class_count = 0,
        int $file_count = 0,
        int $package_count = 0
    ) {
        $this->statement_count = $statement_count;
        $this->covered_statement_count = $covered_statement_count;
        $this->conditional_count = $conditional_count;
        $this->covered_conditional_count = $covered_conditional_count;
        $this->method_count = $method_count;
        $this->covered_method_count = $covered_method_count;
        $this->class_count = $class_count;
        $this->file_count = $file_count;
        $this->package_count = $package_count;
    }

    /**
     * Create an XML element to represent these metrics under a file.
     *
     * @param \DOMDocument $xml_document The parent document.
     * @return \DOMElement
     */
    public function toFileXml(
        \DOMDocument $xml_document
    ) : \DOMElement {
        $xml_metrics = $xml_document->createElement('metrics');

        // We can't know the complexity, just set 0
        // (attribute required by the clover xml schema)
        $xml_metrics->setAttribute('complexity', '0');

        $xml_metrics->setAttribute('elements', (string)$this->getElementCount());
        $xml_metrics->setAttribute('coveredelements', (string)$this->getCoveredElementCount());
        $xml_metrics->setAttribute('conditionals', (string)$this->conditional_count);
        $xml_metrics->setAttribute('coveredconditionals', (string)$this->covered_conditional_count);
        $xml_metrics->setAttribute('statements', (string)$this->statement_count);
        $xml_metrics->setAttribute('coveredstatements', (string)$this->covered_statement_count);
        $xml_metrics->setAttribute('methods', (string)$this->method_count);
        $xml_metrics->setAttribute('coveredmethods', (string)$this->covered_method_count);
        $xml_metrics->setAttribute('classes', (string)$this->class_count);

        return $xml_metrics;
    }

    /**
     * Create an XML element to represent these metrics under a package.
     * Contains all the attributes of the file context plus the number of files.
     *
     * @param \DOMDocument $xml_document The parent document.
     * @return \DOMElement
     */
    public function toPackageXml(
        \DOMDocument $xml_document
    ) : \DOMElement {
        $xml_metrics = $this->toFileXml($xml_document);
        $xml_metrics->setAttribute('files', (string)$this->file_count);
        return $xml_metrics;
    }

    /**
     * Create an XML element to represent these metrics under a project.
     * Contains all the attributes of the package context plus the number of packages.
     *
     * @param \DOMDocument $xml_document The parent document.
     * @return \DOMElement
     */
    public function toProjectXml(
        \DOMDocument $xml_document
    ) : \DOMElement {
        $xml_metrics = $this->toPackageXml($xml_document);
        $xml_metrics->setAttribute('packages', (string)$this->package_count);
        return $xml_metrics;
    }

    /**
     * Merge another set of metrics into this one.
     *
     * @param Metrics $metrics
     * @return void
     */
    public function merge(Metrics $metrics) : void
    {
        $this->statement_count += $metrics->statement_count;
        $this->covered_statement_count += $metrics->covered_statement_count;
        $this->conditional_count += $metrics->conditional_count;
        $this->covered_conditional_count += $metrics->covered_conditional_count;
        $this->method_count += $metrics->method_count;
        $this->covered_method_count += $metrics->covered_method_count;
        $this->class_count += $metrics->class_count;
        $this->file_count += $metrics->file_count;
        $this->package_count += $metrics->package_count;
    }

    /**
     * Return the number of elements.
     *
     * @return integer
     */
    public function getElementCount() : int
    {
        return $this->statement_count +
               $this->conditional_count +
               $this->method_count;
    }

    /**
     * Return the number of covered elemetns.
     *
     * @return integer
     */
    public function getCoveredElementCount() : int
    {
        return $this->covered_statement_count +
               $this->covered_conditional_count +
               $this->covered_method_count;
    }
}
