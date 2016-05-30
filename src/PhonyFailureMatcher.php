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
 * A Leo wrapper for Phony verification failures.
 */
class PhonyFailureMatcher implements MatcherInterface
{
    /**
     * Construct a new Phony failure matcher.
     */
    public function __construct(AssertionException $failure)
    {
        $message = $failure->getMessage();
        $this->template =
            new ArrayTemplate(['default' => $message, 'negated' => $message]);
    }

    /**
     * Always returns false.
     *
     * @return false
     */
    public function isNegated()
    {
        return false;
    }

    /**
     * Does nothing.
     */
    public function invert()
    {
        return $this;
    }

    /**
     * Return a dummy match.
     */
    public function match($actual)
    {
        return new Match(false, '', '', false);
    }

    /**
     * Return a dummy template.
     *
     * @return ArrayTemplate The template.
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Does nothing.
     */
    public function setAssertion(Assertion $assertion)
    {
        return $this;
    }

    /**
     * Does nothing.
     */
    public function setTemplate(TemplateInterface $template)
    {
        return $this;
    }

    /**
     * Return a dummy template.
     *
     * @return ArrayTemplate The template.
     */
    public function getDefaultTemplate()
    {
        return $this->template;
    }

    private $template;
}
