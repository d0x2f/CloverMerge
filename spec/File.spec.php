<?php

namespace d0x2f\CloverMerge\Spec;

use d0x2f\CloverMerge\File;
use d0x2f\CloverMerge\Utilities;

/**
 * @phan-closure-scope \Kahlan\Scope
 * @phan-file-suppress PhanParamTooMany
 */
describe('File', function () {
    describe('__construct', function () {
        context('Receives a package name.', function () {
            beforeEach(function () {
                $this->instance = new File('package_name');
            });

            it('produces a valid instance.', function () {
                expect($this->instance)->toBeAnInstanceOf(File::class);
            });
        });
    });
    describe('fromXML', function () {
        context('Receives a valid XML element.', function () {
            beforeEach(function () {
                $xml_element = simplexml_load_string('
                    <file name="/src/Example/Namespace/Class.php">
                        <class name="Example\Namespace\Class" namespace="Example\Namespace">
                            <metrics bar="foo" fred="baz"/>
                        </class>
                        <line num="22" type="method" name="__construct" count="1"/>
                        <line num="28" type="stmt" count="1"/>
                        <line num="29" type="stmt" count="0"/>
                        <metrics foo="bar" baz="fred"/>
                    </file>
                ');
                assert($xml_element !== false);
                $this->instance = File::fromXML($xml_element, 'package_name');
            });

            it('produces a valid instance.', function () {
                expect($this->instance)->toBeAnInstanceOf(File::class);
            });

            it('has the correct package name set.', function () {
                expect($this->instance->getPackageName())->toBe('package_name');
            });

            /** @phan-suppress PhanUndeclaredProperty */
            it('has the correct classes set.', function () {
                $classes = $this->instance->getClasses();
                expect($classes)->toHaveLength(1);

                $class = $classes->first();
                expect($class->key)->toBe('Example\Namespace\Class');
                expect($class->value->getNamespace())->toBe('Example\Namespace');
            });

            it('has the correct lines set.', function () {
                $lines = $this->instance->getLines();
                expect($lines)->toHaveLength(3);

                $keys = $lines->keys();
                expect($keys->toArray())->toBe([22, 28, 29]);

                expect($lines->get(22)->getCount())->toBe(1);
                expect($lines->get(28)->getCount())->toBe(1);
                expect($lines->get(29)->getCount())->toBe(0);
            });
        });
        describe('Receives a XML element with errors.', function () {
            beforeEach(function () {
                $xml_element = simplexml_load_string('
                    <file name="/src/Example/Namespace/Class.php">
                        <class namespace="Example\Namespace">
                            <metrics bar="foo" fred="baz"/>
                        </class>
                        <line num="22" type="method" name="__construct" count="1"/>
                        <line type="stmt" count="1"/>
                        <line num="29" type="stmt" count="0"/>
                        <metrics foo="bar" baz="fred"/>
                        <banana/>
                    </file>
                ');
                assert($xml_element !== false);
                allow(Utilities::class)->toReceive('::logWarning')->andReturn();
                $this->closure = function () use ($xml_element) {
                    return File::fromXML($xml_element, 'package_name');
                };
            });

            it('produces relevant error logs.', function () {
                $this->closure();

                expect(Utilities::class)->toReceive('::logWarning')->with('Ignoring class with no name.');
                expect(Utilities::class)->toReceive('::logWarning')->with('Ignoring line with no num or count.');
                expect(Utilities::class)->toReceive('::logWarning')->with('Ignoring unknown element: banana.');
            });
        });
    });
    describe('merge', function () {
        context('Receives a second File instance to merge into this one.', function () {
            beforeEach(function () {
                $xml_element = simplexml_load_string('
                    <file name="/src/Example/Namespace/Class.php">
                        <class name="Example\Namespace\Class" namespace="Example\Namespace">
                            <metrics bar="foo" fred="baz"/>
                        </class>
                        <line num="22" type="method" name="__construct" count="1"/>
                        <line num="28" type="stmt" count="1"/>
                        <line num="29" type="stmt" count="0"/>
                        <metrics foo="bar" baz="fred"/>
                    </file>
                ');
                assert($xml_element !== false);
                $this->instance = File::fromXML($xml_element, 'package_name');

                $xml_element = simplexml_load_string('
                    <file name="/src/Example/Namespace/Class.php">
                        <class name="Example\Namespace\OtherClass" namespace="Example\OtherNamespace"/>
                        <line num="28" type="stmt" count="1"/>
                        <line num="29" type="stmt" count="0"/>
                        <line num="30" type="stmt" count="2"/>
                        <metrics foo="barry" baz="freddy"/>
                    </file>
                ');
                assert($xml_element !== false);
                $this->instance->merge(File::fromXML($xml_element, 'other_package_name'));
            });

            it('has the correct package name set.', function () {
                expect($this->instance->getPackageName())->toBe('package_name');
            });

            it('has the correct classes set.', function () {
                $classes = $this->instance->getClasses();
                expect($classes)->toHaveLength(2);

                $keys = $classes->keys();
                expect($keys->toArray())->toBe([
                    'Example\Namespace\OtherClass',
                    'Example\Namespace\Class'
                ]);

                expect($classes->get('Example\Namespace\Class')->getNamespace())->toBe('Example\Namespace');
                expect($classes->get('Example\Namespace\OtherClass')->getNamespace())->toBe('Example\OtherNamespace');
            });

            it('has the correct lines set.', function () {
                $lines = $this->instance->getLines();
                expect($lines)->toHaveLength(4);

                $keys = $lines->keys();
                expect($keys->toArray())->toBe([22, 28, 29, 30]);

                expect($lines->get(22)->getCount())->toBe(1);
                expect($lines->get(28)->getCount())->toBe(2);
                expect($lines->get(29)->getCount())->toBe(0);
                expect($lines->get(30)->getCount())->toBe(2);
            });
        });
    });
});
