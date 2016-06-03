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

        $property = function ($name) {
            return function () use ($name) {
                $actual = $this->getActual();

                if (!$actual instanceof SpyVerifier) {
                    throw new InvalidArgumentException(
                        sprintf('Actual value for %s must be a spy.', $name)
                    );
                }

                $actual->$name();

                return $this;
            };
        };

        $verification = function ($name) {
            return function () use ($name) {
                $actual = $this->getActual();

                if (!$actual instanceof SpyVerifier) {
                    throw new InvalidArgumentException(
                        sprintf('Actual value for %s() must be a spy.', $name)
                    );
                }

                if ($this->flag('not')) {
                    $actual->never();
                }

                $arguments = func_get_args();
                $error = null;

                try {
                    $result =
                        call_user_func_array([$actual, $name], $arguments);
                } catch (AssertionException $error) {
                    // cleanup first
                }

                $this->clearFlags();

                if ($error) {
                    return new PhonyFailureMatcher($error);
                }

                return $result;
            };
        };

        $assertion->addProperty('never', $property('never'));
        $assertion->addProperty('once', $property('once'));
        $assertion->addProperty('twice', $property('twice'));
        $assertion->addProperty('thrice', $property('thrice'));
        $assertion->addMethod('called', $verification('called'));
        $assertion->addMethod('calledWith', $verification('calledWith'));
    }
}
