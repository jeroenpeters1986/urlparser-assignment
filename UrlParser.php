<?php

namespace UrlParser;

/**
 * Simple URL Parser, as part of a test assignment for CTD
 *
 * The assignment:
 *   Create a simple class which parses an URL
 *   The constructor should accept a full or a partial URL, then I could get the
 * different parts of it, e.g. protocol,
 *   host, path, etc. through getters.
 *
 *   No setters needed, this would be an immutable object class.
 *
 *   There should be a __toString() method implemented, so I could cast my $url
 * object to a string easily, e.g.
 *   echo (string)$url;
 *
 * Notes:
 *   - assumption: 'partial URL' means without https and stuff
 *   - assumption: this is used for http (not gopher/ftp/news)
 *   - this DOES recon all public TLD suffixes, there are a lot of them since
 *     ICANN opened up generic TLD's, and it's almost impossible to REGEX,
 *     which I'd prefered (https://publicsuffix.org/list/public_suffix_list.dat)
 *
 * Class UrlParser
 * @package UrlParser
 */
class UrlParser
{
    private $_url;
    private $_host;
    private $_domain;
    private $_tld;
    private $_path;
    private $_anchor;
    private $_valid_tld_list = [];
    private $_request_query_params = [];
    private $_protocol = "http";
    private $_is_secure = false;

    /**
     * UrlParser constructor which takes a single URL
     *
     * @param string $url Full or partial URL
     */
    public function __construct(string $url)
    {
        $this->_url = strtolower($url);
        $this->_parseUrl();
    }

    /**
     * Parse the URL and feed the internal string
     *
     * @return void
     */
    private function _parseUrl()
    {
        if (strpos($this->_url, "https") === 0) {
            $this->_protocol = "https";
            $this->_is_secure = true;
        }

        /* Clean the protocol from raw url */
        $this->_url = str_replace(
            array('http://', 'https://'),
            array('', ''),
            $this->_url
        );

        /* Separate the request parts from domain */
        $url_parts = explode("/", $this->_url, 2);
        $this->_host = array_shift($url_parts);
        $request_path = array_pop($url_parts);

        /* Separate the anchorlink indicator from the query strings, if any */
        if (strpos($request_path, "#") !== false) {
            $request_parts = explode("#", $request_path, 2);
            $this->_anchor = array_pop($request_parts);
            $request_path = array_shift($request_parts);
        }

        /* Separate the request params from the path */
        $request_parts = explode("?", $request_path, 2);
        $this->_path = array_shift($request_parts);

        if (!empty($request_parts)) {
            $this->_queryParamsToArray(array_shift($request_parts));
        }

        $this->_determineTld();
        $this->_determineDomain();
    }

    /**
     * Get a list of all currently valid TLD's available to register on the internet
     * The list is maintained by PublicSuffix.org
     *
     * @return void
     */
    private function _getValidTLDs()
    {
        $this->_valid_tld_list = [];
        $tld_list = file_get_contents("https://publicsuffix.org/list/public_suffix_list.dat");
        $list_line = explode("\n", $tld_list);

        foreach ($list_line as $tld) {
            if (!empty($tld) && substr($tld, 0, 2) != "//") {
                $this->_valid_tld_list[] = $tld;
            }
        }

        /* Reverse the list so '.co.uk' will be checked before 'uk'
           (the list names 'uk' first) */
        $this->_valid_tld_list = array_reverse($this->_valid_tld_list);
    }

    /**
     * Determine the TLD by comparing the tld's in our list to the host
     *
     * @return void
     */
    private function _determineTld()
    {
        $this->_getValidTLDs();

        foreach ($this->_valid_tld_list as $tld) {
            /* Compare the last X characters of the host to find a matching TLD,
               depending on the size of the tld in the list */
            $tld_len = strlen($tld);
            if (substr_compare($this->_host, $tld, (strlen($this->_host) - $tld_len), $tld_len) === 0) {
                $this->_tld = $tld;
                break;
            }
        }
    }

    /**
     * Determine the domain, this can be done by disarming the TLD,
     * check the latest host-part and put back the TLD again.
     *
     * @return void
     */
    private function _determineDomain()
    {
        $domain = str_replace("." . $this->_tld, '', $this->_host);
        $domain_parts = explode(".", $domain);
        $this->_domain = sprintf("%s.%s", array_pop($domain_parts), $this->_tld);
    }

    /**
     * Parse the request string to a key-value array
     *
     * @param string $query_string Query string from URL
     *
     * @return void
     */
    private function _queryParamsToArray($query_string)
    {
        foreach (explode('&', $query_string) as $param) {
            $parts = explode('=', $param);
            $this->_request_query_params[array_shift($parts)]
                = (!empty($parts) ? array_pop($parts) : '');
        }
    }

    /**
     * Let the URL be printed easily
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf("%s://%s", $this->_protocol, $this->_url);
    }

    /**
     * Return whether the url is secured
     *
     * @return boolean
     */
    public function isSecure()
    {
        return $this->_is_secure;
    }

    /**
     * Return the list of TLD's which are valid
     *
     * @return mixed
     */
    public function getTldList()
    {
        return $this->_valid_tld_list;
    }

    /**
     * Return the requested url path
     *
     * @return mixed
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Return the TLD (Top Level Domain)
     *
     * @return mixed
     */
    public function getTld()
    {
        return $this->_tld;
    }

    /**
     * Return the domain
     *
     * @return mixed
     */
    public function getDomain()
    {
        return $this->_domain;
    }

    /**
     * Return the anchor link
     *
     * @return mixed
     */
    public function getAnchor()
    {
        return $this->_anchor;
    }

    /**
     * Return the hostname
     *
     * @return mixed
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     * Return the request parameters
     *
     * @return array
     */
    public function getRequestQueryParams()
    {
        return $this->_request_query_params;
    }
}
