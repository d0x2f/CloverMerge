<?php

namespace d0x2f\CloverMerge\Spec;

use d0x2f\CloverMerge\Invocation;
use d0x2f\CloverMerge\Accumulator;
use d0x2f\CloverMerge\Utilities;
use d0x2f\CloverMerge\Metrics;

/**
 * @phan-closure-scope \Kahlan\Scope
 * @phan-file-suppress PhanParamTooMany
 */
describe('Invocation', function () {
    describe('__construct', function () {
        context('Receives a valid cli argument list.', function () {
            beforeEach(function () {
                allow('is_file')->toBeCalled()->andReturn(true);
                allow('simplexml_load_file')->toBeCalled()->andReturn(
                    new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><coverage/>')
                );
                $this->invocation = new Invocation(['prog', '-o', 'test', 'path', 'path2']);
            });

            it('produces an invocation instance.', function () {
                expect($this->invocation)->toBeAnInstanceOf('d0x2f\CloverMerge\Invocation');
            });
        });
        context('Receives an empty cli argument list.', function () {
            beforeEach(function () {
                $this->closure = function () {
                    new Invocation([]);
                };
            });

            it('throws an error.', function () {
                expect($this->closure)->toThrow();
            });
        });
        context('Receives a cli argument list missing the output option.', function () {
            beforeEach(function () {
                $this->closure = function () {
                    new Invocation(['prog', 'file']);
                };
            });

            it('throws an error.', function () {
                expect($this->closure)->toThrow('Missing required option: output');
            });
        });
        context('Receives a cli argument list with an invalid mode option.', function () {
            beforeEach(function () {
                $this->closure = function () {
                    new Invocation(['prog', '-o', 'test', '-m', 'bogus', 'file']);
                };
            });

            it('throws an error.', function () {
                expect($this->closure)->toThrow('Merge option must be one of: additive, exclusive or inclusive.');
            });
        });
        context('Receives a cli argument list without any filenames given.', function () {
            beforeEach(function () {
                $this->closure = function () {
                    new Invocation(['prog', '-o', 'test']);
                };
            });

            it('throws an error.', function () {
                expect($this->closure)->toThrow("At least one input path is required (preferably two).");
            });
        });
        context('Receives a cli argument list containing a list of files to merge.', function () {
            context('Where one doesn\'t exist', function () {
                beforeEach(function () {
                    allow('is_file')->toBeCalled()->andReturn(false);
                    $this->closure = function () {
                        new Invocation(['prog', '-o', 'test', 'file', 'names']);
                    };
                });

                it('throws an error.', function () {
                    expect($this->closure)->toThrow("One or more of the given file paths couldn't be found.");
                });
            });

            context('Where one refers to an invalid XML document.', function () {
                beforeEach(function () {
                    allow('is_file')->toBeCalled()->andReturn(true);
                    allow('simplexml_load_file')->toBeCalled()->andReturn(false);
                    $this->closure = function () {
                        new Invocation(['prog', '-o', 'test', 'file', 'names']);
                    };
                });

                it('throws an error.', function () {
                    expect($this->closure)->toThrow("Unable to parse one or more of the input files.");
                });
            });
        });
    });
    describe('execute', function () {
        context('With fixtures.', function () {
            context('Executes on all available fixtures.', function () {
                beforeEach(function () {
                    $fixtures = glob(__DIR__.'/fixtures/*.xml');
                    assert(is_array($fixtures));
                    $invocation = new Invocation(array_merge(
                        [
                            'prog',
                            '-o', __DIR__.'/../test_output/fixtures_result.xml'
                        ],
                        $fixtures
                    ));
                    $this->closure = function () use ($invocation) {
                        $invocation->execute();
                    };

                    allow(Utilities::class)->toReceive('::logWarning')->andReturn();
                    allow('file_put_contents')->toBeCalled();
                });
                it('writes to the output file.', function () {
                    expect('file_put_contents')->toBeCalled()->with(
                        __DIR__.'/../test_output/fixtures_result.xml'
                    )->once();
                    expect($this->closure)->toEcho("Files Discovered: 4\nFinal Coverage: 17/24 (70.83%)\n");
                });
            });
        });
        context('With mocked dependencies.', function () {
            beforeEach(function () {
                allow('is_file')->toBeCalled()->andReturn(true);
                allow('simplexml_load_file')->toBeCalled()->andReturn(
                    new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><coverage/>')
                );
                allow(Accumulator::class)->toReceive('parseAll')->andReturn();
                allow(Accumulator::class)->toReceive('toXml')->andReturn([new \Ds\Map(), new Metrics()]);
                $this->invocation = new Invocation(['prog', '-o', 'test', 'path', 'path2']);
                $this->closure = function () {
                    $this->invocation->execute();
                };
            });
            context('Executes an invocation instance where the output file is readable.', function () {
                beforeEach(function () {
                    allow('file_put_contents')->toBeCalled()->andReturn(100);
                });
                it('delegates to Accumulator::parseAll.', function () {
                    expect($this->closure)->toEcho("Files Discovered: 0\nFinal Coverage: 0/0 (0.00%)\n");
                    expect(Accumulator::class)->toReceive('parseAll');
                });
                it('delegates to Accumulator::toXml.', function () {
                    expect($this->closure)->toEcho("Files Discovered: 0\nFinal Coverage: 0/0 (0.00%)\n");
                    expect(Accumulator::class)->toReceive('toXml');
                });
                it('writes to the output file.', function () {
                    expect('file_put_contents')->toBeCalled()->with(
                        'test'
                    )->once();
                    expect($this->closure)->toEcho("Files Discovered: 0\nFinal Coverage: 0/0 (0.00%)\n");
                });
            });
            context('Executes an invocation instance where the output file in unreadable.', function () {
                beforeEach(function () {
                    allow('file_put_contents')->toBeCalled()->andReturn(false);
                });
                it('attempts to write to the file and throws an error.', function () {
                    expect('file_put_contents')->toBeCalled()->with(
                        'test'
                    )->once();
                    expect($this->closure)->toThrow("Unable to write to given output file.");
                });
            });
        });
    });
});
