<?php

use Eloquent\Phony as x;
use Eloquent\Phony\Leo\PhonyLeo;
use Peridot\Leo\Leo;

describe('Functional tests', function () {
    beforeEach(function () {
        Leo::assertion()->extend(new PhonyLeo());
    });

    it('Returns a verification result on success', function () {
        $spy = x\spy();
        $spy();

        $result = expect($spy)->to->have->been->called();
        expect($result)->to->be->an->instanceof('Eloquent\Phony\Event\EventCollection');
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
            $spy = x\spy();
            $spy();

            expect($spy)->not->to->have->been->called();
        };

        expect($actual)->to->throw(
            'Peridot\Leo\Responder\Exception\AssertionException',
            'Expected no call. Called 1 time(s).'
        );
    });

    it('Rejects invalid actual values', function () {
        $actual = function () {
            expect('a')->to->have->been->called();
        };

        expect($actual)->to->throw('InvalidArgumentException', 'Actual value for called() must be a spy.');
    });

    it('Supports methods with arguments', function () {
        $spy = x\spy();
        $spy('a');

        expect($spy)->to->have->been->calledWith('a');

        $actual = function () {
            $spy = x\spy();
            $spy('a');

            expect($spy)->to->have->been->calledWith('b');
        };

        $expected = <<<'EOD'
Expected call on {spy}[5] with arguments like:
    "b"
Calls:
    - "a"
EOD;

        expect($actual)->to->throw('Peridot\Leo\Responder\Exception\AssertionException', $expected);
    });
});
