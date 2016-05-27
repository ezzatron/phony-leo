<?php

use Eloquent\Asplode\Asplode;
use Eloquent\Phony\Leo\PhonyPeridot;
use Evenement\EventEmitterInterface;
use Peridot\Reporter\CodeCoverageReporters;

require __DIR__ . '/vendor/autoload.php';

error_reporting(-1);
Asplode::install();

return function (EventEmitterInterface $emitter) {
    $phony = new PhonyPeridot($emitter);
    $phony->install();

    $reporter = new CodeCoverageReporters($emitter);
    $reporter->register();

    $emitter->on('peridot.start', function ($environment) {
        $environment->getDefinition()->getArgument('path')
            ->setDefault('test/suite');
    });

    $emitter->on('code-coverage.start', function ($reporter) {
        $reporter->addDirectoryToWhitelist(__DIR__ . '/src');
    });
};
