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
     *
     * @param Assertion $assertion The assertion object.
     */
    public function __invoke(Assertion $assertion)
    {
        $assertion->addMethod(
            'method',
            function () {
                $actual = $this->getActual();

                if (!$actual instanceof Mock) {
                    throw new InvalidArgumentException(
                        'Actual value for method() must be a mock.'
                    );
                }
            }
        );

        $verification = function ($method) {
            return function () use ($method) {
                $actual = $this->getActual();

                if (!$actual instanceof SpyVerifier) {
                    throw new InvalidArgumentException(
                        sprintf('Actual value for %s() must be a spy.', $method)
                    );
                }

                if ($this->flag('not')) {
                    $actual->never();
                }

                $arguments = func_get_args();

                try {
                    $result =
                        call_user_func_array([$actual, $method], $arguments);
                } catch (AssertionException $e) {
                    return new PhonyFailureMatcher($e);
                }

                return $result;
            };
        };

        $assertion->addMethod('called', $verification('called'));
        $assertion->addMethod('calledWith', $verification('calledWith'));
    }
}
