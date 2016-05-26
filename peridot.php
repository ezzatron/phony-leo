<?php

use Eloquent\Asplode\Asplode;
use Evenement\EventEmitterInterface;
use Peridot\Reporter\CodeCoverageReporters;

require __DIR__ . '/vendor/autoload.php';

error_reporting(-1);
Asplode::install();

return function (EventEmitterInterface $emitter) {
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
