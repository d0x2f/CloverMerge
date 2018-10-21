<?php

namespace d0x2f\CloverMerge\Spec;

use d0x2f\CloverMerge\ClassT;

/**
 * @phan-closure-scope \Kahlan\Scope
 */
describe('ClassT', function () {
    describe('__construct', function () {
        context('Receives a namespace and metrics.', function () {
            beforeEach(function () {
                $properties = new \Ds\Map([
                    'foo' => 'bar',
                    'baz' => 'fred'
                ]);
                $this->instance = new ClassT($properties);
            });

            it('produces a valid instance.', function () {
                expect($this->instance)->toBeAnInstanceOf(ClassT::class);
            });
        });
    });
    describe('fromXML', function () {
        context('Receives an XML element.', function () {
            beforeEach(function () {
                $xml_element = simplexml_load_string(
                    '<?xml version="1.0" encoding="UTF-8"?>
                    <class name="Example\Namespace\Class" namespace="Example\Namespace" foo="bar" baz="fred"/>'
                );
                assert($xml_element !== false);
                $this->instance = ClassT::fromXML($xml_element);
            });

            it('produces a valid instance.', function () {
                expect($this->instance)->toBeAnInstanceOf(ClassT::class);
            });

            it('has the correct properties set.', function () {
                expect($this->instance->getProperties()->toArray())->toBe([
                    'name' => 'Example\Namespace\Class',
                    'namespace' => 'Example\Namespace',
                    'foo' => 'bar',
                    'baz' => 'fred'
                ]);
            });
        });
    });
    describe('getProperties', function () {
        beforeEach(function () {
            $properties = new \Ds\Map([
                'foo' => 'bar',
                'baz' => 'fred'
            ]);
            $instance = new ClassT($properties);
            $this->result = $instance->getProperties();
        });

        it('returns the properties map.', function () {
            expect($this->result->toArray())->toBe([
                'foo' => 'bar',
                'baz' => 'fred'
            ]);
        });
    });
});
