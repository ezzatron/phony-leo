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

use Evenement\EventEmitterInterface;
use Peridot\Core\Suite;

/**
 * A Peridot plugin for Phony integration.
 */
class PhonyPeridot
{
    /**
     * Construct a new Phony Peridot plugin.
     */
    public function __construct(EventEmitterInterface $emitter)
    {
        $this->emitter = $emitter;
    }

    /**
     * Install the plugin.
     */
    public function install()
    {
        $this->emitter->on('suite.start', [$this, 'onSuiteStart']);
    }

    /**
     * Uninstall the plugin.
     */
    public function uninstall()
    {
        $this->emitter->removeListener('suite.start', [$this, 'onSuiteStart']);
    }

    /**
     * Handle the start of a suite.
     */
    public function onSuiteStart(Suite $suite)
    {
        $suite->getScope()->peridotAddChildScope(new PhonyScope());
    }

    private $emitter;
}
