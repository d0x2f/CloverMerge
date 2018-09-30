<?php

namespace d0x2f\CloverMerge\Spec;

use d0x2f\CloverMerge\ClassT;
use d0x2f\CloverMerge\Metrics;

describe('ClassT', function () {
    describe('__construct', function () {
        describe('Receives a namespace and metrics.', function () {
            beforeEach(function () {
                $this->metrics = new Metrics(new \Ds\Map([
                    'foo' => 'bar',
                    'baz' => 'fred'
                ]));
                $this->instance = new ClassT('Example\Namespace', $this->metrics);
            });

            it('produces a valid instance.', function () {
                expect($this->instance)->toBeAnInstanceOf(ClassT::class);
            });
        });
    });
    describe('fromXML', function () {
        describe('Receives an XML element.', function () {
            beforeEach(function () {
                $xml_element = simplexml_load_string(
                    '<?xml version="1.0" encoding="UTF-8"?>
                    <class name="Example\Namespace\Class" namespace="Example\Namespace">
                        <metrics foo="bar" baz="fred" />
                    </class>'
                );
                $this->instance = ClassT::fromXML($xml_element);
            });

            it('produces a valid instance.', function () {
                expect($this->instance)->toBeAnInstanceOf(ClassT::class);
            });

            it('has the correct namespace set.', function () {
                expect($this->instance->getNamespace())->toBe('Example\Namespace');
            });

            it('has the correct metrics set.', function () {
                expect($this->instance->getMetrics()->getProperties()->toArray())->toBe([
                    'foo' => 'bar',
                    'baz' => 'fred'
                ]);
            });
        });
    });
    describe('getNamespace', function () {
        beforeEach(function () {
            $this->metrics = new Metrics(new \Ds\Map([
                'foo' => 'bar',
                'baz' => 'fred'
            ]));
            $this->instance = new ClassT('Example\Namespace', $this->metrics);
            $this->result = $this->instance->getNamespace();
        });

        it('returns the properties map.', function () {
            expect($this->result)->toBe('Example\Namespace');
        });
    });
    describe('getMetrics', function () {
        beforeEach(function () {
            $this->metrics = new Metrics(new \Ds\Map([
                'foo' => 'bar',
                'baz' => 'fred'
            ]));
            $this->instance = new ClassT('Example\Namespace', $this->metrics);
            $this->result = $this->instance->getMetrics();
        });

        it('returns the properties map.', function () {
            expect($this->result->getProperties()->toArray())->toBe([
                'foo' => 'bar',
                'baz' => 'fred'
            ]);
        });
    });
});
