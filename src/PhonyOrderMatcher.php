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
use RuntimeException;

/**
 * A Leo wrapper for Phony order verifications.
 */
class PhonyOrderMatcher implements MatcherInterface
{
    /**
     * Construct a new Phony matcher.
     */
    public function __construct($verifier, array $results)
    {
        $this->verifier = $verifier;
        $this->results = $results;
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
            throw new RuntimeException(
                'Order verifications do not support negation.'
            );
        }

        $result = null;

        try {
            $result = call_user_func($this->verifier, $this->results);
            $isMatch = true;
        } catch (AssertionException $error) {
            $isMatch = false;
            $message = $error->getMessage();
            $this->template->setDefaultTemplate($message);
            $this->template->setNegatedTemplate($message);
        }

        return new Match($isMatch, null, null, $this->isNegated, $result);
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

    private $verifier;
    private $results;
    private $isNegated;
    private $template;
}
