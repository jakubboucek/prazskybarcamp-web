<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

// Debugging
App\Model\DebugEnabler::setWorkDir(__DIR__ . '/../temp');
$configurator->setDebugMode(App\Model\DebugEnabler::isDebug() ? true : []);
$configurator->enableTracy(__DIR__ . '/../log', 'pan@jakubboucek.cz');
/** @var \Tracy\Logger $logger */
$logger = \Tracy\Debugger::getLogger();
$logger->emailSnooze = '1 hour';
$logger->fromEmail = 'noreply@prazskybarcamp.cz';

// App
$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/../local/config.local.neon');

$container = $configurator->createContainer();

return $container;
