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
use Evenement\EventEmitterInterface;
use Peridot\Core\Suite;
use ReflectionFunction;
use ReflectionParameter;

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
        $this->isScalarTypeHintSupported =
            method_exists('ReflectionParameter', 'getType');
    }

    /**
     * Install the plugin.
     */
    public function install()
    {
        $this->emitter->on('suite.define', [$this, 'onSuiteDefine']);
        $this->emitter->on('suite.start', [$this, 'onSuiteStart']);
    }

    /**
     * Uninstall the plugin.
     */
    public function uninstall()
    {
        $this->emitter
            ->removeListener('suite.define', [$this, 'onSuiteDefine']);
        $this->emitter->removeListener('suite.start', [$this, 'onSuiteStart']);
    }

    /**
     * Handle the definition of a suite.
     */
    public function onSuiteDefine(Suite $suite)
    {
        $definition = new ReflectionFunction($suite->getDefinition());
        $parameters = $definition->getParameters();

        if ($parameters) {
            $suite->setDefinitionArguments(
                $this->parameterArguments($parameters)
            );
        }

        $suite->getScope()->peridotAddChildScope(new PhonyScope());
    }

    /**
     * Handle the start of a suite.
     */
    public function onSuiteStart(Suite $suite)
    {
        foreach ($suite->getTests() as $test) {
            $definition = new ReflectionFunction($test->getDefinition());
            $parameters = $definition->getParameters();

            if ($parameters) {
                $test->setDefinitionArguments(
                    $this->parameterArguments($parameters)
                );
            }
        }

        $suite->getScope()->peridotAddChildScope(new PhonyScope());
    }

    private function parameterArguments(array $parameters)
    {
        $arguments = [];

        foreach ($parameters as $parameter) {
            if ($this->isScalarTypeHintSupported) {
                $type = $parameter->getType();

                if (!$type) {
                    $arguments[] = null;

                    continue;
                }

                $typeName = strval($type);
            } elseif ($class = $parameter->getClass()) {
                $typeName = $class->getName();
            } elseif ($parameter->isArray()) {
                $typeName = 'array';
            } elseif ($parameter->isCallable()) {
                $typeName = 'callable';
            } else {
                $arguments[] = null;

                continue;
            }

            switch (strtolower($typeName)) {
                case 'bool':
                    $argument = false;

                    break;

                case 'int':
                    $argument = 0;

                    break;

                case 'float':
                    $argument = .0;

                    break;

                case 'string':
                    $argument = '';

                    break;

                case 'array':
                    $argument = [];

                    break;

                case 'stdclass':
                    $argument = (object) [];

                    break;

                case 'callable':
                    $argument = Phony::stub();

                    break;

                case 'closure':
                    $argument = function () {};

                    break;

                case 'generator':
                    $fn = function () { return; yield; };
                    $argument = $fn();

                    break;

                default:
                    $argument = Phony::mock($typeName)->mock();
            }

            $arguments[] = $argument;
        }

        return $arguments;
    }

    private $emitter;
    private $isScalarTypeHintSupported;
}
