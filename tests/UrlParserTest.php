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
        $this->assertEquals(true, $urlparser->is_secure());
    }

    public function testUrlIsNotSecure()
    {
        $urlparser = new \UrlParser\UrlParser('http://www.nu.nl');
        $this->assertEquals(false, $urlparser->is_secure());
    }

    public function testUrlGetRequestQueryParams()
    {
        $urlparser = new \UrlParser\UrlParser('http://www.nu.nl/?categorie=tech&ads=nee#ok');
        $this->assertCount(2, $urlparser->get_request_query_params());
        $this->assertEquals(
            ['categorie' => 'tech', 'ads' => 'nee'],
            $urlparser->get_request_query_params());
    }

    public function testUrlPath()
    {
        $urlparser = new \UrlParser\UrlParser('https://nu.nl/film/5821479/bijzonder-einde-flikken.html?no=1&m=d#1');
        $this->assertStringEndsWith(".html", $urlparser->get_path());
        $this->assertStringEndsNotWith("no=1&m=d", $urlparser->get_path());
    }

    public function testUrlDomain()
    {
        $urlparser = new \UrlParser\UrlParser('https://www.quoteshirts.nl/informatie-over-quoteshirts-4.html');
        $this->assertEquals("quoteshirts.nl", $urlparser->get_domain());
        $urlparser = new \UrlParser\UrlParser('https://henk.co.uk/film/');
        $this->assertEquals("henk.co.uk", $urlparser->get_domain());
        $urlparser = new \UrlParser\UrlParser('https://henk.gs.hm.no/film/');
        $this->assertEquals("henk.gs.hm.no", $urlparser->get_domain());
    }

    public function testUrlTLD()
    {
        $urlparser = new \UrlParser\UrlParser('https://nu.nl/film/5821479/bijzonder-einde-flikken.html?no=1&m=d#1');
        $this->assertEquals("nl", $urlparser->get_tld());
        $this->assertNotEmpty($urlparser->get_tld());
        $urlparser = new \UrlParser\UrlParser('https://henk.co.uk/film/');
        $this->assertEquals("co.uk", $urlparser->get_tld());
        $this->assertNotEmpty($urlparser->get_tld());
        $urlparser = new \UrlParser\UrlParser('https://henk.gs.hm.no/film/');
        $this->assertEquals("gs.hm.no", $urlparser->get_tld());
        $this->assertNotEmpty($urlparser->get_tld());
    }

    public function testUrlAnchor()
    {
        $urlparser = new \UrlParser\UrlParser('https://nu.nl/film/dingen#spullen');
        $this->assertEquals("spullen", $urlparser->get_anchor());
        $this->assertStringStartsNotWith("#", $urlparser->get_anchor());
    }

    public function testUrlNoAnchor()
    {
        $urlparser = new \UrlParser\UrlParser('https://www.linkedin.com');
        $this->assertEmpty($urlparser->get_anchor());
    }

    public function testUrlHost()
    {
        $urlparser = new \UrlParser\UrlParser('https://nu.nl/film/5821479/bijzonder-einde-flikken.html?no=1&m=d');
        $this->assertEquals("nu.nl", $urlparser->get_host());

        $urlparser = new \UrlParser\UrlParser('https://projecten.jeroenpeters.com/boerderij');
        $this->assertEquals("projecten.jeroenpeters.com", $urlparser->get_host());

        $urlparser = new \UrlParser\UrlParser('https://onze.kat.heet.mickey.en.hij.mauwt.com/heel/leuk.html');
        $this->assertEquals("onze.kat.heet.mickey.en.hij.mauwt.com", $urlparser->get_host());
    }

    public function testTldListLoaded()
    {
        $urlparser = new \UrlParser\UrlParser('http://www.nu.nl');
        $this->assertNotCount(0, $urlparser->get_tld_list());
    }
}