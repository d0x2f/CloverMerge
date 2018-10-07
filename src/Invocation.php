<?php

namespace d0x2f\CloverMerge;

use Garden\Cli\Cli;

/**
 * Represents an instance of a CLI invocation.
 */
class Invocation
{
    /**
     * Output path.
     *
     * @var string
     */
    private $output_path;

    /**
     * Merge mode.
     *
     * @var string
     */
    private $merge_mode;

    /**
     * Parsed input XML documents.
     *
     * @var \Ds\Vector
     */
    private $documents;

    /**
     * Initialise the invocation.
     *
     * @param array $argv
     * @throws ArgumentException
     */
    public function __construct(array $argv)
    {
        $cli = Cli::create()
            ->opt('output:o', 'output file path', true)
            ->opt('mode:m', 'merge mode: additive, exclusive or inclusive (default)', false)
            ->arg('paths', 'input file paths', true);

        try {
            $arguments = $cli->parse($argv, false);
        } catch (\Exception $e) {
            throw new ArgumentException($e->getMessage());
        }

        $this->output_path = $arguments->getOpt('output');
        $this->merge_mode = $arguments->getOpt('mode', 'inclusive');

        if (!in_array($this->merge_mode, ['inclusive', 'exclusive', 'additive'])) {
            throw new ArgumentException('Merge option must be one of: additive, exclusive or inclusive.');
        }

        // Using a set removes duplicates but we need to wait for the next ds realease to
        // add support for the map method.
        // https://github.com/php-ds/extension/commit/ae9ce662360e9f93b4b6c7abb78b938672be1abc
        // $paths = new \Ds\Set($arguments->getArgs());
        $paths = new \Ds\Vector(array_values(array_unique($arguments->getArgs())));

        if ($paths->count() === 0) {
            throw new ArgumentException('At least one input path is required (preferably two).');
        }

        if (!Utilities::filesExist($paths)) {
            throw new ArgumentException("One or more of the given file paths couldn't be found.");
        }

        /**
         * @throws ArgumentException
         */
        $this->documents = $paths->map(function ($path) {
            $document = simplexml_load_file($path);
            if ($document === false) {
                throw new ArgumentException("Unable to parse one or more of the input files.");
            }
            return $document;
        });
    }

    /**
     * Execute the invocation.
     *
     * @return void
     * @throws FileException
     */
    public function execute() : void
    {
        $accumulator = new Accumulator($this->merge_mode);

        // Parse
        $accumulator->parseAll($this->documents);

        // Output
        $output = $accumulator->toXml();
        $write_result = file_put_contents($this->output_path, $output);
        if ($write_result === false) {
            throw new FileException("Unable to write to given output file.");
        }

        // Stats
        $files_discovered = $accumulator->getFileCount();
        [$covered, $total] = $accumulator->getCoverage();
        if ($total === 0) {
            $coverage_percentage = 0;
        } else {
            $coverage_percentage = 100 * $covered/$total;
        }
        printf("Files Discovered: %d\n", $files_discovered);
        printf("Final Coverage: %d/%d (%.2f%%)\n", $covered, $total, $coverage_percentage);
    }
}
