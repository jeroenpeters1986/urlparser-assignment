<?php

namespace UrlParser;

/**
 * Simple URL Parser, as part of a test assignment for CTD
 *
 * The assignment:
 *   Create a simple class which parses an URL
 *   The constructor should accept a full or a partial URL, then I could get the different parts of it, e.g. protocol,
 *   host, path, etc. through getters.
 *
 *   No setters needed, this would be an immutable object class.
 *
 *   There should be a __toString() method implemented, so I could cast my $url object to a string easily, e.g.
 *   echo (string)$url;
 *
 * Notes:
 *   - assumption: 'partial URL' means without https and stuff
 *   - assumption: this is used for http (not gopher/ftp/news)
 *   - this DOES recon all public TLD suffixes, there are a lot of them since ICANN opened up generic TLD's,
 *     and it's almost impossible to REGEX, which I'd prefered (https://publicsuffix.org/list/public_suffix_list.dat)
 *
 * Class UrlParser
 * @package UrlParser
 */
class UrlParser
{
    private $url;
    private $host;
    private $domain;
    private $tld;
    private $path;
    private $anchor;
    private $valid_tld_list = [];
    private $request_query_params = [];
    private $protocol = "http";
    private $is_secure = false;

    /**
     * UrlParser constructor which takes a single URL
     * @param $url
     */
    public function __construct($url)
    {
        $this->url = strtolower($url);
        $this->parseUrl();
    }

    /* Parse the URL and feed the internal strings */
    public function parseUrl()
    {
        if (strpos($this->url, "https") === 0) {
            $this->protocol = "https";
            $this->is_secure = true;
        }

        /* Clean the protocol from raw url */
        $this->url = str_replace(array('http://', 'https://'), array('', ''), $this->url);

        /* Separate the request parts from domain */
        $url_parts = explode("/", $this->url, 2);
        $this->host = array_shift($url_parts);
        $request_path = array_pop($url_parts);

        /* Separate the anchorlink indicator from the query strings, if any */
        if (strpos($request_path, "#") !== false) {
            $request_parts = explode("#", $request_path, 2);
            $this->anchor = array_pop($request_parts);
            $request_path = array_shift($request_parts);
        }

        /* Separate the request params from the path */
        $request_parts = explode("?", $request_path, 2);
        $this->path = array_shift($request_parts);

        if (!empty($request_parts)) {
            $this->queryParamsToArray(array_shift($request_parts));
        }

        $this->determineTld();
        $this->determineDomain();
    }

    /**
     * Get a list of all currently valid TLD's available to register on the internet
     * The list is maintained by PublicSuffix.org
     */
    private function getValidTLDs()
    {
        $this->valid_tld_list = [];
        $tld_list = file_get_contents("https://publicsuffix.org/list/public_suffix_list.dat");
        $list_line = explode("\n", $tld_list);

        foreach ($list_line as $tld) {
            if (!empty($tld) && substr($tld, 0, 2) != "//") {
                $this->valid_tld_list[] = $tld;
            }
        }

        /* Reverse the list so '.co.uk' will be checked before 'uk' (the list names 'uk' first) */
        $this->valid_tld_list = array_reverse($this->valid_tld_list);
    }

    /**
     * Determine the TLD by comparing the tld's in our list to the host
     */
    private function determineTld()
    {
        $this->getValidTLDs();

        foreach ($this->valid_tld_list as $tld) {
            /* Compare the last X characters of the host to find a matching TLD,
               depending on the size of the tld in the list */
            $tld_len = strlen($tld);
            if (substr_compare($this->host, $tld, (strlen($this->host) - $tld_len), $tld_len) === 0) {
                $this->tld = $tld;
                break;
            }
        }
    }

    /**
     * Determine the domain, this can be done by disarming the TLD, check the latest host-part
     * and put back the TLD again.
     */
    private function determineDomain()
    {
        $domain = str_replace("." . $this->tld, '', $this->host);
        $domain_parts = explode(".", $domain);
        $this->domain = sprintf("%s.%s", array_pop($domain_parts), $this->tld);
    }

    /**
     * Parse the request string to a key-value array
     * @param $query_string
     */
    private function queryParamsToArray($query_string)
    {
        foreach (explode('&', $query_string) as $param) {
            $parts = explode('=', $param);
            $this->request_query_params[array_shift($parts)] = (!empty($parts) ? array_pop($parts) : '');
        }
    }

    /**
     * Let the URL be printed easily
     * @return mixed
     */
    public function __toString()
    {
        return sprintf("%s://%s", $this->protocol, $this->url);
    }

    /* Typical getters */
    public function isSecure()
    {
        return $this->is_secure;
    }

    public function getTldList()
    {
        return $this->valid_tld_list;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getTld()
    {
        return $this->tld;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getAnchor()
    {
        return $this->anchor;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getRequestQueryParams()
    {
        return $this->request_query_params;
    }
}
