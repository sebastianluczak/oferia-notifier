<?php

namespace App\Service;

use Dotenv\Dotenv;

/**
 * Class ConfigService
 * @package App\Service
 */
class ConfigService
{
    /**
     * ConfigService constructor.
     */
    public function __construct()
    {
        $dotenv = new Dotenv(
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR
        );

        $dotenv->load();
    }
}
