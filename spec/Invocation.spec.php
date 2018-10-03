<?php

namespace d0x2f\CloverMerge\Spec;

use d0x2f\CloverMerge\Invocation;
use d0x2f\CloverMerge\Accumulator;
use d0x2f\CloverMerge\Utilities;

describe('Invocation', function () {
    describe('__construct', function () {
        describe('Receives a valid cli argument list.', function () {
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
        describe('Receives an empty cli argument list.', function () {
            beforeEach(function () {
                $this->closure = function () {
                    new Invocation([]);
                };
            });

            it('throws an error.', function () {
                expect($this->closure)->toThrow();
            });
        });
        describe('Receives a cli argument list missing the output option.', function () {
            beforeEach(function () {
                $this->closure = function () {
                    new Invocation(['prog', 'file']);
                };
            });

            it('throws an error.', function () {
                expect($this->closure)->toThrow('Missing required option: output');
            });
        });
        describe('Receives a cli argument list with an invalid mode option.', function () {
            beforeEach(function () {
                $this->closure = function () {
                    new Invocation(['prog', '-o', 'test', '-m', 'bogus', 'file']);
                };
            });

            it('throws an error.', function () {
                expect($this->closure)->toThrow('Merge option must be one of: additive, exclusive or inclusive.');
            });
        });
        describe('Receives a cli argument list without any filenames given.', function () {
            beforeEach(function () {
                $this->closure = function () {
                    new Invocation(['prog', '-o', 'test']);
                };
            });

            it('throws an error.', function () {
                expect($this->closure)->toThrow("At least one input path is required (preferably two).");
            });
        });
        describe('Receives a cli argument list containing a list of files to merge.', function () {
            describe('Where one doesn\'t exist', function () {
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

            describe('Where one refers to an invalid XML document.', function () {
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
        describe('With fixtures.', function () {
            describe('Executes on all available fixtures.', function () {
                beforeEach(function () {
                    $fixtures = glob(__DIR__.'/fixtures/*.xml');
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
                    $this->closure();
                });
            });
        });
        describe('With mocked dependencies.', function () {
            beforeEach(function () {
                allow('is_file')->toBeCalled()->andReturn(true);
                allow('simplexml_load_file')->toBeCalled()->andReturn(
                    new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><coverage/>')
                );
                allow(Accumulator::class)->toReceive('parseAll')->andReturn();
                allow(Accumulator::class)->toReceive('toXml')->andReturn(new \Ds\Map());
                $this->invocation = new Invocation(['prog', '-o', 'test', 'path', 'path2']);
                $this->closure = function () {
                    $this->invocation->execute();
                };
            });
            describe('Executes an invocation instance where the output file is readable.', function () {
                beforeEach(function () {
                    allow('file_put_contents')->toBeCalled()->andReturn(100);
                });
                it('delegates to Accumulator::parseAll.', function () {
                    expect(Accumulator::class)->toReceive('parseAll');
                    $this->closure();
                });
                it('delegates to Accumulator::toXml.', function () {
                    expect(Accumulator::class)->toReceive('toXml');
                    $this->closure();
                });
                it('writes to the output file.', function () {
                    expect('file_put_contents')->toBeCalled()->with(
                        'test'
                    )->once();
                    $this->closure();
                });
            });
            describe('Executes an invocation instance where the output file in unreadable.', function () {
                beforeEach(function () {
                    allow('file_put_contents')->toBeCalled()->andReturn(false);
                });
                it('throws an error.', function () {
                    expect('file_put_contents')->toBeCalled()->with(
                        'test'
                    )->once();
                    expect($this->closure)->toThrow("Unable to write to given output file.");
                });
            });
        });
    });
});
