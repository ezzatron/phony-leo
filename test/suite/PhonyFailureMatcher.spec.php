<?php

/*
 * This file is part of the Phony for Leo package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Leo;

use Eloquent\Phony as x;
use Eloquent\Phony\Assertion\Exception\AssertionException;
use Peridot\Leo\Assertion;
use Peridot\Leo\Matcher\Match;
use Peridot\Leo\Matcher\Template\ArrayTemplate;
use Peridot\Leo\Responder\ResponderInterface;

describe('PhonyFailureMatcher', function () {
    beforeEach(function () {
        $this->failure = new AssertionException('You done goofed.');
        $this->subject = new PhonyFailureMatcher($this->failure);
    });

    it('isNegated()', function () {
        expect($this->subject->isNegated())->to->be->false();
    });

    it('invert()', function () {
        expect($this->subject->invert())->to->equal($this->subject);
    });

    it('match()', function () {
        expect($this->subject->match('x'))->to->loosely->equal(new Match(false, '', '', false));
    });

    it('getTemplate()', function () {
        expect($this->subject->getTemplate())->to->loosely
            ->equal(new ArrayTemplate(['default' => 'You done goofed.', 'negated' => 'You done goofed.']));
    });

    it('setAssertion()', function (ResponderInterface $responder) {
        $assertion = new Assertion($responder);

        expect($this->subject->setAssertion($assertion))->to->equal($this->subject);
    });

    it('setTemplate()', function () {
        expect($this->subject->setTemplate(new ArrayTemplate([])))->to->equal($this->subject);
    });

    it('getDefaultTemplate()', function () {
        expect($this->subject->getDefaultTemplate())->to->loosely
            ->equal(new ArrayTemplate(['default' => 'You done goofed.', 'negated' => 'You done goofed.']));
    });
});
