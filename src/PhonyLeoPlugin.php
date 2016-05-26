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

use Eloquent\Phony\Phony;
use Peridot\Leo\Assertion;

/**
 * A Leo plugin for Phony integration.
 */
class PhonyLeoPlugin
{
    /**
     * Install the plugin to the supplied assertion object.
     *
     * @param Assertion $assertion The assertion object.
     */
    public function __invoke(Assertion $assertion)
    {
        $assertion->addMethod('called', function () {
            return $this->getActual()->called();
        });
    }
}
