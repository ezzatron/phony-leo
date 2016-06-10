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
                        'Unsupported actual value for method().'
                    );
                }

                $this->setActual($handle->stub($name));

                return $this;
            }
        );

        $method = function ($name, $types, $addProperty) use ($assertion) {
            $callback = function () use ($name, $types) {
                $actual = $this->getActual();
                $isValidType = false;

                foreach ($types as $type) {
                    if ($actual instanceof $type) {
                        $isValidType = true;

                        break;
                    }
                }

                if (!$isValidType) {
                    throw new InvalidArgumentException(
                        sprintf('Unsupported actual value for %s().', $name)
                    );
                }

                call_user_func_array([$actual, $name], func_get_args());

                return $this;
            };

            $assertion->addMethod($name, $callback);

            if ($addProperty) {
                $assertion->addProperty($name, $callback);
            }
        };

        $verification = function (
            $name,
            $alias,
            $types,
            $addProperty,
            $isChained
        ) use (
            $assertion
        ) {
            if (!$alias) {
                $alias = $name;
            }

            $callback = function () use ($name, $types, $isChained) {
                $actual = $this->getActual();
                $isValidType = false;

                foreach ($types as $type) {
                    if ($actual instanceof $type) {
                        $isValidType = true;

                        break;
                    }
                }

                if (!$isValidType) {
                    throw new InvalidArgumentException(
                        sprintf('Unsupported actual value for %s().', $name)
                    );
                }

                $matcher = new PhonyMatcher($name, func_get_args(), $isChained);
                $matcher->setAssertion($this);

                return $matcher;
            };

            $assertion->addMethod($alias, $callback);

            if ($addProperty) {
                $assertion->addProperty($alias, $callback);
            }
        };

        $orderVerification = function ($name, $alias = null) use ($assertion) {
            if (!$alias) {
                $alias = $name;
            }

            $verifier = sprintf('Eloquent\Phony\%sSequence', $name);

            $callback = function () use ($verifier) {
                $matcher = new PhonyOrderMatcher(
                    $verifier,
                    $this->getExtendedActual()
                );
                $matcher->setAssertion($this);

                return $matcher;
            };

            $assertion->addMethod($alias, $callback);
            $assertion->addProperty($alias, $callback);
        };

        $spyOnly = ['Eloquent\Phony\Spy\SpyVerifier'];
        $traversableOnly = ['Eloquent\Phony\Verification\TraversableVerifier'];
        $generatorOnly = ['Eloquent\Phony\Verification\GeneratorVerifier'];
        $spyOrTraversable = array_merge($spyOnly, $traversableOnly);
        $spyOrGenerator = array_merge($spyOnly, $generatorOnly);

        // name, types, addProperty
        $method('never', $spyOrTraversable, true);
        $method('once', $spyOrTraversable, true);
        $method('twice', $spyOrTraversable, true);
        $method('thrice', $spyOrTraversable, true);
        $method('times', $spyOrTraversable, false);
        $method('atLeast', $spyOrTraversable, false);
        $method('atMost', $spyOrTraversable, false);
        $method('between', $spyOrTraversable, false);
        $method('always', $spyOrTraversable, true);

        // name, alias, types, addProperty, isChained
        $verification('called', null, $spyOnly, true, false);
        $verification('calledWith', null, $spyOnly, true, false);
        $verification('calledOn', null, $spyOnly, false, false);
        $verification('responded', null, $spyOnly, true, false);
        $verification('completed', null, $spyOnly, true, false);
        $verification('returned', null, $spyOrGenerator, true, false);
        $verification('threw', 'thrown', $spyOrGenerator, true, false);
        $verification('generated', null, $spyOnly, true, true);
        $verification('traversed', null, $spyOnly, true, true);
        $verification('used', null, $traversableOnly, true, false);
        $verification('produced', null, $traversableOnly, true, false);
        $verification('consumed', null, $traversableOnly, true, false);
        $verification('received', null, $generatorOnly, true, false);
        $verification('receivedException', null, $generatorOnly, true, false);

        $orderVerification('inOrder');
        $orderVerification('anyOrder', 'inAnyOrder');
    }
}
