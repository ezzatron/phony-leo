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

use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Mock;
use Eloquent\Phony\Phony;
use Eloquent\Phony\Spy\SpyVerifier;
use InvalidArgumentException;
use Peridot\Leo\Assertion;

/**
 * A Leo plugin for Phony integration.
 */
class PhonyLeo
{
    /**
     * Install the plugin to the supplied assertion object.
     */
    public function __invoke(Assertion $assertion)
    {
        $assertion->addMethod(
            'method',
            function ($name) {
                $actual = $this->getActual();

                if ($actual instanceof InstanceHandle) {
                    $handle = $actual;
                } elseif ($actual instanceof Mock) {
                    $handle = Phony::on($actual);
                } else {
                    throw new InvalidArgumentException(
                        'Actual value for method() must be a mock.'
                    );
                }

                $this->setActual($handle->stub($name));

                return $this;
            }
        );

        $property = function ($name) use ($assertion) {
            $callback = function () use ($name) {
                $actual = $this->getActual();

                if (!$actual instanceof SpyVerifier) {
                    throw new InvalidArgumentException(
                        sprintf('Actual value for %s must be a spy.', $name)
                    );
                }

                $actual->$name();

                return $this;
            };

            $assertion->addProperty($name, $callback);
            $assertion->addMethod($name, $callback);
        };

        $method = function ($name) use ($assertion) {
            $callback = function () use ($name) {
                $actual = $this->getActual();

                if (!$actual instanceof SpyVerifier) {
                    throw new InvalidArgumentException(
                        sprintf('Actual value for %s must be a spy.', $name)
                    );
                }

                call_user_func_array([$actual, $name], func_get_args());

                return $this;
            };

            $assertion->addMethod($name, $callback);
        };

        $verification = function ($name) use ($assertion) {
            $callback = function () use ($name) {
                $actual = $this->getActual();

                if (!$actual instanceof SpyVerifier) {
                    throw new InvalidArgumentException(
                        sprintf('Actual value for %s() must be a spy.', $name)
                    );
                }

                return new PhonyMatcher($name, func_get_args());
            };

            $assertion->addMethod($name, $callback);
        };

        $property('never');
        $property('once');
        $property('twice');
        $property('thrice');
        $property('always');
        $method('times');
        $method('atLeast');
        $method('atMost');
        $method('between');
        $verification('called');
        $verification('calledWith');
    }
}
