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
     * Parsed input XML documents.
     *
     * @var \Ds\Set
     */
    private $documents;

    /**
     * Initialise the invocation.
     *
     * @param array $argv
     */
    public function __construct(array $argv)
    {
        $cli = Cli::create()
            ->opt('output:o', 'output file path', true)
            ->arg('paths', 'input file paths', true);

        try {
            $arguments = $cli->parse($argv, false);
        } catch (\Exception $e) {
            throw new ArgumentException($e->getMessage());
        }

        $this->output_path = $arguments->getOpt('output');

        // Using a set removes duplicates
        $paths = new \Ds\Set(array_values($arguments->getArgs()));

        if ($paths->count() === 0) {
            throw new ArgumentException('At least one input path is required (preferably two).');
        }

        if (!Utilities::filesExist($paths)) {
            throw new ArgumentException("One or more of the given file paths couldn't be found.");
        }

        $this->documents = $paths->map(function ($path) {
            return simplexml_load_file($path);
        });

        if ($this->documents->contains(false)) {
            throw new ArgumentException("Unable to parse one or more of the input files.");
        }
    }

    public function execute() : void
    {
        $items = Document::parseSet($this->documents);
        $output = Document::build($items);
        $write_result = file_put_contents($this->output_path, $output);
        if ($write_result === false) {
            throw new FileException("Unable to write to given output file.");
        }
    }
}
