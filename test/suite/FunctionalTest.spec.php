<?php

/*
 * This file is part of the Phony for Leo package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

use Eloquent\Phony as x;
use Eloquent\Phony\Leo\PhonyLeo;
use Peridot\Leo\Leo;

describe('Functional tests', function () {
    beforeEach(function () {
        Leo::assertion()->extend(new PhonyLeo());
    });

    it('Returns a Phony matcher on success', function () {
        $spy = x\spy();
        $spy();
        $actual = expect($spy)->to->have->been->called();

        expect($actual)->to->be->an->instanceof('Eloquent\Phony\Leo\PhonyMatcher');
        expect($actual->getResult())->to->be->an->instanceof('Eloquent\Phony\Event\EventCollection');
    });

    it('Throws exceptions on failure', function () {
        $actual = function () {
            expect(x\spy())->to->have->been->called();
        };

        expect($actual)->to->throw(
            'Peridot\Leo\Responder\Exception\AssertionException',
            'Expected call. Never called.'
        );
    });

    it('Supports negation', function () {
        expect(x\spy())->not->to->have->been->called();
    });

    it('Supports negation failures', function () {
        $actual = function () {
            $spy = x\spy()->setLabel('label');
            $spy();

            expect($spy)->not->to->have->been->called();
        };

        $expected = <<<'EOD'
Expected no call. Calls:
    - {spy}[label]()
EOD;

        expect($actual)->to->throw('Peridot\Leo\Responder\Exception\AssertionException', $expected);
    });

    it('Supports verifications with arguments', function () {
        $spy = x\spy();
        $spy('a');

        expect($spy)->to->have->been->calledWith('a');

        $actual = function () {
            $spy = x\spy()->setLabel('label');
            $spy('a');

            expect($spy)->to->have->been->calledWith('b');
        };

        $expected = <<<'EOD'
Expected call on {spy}[label] with arguments like:
    "b"
Calls:
    - "a"
EOD;

        expect($actual)->to->throw('Peridot\Leo\Responder\Exception\AssertionException', $expected);
    });

    it('Supports spies retreived from mocks', function () {
        $handle = x\mock(['a' => function () {}]);
        $mock = $handle->mock();
        $mock->a();

        expect($handle->a)->to->have->been->called();
        expect($handle)->method('a')->to->have->been->called();
        expect($mock)->method('a')->to->have->been->called();
    });

    it('Rejects invalid actual values for properties', function () {
        $actual = function () {
            expect('a')->to->never->have->been->called();
        };

        expect($actual)->to->throw('InvalidArgumentException', 'Actual value for never must be a spy.');
    });

    it('Rejects invalid actual values for verifications', function () {
        $actual = function () {
            expect('a')->to->have->been->called();
        };

        expect($actual)->to->throw('InvalidArgumentException', 'Actual value for called() must be a spy.');
    });

    it('Rejects invalid actual values for method()', function () {
        $actual = function () {
            expect('a')->method('b');
        };

        expect($actual)->to->throw('InvalidArgumentException', 'Actual value for method() must be a mock.');
    });

    describe('Cardinality methods', function () {
        it('Supports never()', function () {
            $spy = x\spy();

            expect($spy)->to->never->have->been->called();
            expect($spy)->to->never()->have->been->called();

            $actual = function () {
                $spy = x\spy()->setLabel('label');
                $spy();

                expect($spy)->to->never->have->been->called();
            };

        $expected = <<<'EOD'
Expected no call. Calls:
    - {spy}[label]()
EOD;

            expect($actual)->to->throw('Peridot\Leo\Responder\Exception\AssertionException', $expected);
        });

        it('Supports once()', function () {
            $spy = x\spy();
            $spy();

            expect($spy)->to->once->have->been->called();
            expect($spy)->to->once()->have->been->called();

            $actual = function () {
                expect(x\spy()->setLabel('label'))->to->once()->have->been->called();
            };

            expect($actual)->to->throw(
                'Peridot\Leo\Responder\Exception\AssertionException',
                'Expected call, exactly 1 time. Never called.'
            );
        });

        it('Supports twice()', function () {
            $spy = x\spy();
            $spy();
            $spy();

            expect($spy)->to->twice->have->been->called();
            expect($spy)->to->twice()->have->been->called();

            $actual = function () {
                expect(x\spy()->setLabel('label'))->to->twice()->have->been->called();
            };

            expect($actual)->to->throw(
                'Peridot\Leo\Responder\Exception\AssertionException',
                'Expected call, exactly 2 times. Never called.'
            );
        });

        it('Supports thrice()', function () {
            $spy = x\spy();
            $spy();
            $spy();
            $spy();

            expect($spy)->to->thrice->have->been->called();
            expect($spy)->to->thrice()->have->been->called();

            $actual = function () {
                expect(x\spy()->setLabel('label'))->to->thrice()->have->been->called();
            };

            expect($actual)->to->throw(
                'Peridot\Leo\Responder\Exception\AssertionException',
                'Expected call, exactly 3 times. Never called.'
            );
        });

        it('Supports times()', function () {
            $spy = x\spy();
            $spy();
            $spy();
            $spy();
            $spy();

            expect($spy)->to->times(4)->have->been->called();

            $actual = function () {
                expect(x\spy()->setLabel('label'))->to->times(4)->have->been->called();
            };

            expect($actual)->to->throw(
                'Peridot\Leo\Responder\Exception\AssertionException',
                'Expected call, exactly 4 times. Never called.'
            );
        });

        it('Supports atLeast()', function () {
            $spy = x\spy();
            $spy();

            expect($spy)->to->atLeast(1)->have->been->called();

            $actual = function () {
                expect(x\spy()->setLabel('label'))->to->atLeast(2)->have->been->called();
            };

            expect($actual)->to->throw(
                'Peridot\Leo\Responder\Exception\AssertionException',
                'Expected call, 2 times. Never called.'
            );
        });

        it('Supports atMost()', function () {
            $spy = x\spy();

            expect($spy)->to->atMost(1)->have->been->called();

            $actual = function () {
                $spy = x\spy()->setLabel('label');
                $spy();
                $spy();

                expect($spy)->to->atMost(1)->have->been->called();
            };

        $expected = <<<'EOD'
Expected call, up to 1 time. Calls:
    - {spy}[label]()
    - {spy}[label]()
EOD;

            expect($actual)->to->throw('Peridot\Leo\Responder\Exception\AssertionException', $expected);
        });

        it('Supports between()', function () {
            $spy = x\spy();
            $spy();

            expect($spy)->to->between(1, 2)->have->been->called();

            $actual = function () {
                $spy = x\spy()->setLabel('label');
                $spy();
                $spy();
                $spy();

                expect($spy)->to->between(1, 2)->have->been->called();
            };

        $expected = <<<'EOD'
Expected call, between 1 and 2 times. Calls:
    - {spy}[label]()
    - {spy}[label]()
    - {spy}[label]()
EOD;

            expect($actual)->to->throw('Peridot\Leo\Responder\Exception\AssertionException', $expected);
        });

        it('Supports always()', function () {
            $spy = x\spy();
            $spy('a');

            expect($spy)->to->always->have->been->calledWith('a');
            expect($spy)->to->always()->have->been->calledWith('a');

            $actual = function () {
                $spy = x\spy()->setLabel('label');

                expect($spy)->to->always()->have->been->calledWith('a');
            };

        $expected = <<<'EOD'
Expected every call on {spy}[label] with arguments like:
    "a"
Never called.
EOD;

            expect($actual)->to->throw('Peridot\Leo\Responder\Exception\AssertionException', $expected);
        });
    });
});
