<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Logger;

use Monolog\Logger as MonologLogger;
use Virtua\FreshMail\Model\System\Config;

class Logger extends MonologLogger
{
    private $config;

    public function __construct(
        Config $config,
        $name,
        array $handlers = array(),
        array $processors = array()
    ) {
        $this->config = $config;
        parent::__construct($name, $handlers, $processors);
    }

    public function logIfDebugModeOn(string $message, array $context = array())
    {
        if ($this->config->IsDebugMode()) {
            $this->debug($message, $context);
        }
    }
}
