<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Joli\JoliNotif\Notification;
use Joli\JoliNotif\NotifierFactory;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Dom\HtmlNode;
use Medoo\Medoo;

class Main extends Command
{
    /**
     * @var Medoo
     */
    protected $database;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'main';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The main app command';

    public function __construct()
    {
        $this->database = new Medoo([
            'database_type' => 'mysql',
            'database_name' => 'oferia_notifier',
            'server'        => 'localhost',
            'username'      => 'root',
            'password'      => 'Ne3xvm86',
        ]);

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $offersFromFirstPage = $this->loadAllOffers();

        $this->processOffers($offersFromFirstPage);

        $offersFromSecond = $this->loadAllOffers($secondPage = true);

        $this->processOffers($offersFromSecond);
    }

    /**
     * @param string $title
     * @param string $body
     */
    protected function notify(string $title, string $body)
    {
        $notifier = NotifierFactory::create();

        $notification =
            (new Notification())
                ->setTitle($title)
                ->setBody($body)
        ;

        $notifier->send($notification);
    }

    /**
     * @param bool $secondPage
     * @return string
     */
    protected function loadURL(bool $secondPage = false)
    {
        $dom = new Dom;
        $url = 'http://oferia.pl/zlecenia/programowanie-it';
        if ($secondPage) {
            $url = $url . '?strona=2';
        }
        $dom->loadFromUrl($url);
        $html = $dom->outerHtml;

        return $html;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->signature;
    }

    /**
     * @param bool $secondPage
     * @return Dom\Collection|HtmlNode
     * @throws \Exception
     */
    protected function loadAllOffers(bool $secondPage = false): Dom\Collection
    {
        $html = $this->loadURL($secondPage);
        $dom = new Dom;
        $dom->load($html);
        /** @var HtmlNode $t */
        $t = $dom->find('h2[class="listing-order-name"]');

        if ($t) {
            return $t;
        } else {
            throw new \Exception('No offers!');
        }
    }

    /**
     * @param Dom\Collection|HtmlNode $offers
     */
    protected function processOffers(Dom\Collection $offers)
    {
        foreach ($offers as $item) {
            $offerLink = $item->find('a')[0];
            if ($offerLink) {
                $this->handleOfferLink($offerLink);
            }
        }
    }

    /**
     * @param HtmlNode $offerLink
     */
    protected function handleOfferLink(HtmlNode $offerLink)
    {
        if (!$this->offerExists($offerLink->text, $offerLink->href)) {
            $this->insertOfferIntoDatabase($offerLink->text, $offerLink->href);
        }
    }

    /**
     * @param string $offerName
     * @param string $link
     * @return string
     */
    protected function generateUUID(string $offerName, string $link)
    {
        return md5($offerName . '----' . $link);
    }

    /**
     * @param string $offerName
     * @param string $link
     */
    protected function insertOfferIntoDatabase(string $offerName, string $link)
    {
        $verify = $this->database->insert('offer', [
            'url' => $link,
            'title' => $offerName,
            'uuid' => $this->generateUUID($offerName, $link)
        ]);

        if ($verify) {
            $this->info('Nowa oferta: ' . $offerName);

            $this->notify($offerName, $link);
        }
    }

    /**
     * @param string $offerName
     * @param string $link
     * @return bool
     */
    protected function offerExists(string $offerName, string $link)
    {
        $data = $this->database->select('offer', [
            'id'
        ], [
            'uuid' => $this->generateUUID($offerName, $link)
        ]);

        if (count($data) == 0) {
            return false;
        } else {
            return true;
        }
    }
}
