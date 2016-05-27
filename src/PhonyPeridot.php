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
    }

    /**
     * Uninstall the plugin.
     */
    public function uninstall()
    {
        $this->emitter->removeListener('suite.define', [$this, 'onSuiteDefine']);
    }

    /**
     * Handle the definition of a suite.
     */
    public function onSuiteDefine(Suite $suite)
    {
        $definition = new ReflectionFunction($suite->getDefinition());
        $parameters = $definition->getParameters();

        if ($parameters) {
            $arguments = [];

            foreach ($parameters as $parameter) {
                $arguments[] = $this->parameterArgument($parameter);
            }

            $suite->setDefinitionArguments($arguments);
        }

        $suite->getScope()->peridotAddChildScope(new PhonyScope());
    }

    private function parameterArgument(ReflectionParameter $parameter)
    {
        if ($this->isScalarTypeHintSupported) {
            $type = $parameter->getType();

            if (!$type) {
                return null;
            }

            $typeName = strval($type);
        } else {
            if ($class = $parameter->getClass()) {
                $typeName = $class->getName();
            } elseif ($parameter->isArray()) {
                $typeName = 'array';
            } elseif ($parameter->isCallable()) {
                $typeName = 'callable';
            }

            return null;
        }

        switch (strtolower($typeName)) {
            case 'bool': return false;
            case 'int': return 0;
            case 'float': return .0;
            case 'string': return '';
            case 'array': return array();
            case 'stdclass': return (object) array();

            case 'callable':
                return Phony::stub();

            case 'closure':
                return function () {};

            case 'generator':
                $fn = function () { return; yield; };

                return $fn();
        }

        return Phony::mock($typeName)->mock();
    }

    private $emitter;
    private $isScalarTypeHintSupported;
}
