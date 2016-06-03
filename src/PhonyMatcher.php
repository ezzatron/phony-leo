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
use Peridot\Leo\Assertion;
use Peridot\Leo\Matcher\Match;
use Peridot\Leo\Matcher\MatcherInterface;
use Peridot\Leo\Matcher\Template\ArrayTemplate;
use Peridot\Leo\Matcher\Template\TemplateInterface;

/**
 * A Leo wrapper for Phony verifications.
 */
class PhonyMatcher implements MatcherInterface
{
    /**
     * Construct a new Phony matcher.
     */
    public function __construct($name, array $arguments)
    {
        $this->name = $name;
        $this->arguments = $arguments;
        $this->isNegated = false;
        $this->template = new ArrayTemplate([]);
    }

    public function isNegated()
    {
        return $this->isNegated;
    }

    public function invert()
    {
        $this->isNegated = !$this->isNegated;

        return $this;
    }

    public function match($actual)
    {
        if ($this->isNegated) {
            $actual->never();
        }

        try {
            call_user_func_array([$actual, $this->name], $this->arguments);
            $isMatch = true;
        } catch (AssertionException $error) {
            $isMatch = false;
            $message = $error->getMessage();
            $this->template->setDefaultTemplate($message);
            $this->template->setNegatedTemplate($message);
        }

        return new Match($isMatch, null, null, $this->isNegated);
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setAssertion(Assertion $assertion)
    {
        return $this;
    }

    public function setTemplate(TemplateInterface $template)
    {
        return $this;
    }

    public function getDefaultTemplate()
    {
        return $this->template;
    }

    private $name;
    private $arguments;
    private $isNegated;
    private $template;
}
