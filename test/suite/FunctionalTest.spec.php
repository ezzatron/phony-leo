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

        expect($actual)->to->be->an->instanceof('Eloquent\Phony\Event\EventCollection');
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

    it('Does not support negation for order verifications', function () {
        $actual = function () {
            expect('a')->to->not->be->inOrder();
        };

        expect($actual)->to->throw('RuntimeException', 'Order verifications do not support negation.');
    });

    describe('Cardinality', function () {
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

    describe('Verifications', function () {
        it('Supports called()', function () {
            $spy = x\spy();
            $spy();

            expect($spy)->to->have->been->called();
            expect($spy)->to->have->been->called;

            $actual = function () {
                expect(x\spy()->setLabel('label'))->to->have->been->called();
            };

            expect($actual)->to->throw(
                'Peridot\Leo\Responder\Exception\AssertionException',
                'Expected call. Never called.'
            );
        });

        it('Supports calledWith()', function () {
            $spy = x\spy();
            $spy('a');
            $spy();

            expect($spy)->to->have->been->calledWith('a');
            expect($spy)->to->have->been->calledWith();
            expect($spy)->to->have->been->calledWith;

            $actual = function () {
                expect(x\spy()->setLabel('label'))->to->have->been->calledWith();
            };
            $expected = <<<'EOD'
Expected call on {spy}[label] with arguments like:
    <none>
Never called.
EOD;

            expect($actual)->to->throw('Peridot\Leo\Responder\Exception\AssertionException', $expected);
        });

        it('Supports calledOn()', function () {
            $object = (object) [];
            $closure = function () {};
            $closure = $closure->bindTo($object);
            $spy = x\spy($closure);
            $spy();

            expect($spy)->to->have->been->calledOn($object);

            $actual = function () {
                expect(x\spy()->setLabel('label'))->to->have->been->calledOn((object) []);
            };

            expect($actual)->to->throw(
                'Peridot\Leo\Responder\Exception\AssertionException',
                'Expected call on supplied object. Never called.'
            );
        });

        it('Supports responded()', function () {
            $spy = x\spy();
            $spy();

            expect($spy)->to->have->responded();
            expect($spy)->to->have->responded;

            $actual = function () {
                expect(x\spy()->setLabel('label'))->to->have->responded();
            };

            expect($actual)->to->throw(
                'Peridot\Leo\Responder\Exception\AssertionException',
                'Expected call on {spy}[label] to respond. Never called.'
            );
        });

        it('Supports completed()', function () {
            $spy = x\spy();
            $spy();

            expect($spy)->to->have->completed();
            expect($spy)->to->have->completed;

            $actual = function () {
                expect(x\spy()->setLabel('label'))->to->have->completed();
            };

            expect($actual)->to->throw(
                'Peridot\Leo\Responder\Exception\AssertionException',
                'Expected call on {spy}[label] to complete. Never called.'
            );
        });

        it('Supports returned()', function () {
            $spy = x\spy(function () {
                return 'a';
            });
            $spy();

            expect($spy)->to->have->returned('a');
            expect($spy)->to->have->returned();
            expect($spy)->to->have->returned;

            $actual = function () {
                expect(x\spy()->setLabel('label'))->to->have->returned();
            };

            expect($actual)->to->throw(
                'Peridot\Leo\Responder\Exception\AssertionException',
                'Expected call on {spy}[label] to return. Never called.'
            );
        });

        it('Supports thrown()', function () {
            $spy = x\spy(function () {
                throw new RuntimeException('You done goofed.');
            });
            try {
                $spy();
            } catch (RuntimeException $e) {
            }

            expect($spy)->to->have->thrown('RuntimeException');
            expect($spy)->to->have->thrown();
            expect($spy)->to->have->thrown;

            $actual = function () {
                expect(x\spy()->setLabel('label'))->to->have->thrown();
            };

            expect($actual)->to->throw(
                'Peridot\Leo\Responder\Exception\AssertionException',
                'Expected call on {spy}[label] to throw. Never called.'
            );
        });

        it('Supports traversed()', function () {
            $spy = x\spy(function () {
                return [];
            });
            $spy();

            expect($spy)->to->have->traversed();
            $result = expect($spy)->to->have->traversed;
            expect($result)->to->be->an->instanceof('Eloquent\Phony\Verification\TraversableVerifier');

            $actual = function () {
                expect(x\spy()->setLabel('label'))->to->have->traversed();
            };

            expect($actual)->to->throw(
                'Peridot\Leo\Responder\Exception\AssertionException',
                'Expected call on {spy}[label] to be traversable. Never called.'
            );
        });

        if (class_exists('Generator')) {
            it('Supports generated()', function () {
                $spy = x\spy(function () {
                    return;
                    yield;
                });
                $spy();

                expect($spy)->to->have->generated();
                $result = expect($spy)->to->have->generated;
                expect($result)->to->be->an->instanceof('Eloquent\Phony\Verification\GeneratorVerifier');

                $actual = function () {
                    expect(x\spy()->setLabel('label'))->to->have->generated();
                };

                expect($actual)->to->throw(
                    'Peridot\Leo\Responder\Exception\AssertionException',
                    'Expected call on {spy}[label] to generate. Never called.'
                );
            });
        }
    });

    describe('Order verifications', function () {
        it('Supports inOrder()', function () {
            $spy = x\spy();
            $spy('a');
            $spy('b');

            expect(
                expect($spy)->to->have->been->calledWith('a'),
                expect($spy)->to->have->been->calledWith('b')
            )->to->be->inOrder();
            expect(
                expect($spy)->to->have->been->calledWith('a'),
                expect($spy)->to->have->been->calledWith('b')
            )->to->be->inOrder;

            $actual = function () {
                $spy = x\spy();
                $spy('a');
                $spy('b');

                expect(
                    expect($spy)->to->have->been->calledWith('b'),
                    expect($spy)->to->have->been->calledWith('a')
                )->to->be->inOrder();
            };

            $expected = <<<'EOD'
Expected events in order:
    - called {spy}[44]("b")
    - called {spy}[44]("a")
Order:
    - called {spy}[44]("a")
    - called {spy}[44]("b")
EOD;

            expect($actual)->to->throw('Peridot\Leo\Responder\Exception\AssertionException', $expected);
        });

        it('Supports inAnyOrder()', function () {
            $spy = x\spy();
            $spy('b');
            $spy('a');

            expect(
                expect($spy)->to->have->been->calledWith('a'),
                expect($spy)->to->have->been->calledWith('b')
            )->to->be->inAnyOrder();
            expect(
                expect($spy)->to->have->been->calledWith('a'),
                expect($spy)->to->have->been->calledWith('b')
            )->to->be->inAnyOrder;
        });

        it('Supports nested order verification', function () {
            $spy = x\spy();
            $spy('a');
            $spy('b');
            $spy('c');
            $spy('d');

            $bc = expect(
                expect($spy)->to->have->been->calledWith('b'),
                expect($spy)->to->have->been->calledWith('c')
            )->to->be->inOrder();

            $abc = expect(
                $bc,
                expect($spy)->to->have->been->calledWith('a')
            )->to->be->inAnyOrder();

            $bcd = expect(
                expect($spy)->to->have->been->calledWith('d'),
                $bc
            )->to->be->inAnyOrder();

            expect($abc, $bcd)->to->be->inOrder();
        });
    });
});
