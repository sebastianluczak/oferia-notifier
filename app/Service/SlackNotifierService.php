<?php

namespace App\Service;

use Maknz\Slack\Client;

/**
 * Class SlackNotifierService
 * @package App\Service
 */
class SlackNotifierService
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * SlackNotifierService constructor.
     */
    public function __construct()
    {
        $settings = [
            'username'   => getenv('SLACK_USER'),
            'channel'    => getenv('SLACK_CHANNEL'),
            'link_names' => true,
        ];

        $this->client = new Client(
            getenv('SLACK_HOOK'),
            $settings
        );
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param string $message
     */
    public function send(string $message)
    {
        $this->getClient()->send($message);
    }
}
