<?php

namespace Moxio\PsalmPlugin;

use Moxio\PsalmPlugin\Hook\AfterMethodCallHandler;
use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\PluginRegistrationSocket;

final class Plugin implements PluginEntryPointInterface
{
    #[\Override]
    public function __invoke(PluginRegistrationSocket $registration, ?\SimpleXMLElement $config = null): void
    {
        require_once __DIR__ . '/Hook/AfterMethodCallHandler.php';
        $registration->registerHooksFromClass(AfterMethodCallHandler::class);
    }
}
