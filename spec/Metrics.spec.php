<?php

namespace d0x2f\CloverMerge\Spec;

use d0x2f\CloverMerge\Metrics;

describe('Metrics', function () {
    describe('__construct', function () {
        describe('Receives a map of properties.', function () {
            beforeEach(function () {
                $this->instance = new Metrics(new \Ds\Map([
                    'foo' => 'bar',
                    'baz' => 'fred'
                ]));
            });

            it('produces a valid instance.', function () {
                expect($this->instance)->toBeAnInstanceOf(Metrics::class);
            });
        });
    });
    describe('fromXML', function () {
        describe('Receives an XML element.', function () {
            beforeEach(function () {
                $xml_element = simplexml_load_string(
                    '<?xml version="1.0" encoding="UTF-8"?>
                    <metrics foo="bar" baz="fred" />'
                );
                $this->instance = Metrics::fromXML($xml_element);
            });

            it('produces a valid instance.', function () {
                expect($this->instance)->toBeAnInstanceOf(Metrics::class);
            });

            it('has the correct properties set.', function () {
                expect($this->instance->getProperties()->toArray())->toBe([
                    'foo' => 'bar',
                    'baz' => 'fred'
                ]);
            });
        });
    });
    describe('getProperties', function () {
        beforeEach(function () {
            $this->instance = new Metrics(new \Ds\Map([
                'foo' => 'bar',
                'baz' => 'fred'
            ]));
            $this->result = $this->instance->getProperties();
        });

        it('returns the properties map.', function () {
            expect($this->result->toArray())->toBe([
                'foo' => 'bar',
                'baz' => 'fred'
            ]);
        });
    });
});
