<?php

namespace d0x2f\CloverMerge\Spec;

use d0x2f\CloverMerge\Invocation;

describe('Invocation', function () {
    describe('execute', function () {
        describe('Receives a valid cli argument list.', function () {
            beforeEach(function () {
                allow('is_file')->toBeCalled()->andReturn(true);
                allow('simplexml_load_file')->toBeCalled()->andReturn(
                    new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><coverage/>')
                );
                $this->invocation = new Invocation(['prog', '-o', 'test', 'path', 'path2']);
            });

            it('throws an error.', function () {
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
});
