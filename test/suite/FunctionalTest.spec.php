<?php

use Eloquent\Phony\Leo\PhonyLeo;
use Peridot\Leo\Leo;

describe('Functional tests', function () {
    beforeEach(function () {
        Leo::assertion()->extend(new PhonyLeo());
    });

    it('Returns a verification result on success', function () {
        $spy = $this->spy();
        $spy();

        $result = expect($spy)->to->have->been->called();
        expect($result)->to->be->an->instanceof('Eloquent\Phony\Event\EventCollection');
    });

    it('Throws exceptions on failure', function () {
        $actual = function () {
            expect($this->spy())->to->have->been->called();
        };

        expect($actual)->to->throw(
            'Peridot\Leo\Responder\Exception\AssertionException',
            'Expected call. Never called.'
        );
    });

    it('Supports negation', function () {
        expect($this->spy())->not->to->have->been->called();
    });

    it('Supports negation failures', function () {
        $actual = function () {
            $spy = $this->spy();
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
        $spy = $this->spy();
        $spy('a');

        expect($spy)->to->have->been->calledWith('a');

        $actual = function () {
            $spy = $this->spy()->setLabel('label');
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

    it('Returns a method stub from method()', function () {
        $mock = $this->mock(['a' => function () {}]);

        expect($mock)->method('a')->to->be->an->instanceof('Eloquent\Phony\Spy\SpyVerifier');
        expect($mock->mock())->method('a')->to->be->an->instanceof('Eloquent\Phony\Spy\SpyVerifier');
    });

    it('Throws an exception from method() when the method is undefined', function () {
        $actual = function () {
            expect($this->mockBuilder()->named('PhonyLeoUndefinedMethodFailureSpec')->get())->method('a');
        };

        expect($actual)->to->throw(
            'Eloquent\Phony\Mock\Exception\UndefinedMethodStubException',
            'The requested method stub PhonyLeoUndefinedMethodFailureSpec::a() does not exist.'
        );
    });
});
