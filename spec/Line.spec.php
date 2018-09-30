<?php

namespace d0x2f\CloverMerge\Spec;

use d0x2f\CloverMerge\Line;
use d0x2f\CloverMerge\Utilities;

describe('Line', function () {
    describe('__construct', function () {
        describe('Receives a map of properties.', function () {
            beforeEach(function () {
                $this->instance = new Line(new \Ds\Map([
                    'foo' => 'bar',
                    'baz' => 'fred'
                ]), 23);
            });

            it('produces a valid instance.', function () {
                expect($this->instance)->toBeAnInstanceOf(Line::class);
            });
        });
    });
    describe('fromXML', function () {
        describe('Receives a valid XML element.', function () {
            beforeEach(function () {
                $xml_element = simplexml_load_string(
                    '<?xml version="1.0" encoding="UTF-8"?>
                    <line foo="bar" baz="fred" count="2"/>'
                );
                $this->instance = Line::fromXML($xml_element);
            });

            it('produces a valid instance.', function () {
                expect($this->instance)->toBeAnInstanceOf(Line::class);
            });

            it('has the correct properties set.', function () {
                expect($this->instance->getProperties()->toArray())->toBe([
                    'foo' => 'bar',
                    'baz' => 'fred'
                ]);
            });

            it('has the correct count set.', function () {
                expect($this->instance->getCount())->toBe(2);
            });
        });
        describe('Receives a XML element with errors.', function () {
            beforeEach(function () {
                $xml_element = simplexml_load_string(
                    '<?xml version="1.0" encoding="UTF-8"?>
                    <line foo="bar" baz="fred"/>'
                );
                $this->closure = function () use ($xml_element) {
                    return Line::fromXML($xml_element);
                };
            });

            it('throws an error.', function () {
                expect($this->closure)->toThrow('Unable to parse line, missing count attribute.');
            });
        });
    });
});
