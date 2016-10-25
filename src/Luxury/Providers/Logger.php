<?php

namespace Luxury\Providers;

use Luxury\Constants\Services;
use Luxury\Support\Arr;

/**
 * Class Logger
 *
 * @package Luxury\Foundation\Bootstrap
 */
class Logger extends Provider
{
    protected $name = Services::LOGGER;

    protected $shared = true;

    /**
     * Register the logger
     *
     * @return \Phalcon\Logger\AdapterInterface
     */
    protected function register()
    {
        /** @var \Phalcon\Config|\stdClass $config */
        $config = $this->getDI()->getShared(Services::CONFIG);

        switch (ucfirst($adapter = $config->log->adapter ?? 'empty')) {
            case null:
            case 'Multiple':
                $adapter = \Phalcon\Logger\Adapter\File\Multiple::class;

                $name = $config->log->path ?? null;
                break;
            case 'File':
                $adapter = \Phalcon\Logger\Adapter\File::class;

                $name = $config->log->path ?? null;
                break;
            case 'Database':
                $adapter = \Phalcon\Logger\Adapter\Database::class;

                $config->log->options->db = $this->getDI()->getShared(Services::DB);
                $name = $config->log->name ?? 'phalcon';
                break;
            case 'Firelogger':
            case 'Stream':
            case 'Syslog':
            case 'Udplogger':
                $adapter = '\Phalcon\Logger\Adapter\\' . $adapter;

                $name = $config->log->name ?? 'phalcon';
                break;
            default:
                throw new \RuntimeException("Logger adapter $adapter not implemented.");
        }

        if (empty($name)) {
            throw new \RuntimeException('Required parameter {name|path} missing.');
        }

        if (empty($config->log->options)) {
            throw new \RuntimeException('Required parameter {options} missing.');
        }

        return new $adapter($name, $config->log->options->toArray());
    }
}
