<?php

namespace d0x2f\CloverMerge\Spec;

use d0x2f\CloverMerge\Document;
use d0x2f\CloverMerge\File;
use d0x2f\CloverMerge\Utilities;
use d0x2f\CloverMerge\Metrics;

describe('Document', function () {
    describe('parseSet', function () {
        describe('Receives a vector of nice XML documents.', function () {
            beforeEach(function () {
                $this->result = Document::parseSet(new \Ds\Vector([
                    simplexml_load_file(__DIR__.'/fixtures/file-with-package.xml'),
                    simplexml_load_file(__DIR__.'/fixtures/file-without-package.xml')
                ]));
            });

            it('returns a map of files parsed from the input.', function () {
                expect($this->result)->toBeAnInstanceOf(\Ds\Map::class);
                expect($this->result)->toHaveLength(2);

                expect($this->result->keys()->toArray())->toBe([
                    'test.php',
                    'metrics'
                ]);

                $metrics = $this->result->get('metrics');
                expect($metrics)->toBeAnInstanceOf(Metrics::class);

                $file = $this->result->get('test.php');
                expect($file)->toBeAnInstanceOf(File::class);

                $lines = $file->getLines();
                expect($lines)->toHaveLength(5);

                expect($lines->keys()->toArray())->toBe([1, 2, 3, 4, 5]);

                expect($lines->get(1)->getCount())->toBe(0);
                expect($lines->get(2)->getCount())->toBe(2);
                expect($lines->get(3)->getCount())->toBe(4);
                expect($lines->get(4)->getCount())->toBe(6);
                expect($lines->get(5)->getCount())->toBe(8);
            });
        });
        describe('Receives a vector of XML documents with junk included.', function () {
            beforeEach(function () {
                allow(Utilities::class)->toReceive('::logWarning')->andReturn();
                $this->closure = function () {
                    return Document::parseSet(new \Ds\Vector([
                        simplexml_load_file(__DIR__.'/fixtures/file-with-junk.xml'),
                        simplexml_load_file(__DIR__.'/fixtures/non-clover.xml')
                    ]));
                };
            });

            it('ignores the junk and returns a map of files parsed from the input.', function () {
                $result = $this->closure();

                expect(Utilities::class)->toReceive('::logWarning')->with('Ignoring unknown element: bogus.');
                expect(Utilities::class)->toReceive('::logWarning')->with('Ignoring unknown element: dinosaurs.');
                expect(Utilities::class)->toReceive('::logWarning')->with('Ignoring unknown element: folder.');

                expect($result)->toBeAnInstanceOf('\Ds\Map');
                expect($result)->toHaveLength(1);

                $file = $result->first()->value;
                expect($file)->toBeAnInstanceOf(File::class);

                $lines = $file->getLines();
                expect($lines)->toHaveLength(5);

                $keys = $lines->keys();
                expect($keys->toArray())->toBe([1, 2, 3, 4, 5]);

                expect($lines->get(1)->getCount())->toBe(0);
                expect($lines->get(2)->getCount())->toBe(1);
                expect($lines->get(3)->getCount())->toBe(2);
                expect($lines->get(4)->getCount())->toBe(3);
                expect($lines->get(5)->getCount())->toBe(4);
            });
        });
        describe('Receives a vector of XML documents with errors.', function () {
            beforeEach(function () {
                allow(Utilities::class)->toReceive('::logWarning')->andReturn();
                $this->closure = function () {
                    return Document::parseSet(new \Ds\Vector([
                        simplexml_load_file(__DIR__.'/fixtures/file-with-errors.xml')
                    ]));
                };
            });

            it('Makes a warning log entry for each encountered error.', function () {
                $result = $this->closure();

                expect(Utilities::class)->toReceive('::logWarning')->with('Ignoring package with no name.');
                expect(Utilities::class)->toReceive('::logWarning')->with('Ignoring file with no name.');
                expect(Utilities::class)->toReceive('::logWarning')->with('Ignoring unknown element: bogus.');
            });
        });
    });
});
