<?php

require 'UrlParser.php';
use PHPUnit\Framework\TestCase;

class UrlParserTest extends TestCase
{
    public function testCanBePrinted()
    {
        $urlparser = new \UrlParser\UrlParser('https://www.quoteshirts.nl');
        $this->assertEquals('https://www.quoteshirts.nl', (string)$urlparser);
    }

    public function testUrlIsSecure()
    {
        $urlparser = new \UrlParser\UrlParser('https://www.quoteshirts.nl');
        $this->assertEquals(true, $urlparser->isSecure());
    }

    public function testUrlIsNotSecure()
    {
        $urlparser = new \UrlParser\UrlParser('http://www.nu.nl');
        $this->assertEquals(false, $urlparser->isSecure());
    }

    public function testUrlGetRequestQueryParams()
    {
        $urlparser = new \UrlParser\UrlParser('http://www.nu.nl/?categorie=tech&ads=nee#ok');
        $this->assertCount(2, $urlparser->getRequestQueryParams());
        $this->assertEquals(
            ['categorie' => 'tech', 'ads' => 'nee'],
            $urlparser->getRequestQueryParams());
    }

    public function testUrlPath()
    {
        $urlparser = new \UrlParser\UrlParser('https://nu.nl/film/5821479/bijzonder-einde-flikken.html?no=1&m=d#1');
        $this->assertStringEndsWith(".html", $urlparser->getPath());
        $this->assertStringEndsNotWith("no=1&m=d", $urlparser->getPath());
    }

    public function testUrlDomain()
    {
        $urlparser = new \UrlParser\UrlParser('https://www.quoteshirts.nl/informatie-over-quoteshirts-4.html');
        $this->assertEquals("quoteshirts.nl", $urlparser->getDomain());
        $urlparser = new \UrlParser\UrlParser('https://henk.co.uk/film/');
        $this->assertEquals("henk.co.uk", $urlparser->getDomain());
        $urlparser = new \UrlParser\UrlParser('https://henk.gs.hm.no/film/');
        $this->assertEquals("henk.gs.hm.no", $urlparser->getDomain());
    }

    public function testUrlTLD()
    {
        $urlparser = new \UrlParser\UrlParser('https://nu.nl/film/5821479/bijzonder-einde-flikken.html?no=1&m=d#1');
        $this->assertEquals("nl", $urlparser->getTld());
        $this->assertNotEmpty($urlparser->getTld());
        $urlparser = new \UrlParser\UrlParser('https://henk.co.uk/film/');
        $this->assertEquals("co.uk", $urlparser->getTld());
        $this->assertNotEmpty($urlparser->getTld());
        $urlparser = new \UrlParser\UrlParser('https://henk.gs.hm.no/film/');
        $this->assertEquals("gs.hm.no", $urlparser->getTld());
        $this->assertNotEmpty($urlparser->getTld());
    }

    public function testUrlAnchor()
    {
        $urlparser = new \UrlParser\UrlParser('https://nu.nl/film/dingen#spullen');
        $this->assertEquals("spullen", $urlparser->getAnchor());
        $this->assertStringStartsNotWith("#", $urlparser->getAnchor());
    }

    public function testUrlNoAnchor()
    {
        $urlparser = new \UrlParser\UrlParser('https://www.linkedin.com');
        $this->assertEmpty($urlparser->getAnchor());
    }

    public function testUrlHost()
    {
        $urlparser = new \UrlParser\UrlParser('https://nu.nl/film/5821479/bijzonder-einde-flikken.html?no=1&m=d');
        $this->assertEquals("nu.nl", $urlparser->getHost());

        $urlparser = new \UrlParser\UrlParser('https://projecten.jeroenpeters.com/boerderij');
        $this->assertEquals("projecten.jeroenpeters.com", $urlparser->getHost());

        $urlparser = new \UrlParser\UrlParser('https://onze.kat.heet.mickey.en.hij.mauwt.com/heel/leuk.html');
        $this->assertEquals("onze.kat.heet.mickey.en.hij.mauwt.com", $urlparser->getHost());
    }

    public function testTldListLoaded()
    {
        $urlparser = new \UrlParser\UrlParser('http://www.nu.nl');
        $this->assertNotCount(0, $urlparser->getTldList());
    }
}