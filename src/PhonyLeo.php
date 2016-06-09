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
use ReflectionClass;

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

        $spyVerifier = new ReflectionClass('Eloquent\Phony\Spy\SpyVerifier');

        $method = function ($name) use ($assertion, $spyVerifier) {
            $method = $spyVerifier->getMethod($name);

            $callback = function () use ($name, $method) {
                $actual = $this->getActual();

                if (!$actual instanceof SpyVerifier) {
                    throw new InvalidArgumentException(
                        sprintf('Actual value for %s must be a spy.', $name)
                    );
                }

                $method->invokeArgs($actual, func_get_args());

                return $this;
            };

            $assertion->addMethod($name, $callback);

            if (!$method->getNumberOfRequiredParameters()) {
                $assertion->addProperty($name, $callback);
            }
        };

        $verification = function ($name, $alias = null) use (
            $assertion,
            $spyVerifier
        ) {
            if (!$alias) {
                $alias = $name;
            }

            $method = $spyVerifier->getMethod($name);

            $callback = function () use ($name) {
                $actual = $this->getActual();

                if (!$actual instanceof SpyVerifier) {
                    throw new InvalidArgumentException(
                        sprintf('Actual value for %s() must be a spy.', $name)
                    );
                }

                return new PhonyMatcher($name, func_get_args());
            };

            $assertion->addMethod($alias, $callback);

            if (!$method->getNumberOfRequiredParameters()) {
                $assertion->addProperty($alias, $callback);
            }
        };

        $orderVerification = function ($name, $alias = null) use ($assertion) {
            if (!$alias) {
                $alias = $name;
            }

            $verifier = sprintf('Eloquent\Phony\%sSequence', $name);

            $callback = function () use ($verifier) {
                return new PhonyOrderMatcher(
                    $verifier,
                    $this->getExtendedActual()
                );
            };

            $assertion->addMethod($alias, $callback);
            $assertion->addProperty($alias, $callback);
        };

        $method('never');
        $method('once');
        $method('twice');
        $method('thrice');
        $method('times');
        $method('atLeast');
        $method('atMost');
        $method('between');
        $method('always');
        $verification('called');
        $verification('calledWith');
        $verification('calledOn');
        $verification('responded');
        $verification('completed');
        $verification('returned');
        $verification('threw', 'thrown');
        $verification('generated');
        $verification('traversed');
        $orderVerification('inOrder');
        $orderVerification('anyOrder', 'inAnyOrder');
    }
}
