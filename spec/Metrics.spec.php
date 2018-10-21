<?php

namespace d0x2f\CloverMerge\Spec;

use d0x2f\CloverMerge\Metrics;

/**
 * @phan-closure-scope \Kahlan\Scope
 */
describe('Metrics', function () {
    describe('__construct', function () {
        context('Receives a set of statistics.', function () {
            beforeEach(function () {
                $this->instance = new Metrics(10, 9, 8, 7, 6, 5, 4, 3, 2);
            });

            it('produces a valid instance.', function () {
                expect($this->instance)->toBeAnInstanceOf(Metrics::class);
            });
        });
    });
    describe('toFileXml', function () {
        beforeEach(function () {
            $instance = new Metrics(10, 9, 8, 7, 6, 5, 4, 3, 2);
            $xml_document = new \DOMDocument();
            $this->result = $instance->toFileXml($xml_document);
        });

        it("produces a XML element containing an 'elements' attribute.", function () {
            expect($this->result->getAttribute('elements'))->toBe('24');
        });

        it("produces a XML element containing a 'coveredelements' attribute.", function () {
            expect($this->result->getAttribute('coveredelements'))->toBe('21');
        });

        it("produces a XML element containing a 'conditionals' attribute.", function () {
            expect($this->result->getAttribute('conditionals'))->toBe('8');
        });

        it("produces a XML element containing a 'coveredconditionals' attribute.", function () {
            expect($this->result->getAttribute('coveredconditionals'))->toBe('7');
        });

        it("produces a XML element containing a 'statements' attribute.", function () {
            expect($this->result->getAttribute('statements'))->toBe('10');
        });

        it("produces a XML element containing a 'coveredstatements' attribute.", function () {
            expect($this->result->getAttribute('coveredstatements'))->toBe('9');
        });

        it("produces a XML element containing a 'methods' attribute.", function () {
            expect($this->result->getAttribute('methods'))->toBe('6');
        });

        it("produces a XML element containing a 'coveredmethods' attribute.", function () {
            expect($this->result->getAttribute('coveredmethods'))->toBe('5');
        });

        it("produces a XML element containing a 'classes' attribute.", function () {
            expect($this->result->getAttribute('classes'))->toBe('4');
        });

        it("produces a XML element without a 'files' attribute.", function () {
            expect($this->result->hasAttribute('files'))->toBe(false);
        });

        it("produces a XML element without a 'packages' attribute.", function () {
            expect($this->result->hasAttribute('packages'))->toBe(false);
        });
    });
    describe('toPackageXml', function () {
        beforeEach(function () {
            $instance = new Metrics(10, 9, 8, 7, 6, 5, 4, 3, 2);
            $xml_document = new \DOMDocument();
            $this->result = $instance->toPackageXml($xml_document);
        });

        it("produces a XML element containing an 'elements' attribute.", function () {
            expect($this->result->getAttribute('elements'))->toBe('24');
        });

        it("produces a XML element containing a 'coveredelements' attribute.", function () {
            expect($this->result->getAttribute('coveredelements'))->toBe('21');
        });

        it("produces a XML element containing a 'conditionals' attribute.", function () {
            expect($this->result->getAttribute('conditionals'))->toBe('8');
        });

        it("produces a XML element containing a 'coveredconditionals' attribute.", function () {
            expect($this->result->getAttribute('coveredconditionals'))->toBe('7');
        });

        it("produces a XML element containing a 'statements' attribute.", function () {
            expect($this->result->getAttribute('statements'))->toBe('10');
        });

        it("produces a XML element containing a 'coveredstatements' attribute.", function () {
            expect($this->result->getAttribute('coveredstatements'))->toBe('9');
        });

        it("produces a XML element containing a 'methods' attribute.", function () {
            expect($this->result->getAttribute('methods'))->toBe('6');
        });

        it("produces a XML element containing a 'coveredmethods' attribute.", function () {
            expect($this->result->getAttribute('coveredmethods'))->toBe('5');
        });

        it("produces a XML element containing a 'classes' attribute.", function () {
            expect($this->result->getAttribute('classes'))->toBe('4');
        });

        it("produces a XML element containing a 'files' attribute.", function () {
            expect($this->result->getAttribute('files'))->toBe('3');
        });

        it("produces a XML element without a 'packages' attribute.", function () {
            expect($this->result->hasAttribute('packages'))->toBe(false);
        });
    });
    describe('toProjectXml', function () {
        beforeEach(function () {
            $instance = new Metrics(10, 9, 8, 7, 6, 5, 4, 3, 2);
            $xml_document = new \DOMDocument();
            $this->result = $instance->toProjectXml($xml_document);
        });

        it("produces a XML element containing an 'elements' attribute.", function () {
            expect($this->result->getAttribute('elements'))->toBe('24');
        });

        it("produces a XML element containing a 'coveredelements' attribute.", function () {
            expect($this->result->getAttribute('coveredelements'))->toBe('21');
        });

        it("produces a XML element containing a 'conditionals' attribute.", function () {
            expect($this->result->getAttribute('conditionals'))->toBe('8');
        });

        it("produces a XML element containing a 'coveredconditionals' attribute.", function () {
            expect($this->result->getAttribute('coveredconditionals'))->toBe('7');
        });

        it("produces a XML element containing a 'statements' attribute.", function () {
            expect($this->result->getAttribute('statements'))->toBe('10');
        });

        it("produces a XML element containing a 'coveredstatements' attribute.", function () {
            expect($this->result->getAttribute('coveredstatements'))->toBe('9');
        });

        it("produces a XML element containing a 'methods' attribute.", function () {
            expect($this->result->getAttribute('methods'))->toBe('6');
        });

        it("produces a XML element containing a 'coveredmethods' attribute.", function () {
            expect($this->result->getAttribute('coveredmethods'))->toBe('5');
        });

        it("produces a XML element containing a 'classes' attribute.", function () {
            expect($this->result->getAttribute('classes'))->toBe('4');
        });

        it("produces a XML element containing a 'files' attribute.", function () {
            expect($this->result->getAttribute('files'))->toBe('3');
        });

        it("produces a XML element containing a 'packages' attribute.", function () {
            expect($this->result->getAttribute('packages'))->toBe('2');
        });
    });
});
