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
use Peridot\Scope\Scope;

/**
 * A Peridot scope for Phony integration.
 */
class PhonyScope extends Scope
{
    public function mockBuilder($types = [])
    {
        return Phony::mockBuilder($types);
    }

    public function mock($types = [])
    {
        return Phony::mock($types);
    }

    public function spy($callback = null)
    {
        return Phony::spy($callback = null);
    }
}
