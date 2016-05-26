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
use Peridot\Leo\Matcher\AbstractMatcher;
use Peridot\Leo\Matcher\Template\ArrayTemplate;

/**
 * A Leo wrapper for Phony verification failures.
 */
class PhonyFailureMatcher extends AbstractMatcher
{
    /**
     * Construct a new Phony failure matcher.
     *
     * @param AssertionException $failure The failure.
     */
    public function __construct(AssertionException $failure)
    {
        $this->failure = $failure;
    }

    /**
     * Always returns false.
     *
     * @return false
     */
    public function doMatch($actual)
    {
        return false;
    }

    /**
     * Get a dummy template containing the original verification failure message.
     *
     * @return ArrayTemplate The template.
     */
    public function getDefaultTemplate()
    {
        $message = $this->failure->getMessage();

        return new ArrayTemplate(
            ['default' => $message, 'negated' => $message]
        );
    }

    private $failure;
}
