<?php

namespace App\Service;

use PHPHtmlParser\Dom;
use PHPHtmlParser\Dom\HtmlNode;

/**
 * Class DomParserService
 * @package App\Service
 */
class DomParserService
{
    /**
     * @var Dom
     */
    protected $dom;

    /**
     * DomParserService constructor.
     */
    public function __construct()
    {
        $this->dom = new Dom;
    }

    /**
     * @param bool $secondPage
     * @return Dom\Collection|HtmlNode
     * @throws \Exception
     */
    public function loadOffers(bool $secondPage = false)
    {
        $html = $this->loadURL($secondPage);
        $this->dom->load($html);
        /** @var HtmlNode $t */
        $t = $this->dom->find('h2[class="listing-order-name"]');

        if ($t) {
            return $t;
        } else {
            throw new \Exception('No offers!');
        }
    }

    /**
     * @param bool $secondPage
     * @return mixed
     */
    protected function loadURL(bool $secondPage = false)
    {
        $url = getenv('OFERIA_URL');
        if ($secondPage) {
            $url = $url . '?strona=2';
        }
        $this->dom->loadFromUrl($url);
        $html = $this->dom->__get('outerHtml');

        return $html;
    }
}