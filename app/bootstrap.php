<?php /** @noinspection PhpUnhandledExceptionInspection */

use JakubBoucek\DebugEnabler\DebugEnabler;

require __DIR__ . '/../vendor/autoload.php';

$tempDir = __DIR__ . '/../temp';
$configurator = new Nette\Configurator;


// Debugging
DebugEnabler::setWorkDir($tempDir);
$configurator->setDebugMode(DebugEnabler::isDebug());
$configurator->enableTracy(__DIR__ . '/../log', 'pan@jakubboucek.cz');

/** @var \Tracy\Logger $logger */
$logger = \Tracy\Debugger::getLogger();
$logger->emailSnooze = '1 hour';
$logger->fromEmail = 'noreply@prazskybarcamp.cz';

// App
$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory($tempDir);

$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/../local/config.local.neon');

$container = $configurator->createContainer();

return $container;
