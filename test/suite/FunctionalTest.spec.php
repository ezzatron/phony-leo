<?php

use Eloquent\Phony as x;
use Eloquent\Phony\Leo\PhonyLeoPlugin;
use Peridot\Leo\Leo;

Leo::assertion()->extend(new PhonyLeoPlugin());

describe('Functional tests', function () {
    it('Throws exceptions on failure', function () {
        $actual = function () {
            expect(x\spy())->to->have->been->called();
        };

        expect($actual)->to->throw('Eloquent\Phony\Assertion\Exception\AssertionException');
    });

    it('Adds the called() method', function () {
        $spy = x\spy();
        $spy();

        expect($spy)->to->have->been->called();
    });
});
