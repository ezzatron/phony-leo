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

use Eloquent\Phony\Assertion\Exception\AssertionException;

describe('PhonyFailureMatcher', function () {
    beforeEach(function () {
        $this->failure = new AssertionException('You done goofed.');
        $this->subject = new PhonyFailureMatcher($this->failure);
    });

    it('isNegated()', function () {
        expect($this->subject->isNegated())->to->be->false();
    });
});
