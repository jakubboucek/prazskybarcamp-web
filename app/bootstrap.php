<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

require_once __DIR__ . '/model/DebugEnabler.php';
App\Model\DebugEnabler::setWorkDir(__DIR__ . '/../temp');

$configurator->setDebugMode(App\Model\DebugEnabler::isDebug() ? true : []);

$configurator->enableTracy(__DIR__ . '/../log', 'pan@jakubboucek.cz');
\Tracy\Debugger::getLogger()->emailSnooze = '1 hour';

$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

return $container;
