<?php

namespace App\Service;

use Joli\JoliNotif\Notification;
use Joli\JoliNotif\NotifierFactory;

/**
 * Class OSNotifierService
 * @package App\Service
 */
class OSNotifierService
{
    /**
     * @var \Joli\JoliNotif\Notifier
     */
    protected $notifier;

    /**
     * OSNotifierService constructor.
     */
    public function __construct()
    {
        $this->notifier = NotifierFactory::create();
    }

    /**
     * @param string $title
     * @param string $body
     */
    public function send(string $title, string $body)
    {
        $notification =
            (new Notification())
                ->setTitle($title)
                ->setBody($body)
        ;

        $this->notifier->send($notification);
    }
}
