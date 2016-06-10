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

use Eloquent\Phony\Event\EventCollection;
use Peridot\Leo\Assertion;

/**
 * A Leo wrapper for Phony results.
 */
class PhonyResult
{
    public function __construct(Assertion $assertion, EventCollection $result)
    {
        $this->assertion = $assertion;
        $this->result = $result;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function __get($property)
    {
        return $this->assertion->$property;
    }

    public function __call($method, array $arguments)
    {
        if (method_exists($this->result, $method)) {
            $subject = $this->result;
        } else {
            $subject = $this->assertion;
        }

        return call_user_func_array([$subject, $method], $arguments);
    }

    private $assertion;
    private $result;
}
