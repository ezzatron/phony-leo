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

use Peridot\Leo\Matcher\Template\TemplateInterface;

describe('PhonyMatcher', function () {
    beforeEach(function () {
        $this->subject = new PhonyMatcher('name', [], false);
    });

    it('negation', function () {
        expect($this->subject->isNegated())->to->be->false();

        $this->subject->invert();

        expect($this->subject->isNegated())->to->be->true();
    });

    it('unused template methods', function (TemplateInterface $template) {
        expect($this->subject->setTemplate($template))->to->equal($this->subject);
        expect($this->subject->getTemplate())->to->be->an->instanceof('Peridot\Leo\Matcher\Template\ArrayTemplate');
        expect($this->subject->getDefaultTemplate())->to->equal($this->subject->getTemplate());
    });
});
