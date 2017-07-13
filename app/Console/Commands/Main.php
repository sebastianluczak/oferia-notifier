<?php

namespace App\Console\Commands;

use App\Service\DatabaseService;
use App\Service\DomParserService;
use App\Service\OSNotifierService;
use App\Service\SlackNotifierService;
use Illuminate\Console\Command;
use PHPHtmlParser\Dom\Collection;
use PHPHtmlParser\Dom\HtmlNode;

/**
 * Class Main
 * @package App\Console\Commands
 */
class Main extends Command
{
    /**
     * @var DatabaseService
     */
    protected $databaseService;

    /**
     * @var SlackNotifierService
     */
    protected $slackService;

    /**
     * @var OSNotifierService
     */
    protected $notifierService;

    /**
     * @var DomParserService
     */
    protected $domParserService;

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
    protected $description = 'Looks for oferia.pl offers, saves in DB and notifies user via Slack Service';

    /**
     * Main constructor.
     */
    public function __construct()
    {
        $this->databaseService = new DatabaseService();
        $this->slackService = new SlackNotifierService();
        $this->notifierService = new OSNotifierService();
        $this->domParserService = new DomParserService();

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $offersFromFirstPage = $this->domParserService->loadOffers();
        $offersProcessed = $this->processOffers($offersFromFirstPage);

        if ($offersProcessed >= 15) {
            // we should look for second page, oferia.pl sometimes limits page views to non-commercial offers = 15
            $offersFromSecond = $this->domParserService->loadOffers($secondPage = true);
            $this->processOffers($offersFromSecond);
        }

    }

    /**
     * @param Collection $offers
     * @return int
     */
    protected function processOffers(Collection $offers)
    {
        $count = 0;
        foreach ($offers as $item) {
            $offerLink = $item->find('a')[0];
            if ($offerLink) {
                if ($this->handleOfferLink($offerLink)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * @param HtmlNode $offerLink
     * @return bool
     */
    protected function handleOfferLink(HtmlNode $offerLink)
    {
        $link = $offerLink->__get('href');
        $title = $offerLink->__get('text');

        if (!$this->databaseService->checkIfOfferExists($title, $link)) {
            $verify = $this->databaseService->insertOffer($title, $link);

            if ($verify) {
                $this->info($title);
                if (getenv('NOTIFY_VIA_SLACK')) {
                    $this->slackService->send($title);
                }

                if (getenv('NOTIFY_VIA_DESKTOP')) {
                    $this->notifierService->send($title, $link);
                }

                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->signature;
    }
}
