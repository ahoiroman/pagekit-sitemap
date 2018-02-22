<?php

namespace Spqr\Sitemap\Helper;

use Pagekit\Application as App;
use Sunra\PhpSimple\HtmlDomParser;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;


/**
 * Class SitemapHelper
 *
 * @package Spqr\Sitemap\Helper
 */
class SitemapHelper
{
    /**
     * @var
     */
    private $client;
    
    /**
     * @var
     */
    private $outputfile;
    /**
     * @var string
     */
    private $site;
    /**
     * @var
     */
    private $skip_urls;
    /**
     * @var
     */
    private $frequency;
    /**
     * @var float
     */
    private $priority;
    
    /**
     * @var array
     */
    private $extensions;
    
    /**
     * @var string
     */
    private $version;
    /**
     * @var string
     */
    private $agent;
    
    /**
     * @var array
     */
    private $urls;
    
    /**
     * @var array
     */
    private $scanned;
    
    /**
     * @var
     */
    private $pf;
    
    
    /**
     * SitemapHelper constructor.
     */
    public function __construct()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
            || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domain   = $_SERVER['HTTP_HOST'];
        
        $module = App::module('sitemap');
        $config = $module->config;
        
        $this->client     = new Client();
        $this->outputfile = $config['filename'];
        $this->site       = $protocol.$domain.'/';
        $this->skip_urls  = $config['excluded'];
        $this->urls       = [];
        $this->scanned    = [];
        $this->frequency  = $config['frequency'];
        $this->priority   = 0.5;
        $this->extensions = [
            ".html",
            ".php",
            "/",
        ];
        $this->version    = "2.0";
        $this->agent      = "Mozilla/5.0 (compatible; SPQR Sitemap Generator/"
            .$this->version.")";
    }
    
    /**
     * @return bool
     */
    public function generate()
    {
        $this->pf = fopen($this->outputfile, "w");
        if (!$this->pf) {
            return false;
        }
        
        $this->site = filter_var($this->site, FILTER_SANITIZE_URL);
        
        fwrite($this->pf, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
            ."<!-- Created with SPQR Sitemap Generator ".$this->version
            ." https://spqr.wtf -->"
            ."<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"\n"
            ."        xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n"
            ."        xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9\n"
            ."        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">\n"
            ."  <url>\n"."    <loc>".htmlentities($this->site)."</loc>\n"
            ."    <changefreq>$this->frequency</changefreq>\n"
            ."    <priority>$this->priority</priority>\n"."  </url>\n");
        
        $this->scan($this->site);
        
        $result = [];
        
        foreach ($this->urls as $url) {
            $result[] = rtrim($url, '/\\');
        }
        
        foreach (array_unique($result) as $entry) {
            $pr = number_format(round($this->priority / count(explode("/",
                    trim(str_ireplace([
                        "http://",
                        "https://",
                    ], "", $entry), "/"))) + 0.5, 3), 1);
            fwrite($this->pf,
                "  <url>\n"."    <loc>".htmlentities($entry)."</loc>\n"
                ."    <changefreq>$this->frequency</changefreq>\n"."    <priority
  >$pr</priority
  >\n"."  </url>\n");
        }
        
        fwrite($this->pf, "</urlset>\n");
        fclose($this->pf);
        
        return true;
    }
    
    /**
     * @param $url
     */
    function scan($url)
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);
        
        if (!filter_var($url, FILTER_VALIDATE_URL)
            || in_array($url, $this->scanned)
        ) {
            return;
        }
        
        $this->scanned[] = $url;
        $html            = HtmlDomParser::str_get_html($this->getURL($url));
        
        if ($html === false) {
            return; //TODO: Dirty workaround. We can do better...
        }
        
        $a1 = $html->find('a');
        
        foreach ($a1 as $val) {
            $next_url = $val->href or "";
            $fragment_split = explode("#", $next_url);
            $next_url       = $fragment_split[0];
            
            if ((substr($next_url, 0, 7) != "http://")
                && (substr($next_url, 0, 8) != "https://")
                && (substr($next_url, 0, 6) != "ftp://")
                && (substr($next_url, 0, 7) != "mailto:")
            ) {
                $next_url = @$this->rel2abs($next_url, $url);
            }
            
            $next_url = filter_var($next_url, FILTER_SANITIZE_URL);
            
            if (substr($next_url, 0, strlen($this->site)) == $this->site) {
                $ignore = false;
                
                if (!filter_var($next_url, FILTER_VALIDATE_URL)) {
                    $ignore = true;
                }
                
                if (in_array($next_url, $this->scanned)) {
                    $ignore = true;
                }
                
                $pi = pathinfo(parse_url($next_url)['path'],
                    PATHINFO_EXTENSION);
                
                if (!in_array($pi, $this->extensions)
                    && ($pi != ""
                        || $pi = null)
                ) {
                    $ignore = true;
                }
                
                if (isset ($this->skip_urls) && !$ignore) {
                    foreach ($this->skip_urls as $v) {
                        if (substr($next_url, 0, strlen($v)) == $v) {
                            $ignore = true;
                        }
                    }
                }
                
                if (!$ignore) {
                    foreach ($this->extensions as $ext) {
                        if (strpos($next_url, $ext) > 0) {
                            $this->urls[] = htmlentities($next_url);
                            $this->scan($next_url);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * @param $url
     *
     * @return mixed
     */
    private function getURL($url)
    {
        try {
            $data = $this->client->request('GET', $url, [
                'headers' => [
                    'User-Agent' => $this->agent,
                ],
            ]);
            
            return $data->getBody();
            
        } catch (ClientException $e) {
            if (($key = array_search($url, $this->urls)) !== false) {
                unset($this->urls[$key]);
            }
            
            return false;
        }
    }
    
    /**
     * @param $rel
     * @param $base
     *
     * @return string
     */
    private function rel2abs($rel, $base)
    {
        if (strpos($rel, "//") === 0) {
            return "http:".$rel;
        }
        /* return if  already absolute URL */
        if (parse_url($rel, PHP_URL_SCHEME) != '') {
            return $rel;
        }
        $first_char = substr($rel, 0, 1);
        /* queries and  anchors */
        if ($first_char == '#' || $first_char == '?') {
            return $base.$rel;
        }
        /* parse base URL  and convert to local variables:
        $scheme, $host,  $path */
        extract(parse_url($base));
        /* remove  non-directory element from path */
        $path = preg_replace('#/[^/]*$#', '', $path);
        /* destroy path if  relative url points to root */
        if ($first_char == '/') {
            $path = '';
        }
        /* dirty absolute  URL */
        $abs = "$host$path/$rel";
        /* replace '//' or  '/./' or '/foo/../' with '/' */
        $re = ['#(/.?/)#', '#/(?!..)[^/]+/../#'];
        for ($n = 1; $n > 0; $abs = preg_replace($re, '/', $abs, -1, $n)) {
        }
        
        /* absolute URL is  ready! */
        
        return $scheme.'://'.$abs;
    }
    
}