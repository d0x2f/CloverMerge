<?php

namespace d0x2f\CloverMerge\Spec;

use d0x2f\CloverMerge\Utilities;

describe('Utilities', function () {
    describe('filesExist', function () {
        describe('Receives a list of paths containing a non existant file first.', function () {
            beforeEach(function () {
                allow('is_file')->toBeCalled()->andReturn(false, true, true);
                $this->result = Utilities::filesExist(new \Ds\Vector(['no', 'yes', 'also yes']));
            });

            it('returns false.', function () {
                expect($this->result)->toBe(false);
            });
        });

        describe('Receives a list of paths containing a non existant file last.', function () {
            beforeEach(function () {
                allow('is_file')->toBeCalled()->andReturn(true, true, false);
                $this->result = Utilities::filesExist(new \Ds\Vector(['yes', 'also yes', 'no']));
            });

            it('returns false.', function () {
                expect($this->result)->toBe(false);
            });
        });

        describe('Receives a list of paths containing no missing files.', function () {
            beforeEach(function () {
                allow('is_file')->toBeCalled()->andReturn(true, true, true);
                $this->result = Utilities::filesExist(new \Ds\Vector(['yes', 'also yes', 'yes again']));
            });

            it('returns true.', function () {
                expect($this->result)->toBe(true);
            });
        });
    });
    describe('logInfo', function () {
        describe('Receives a string to print.', function () {
            beforeEach(function () {
                $this->closure = function () {
                    Utilities::logInfo('test');
                };
            });

            it('prints an info log.', function () {
                expect($this->closure)->toEcho("INFO: test\n");
            });
        });
    });
    describe('logWarning', function () {
        describe('Receives a string to print.', function () {
            beforeEach(function () {
                $this->closure = function () {
                    Utilities::logWarning('test');
                };
            });

            it('prints a warning log.', function () {
                expect($this->closure)->toEcho("WARNING: test\n");
            });
        });
    });
});
