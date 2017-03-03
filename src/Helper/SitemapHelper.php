<?php

namespace Spqr\Sitemap\Helper;

use Pagekit\Application as App;


/**
 * Class SitemapHelper
 * @package Spqr\Sitemap\Helper
 */
class SitemapHelper
{
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
	 * @var bool
	 */
	private $ignoreemptycontenttype;
	/**
	 * @var string
	 */
	private $version;
	/**
	 * @var string
	 */
	private $agent;
	
	
	/**
	 * SitemapHelper constructor.
	 */
	public function __construct()
	{
		$protocol =
			( !empty( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] !== 'off' || $_SERVER[ 'SERVER_PORT' ] == 443 )
				? "https://" : "http://";
		$domain   = $_SERVER[ 'HTTP_HOST' ];
		
		$module = App::module( 'sitemap' );
		$config = $module->config;
		
		$this->outputfile             = $config[ 'filename' ];
		$this->site                   = $protocol . $domain;
		$this->skip_urls              = $config[ 'excluded' ];
		$this->frequency              = $config[ 'frequency' ];
		$this->priority               = 0.5;
		$this->ignoreemptycontenttype = false;
		$this->version                = "1.0";
		$this->agent                  = "Mozilla/5.0 (compatible; SPQR Sitemap Generator/" . $this->version . ")";
	}
	
	private function getPage( $url )
	{
		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_USERAGENT, $this->agent );
		
		$data = curl_exec( $ch );
		
		curl_close( $ch );
		
		return $data;
	}
	
	private function getQuotedUrl( $str )
	{
		$quote = substr( $str, 0, 1 );
		if ( ( $quote != "\"" ) && ( $quote != "'" ) ) {
			return $str;
		}
		
		$ret = "";
		$len = strlen( $str );
		for ( $i = 1; $i < $len; $i++ ) {
			$ch = substr( $str, $i, 1 );
			
			if ( $ch == $quote )
				break;
			
			$ret .= $ch;
		}
		
		return $ret;
	}
	
	private function getHrefValue( $anchor )
	{
		$split1      = explode( "href=", $anchor );
		$split2      = explode( ">", $split1[ 1 ] );
		$href_string = $split2[ 0 ];
		
		$first_ch = substr( $href_string, 0, 1 );
		if ( $first_ch == "\"" || $first_ch == "'" ) {
			$url = $this->getQuotedUrl( $href_string );
		} else {
			$spaces_split = explode( " ", $href_string );
			$url          = $spaces_split[ 0 ];
		}
		
		return $url;
	}
	
	private function getEffectiveUrl( $url )
	{
		$ch = curl_init( $url );
		
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_USERAGENT, $this->agent );
		curl_exec( $ch );
		
		$effective_url = curl_getinfo( $ch, CURLINFO_EFFECTIVE_URL );
		
		curl_close( $ch );
		
		return $effective_url;
	}
	
	private function validateUrl( $url_base, $url )
	{
		global $scanned;
		
		$parsed_url = parse_url( $url );
		
		$scheme = $parsed_url[ "scheme" ];
		
		if ( ( $scheme != SITE_SCHEME ) && ( $scheme != "" ) )
			return false;
		
		$host = $parsed_url[ "host" ];
		
		if ( ( $host != SITE_HOST ) && ( $host != "" ) )
			return false;
		
		
		if ( $host == "" ) {
			if ( substr( $url, 0, 1 ) == '#' ) {
				return false;
			}
			
			if ( substr( $url, 0, 1 ) == '/' ) {
				$url = SITE_SCHEME . "://" . SITE_HOST . $url;
			} else {
				
				$path = parse_url( $url_base, PHP_URL_PATH );
				
				if ( substr( $path, -1 ) == '/' ) {
					$url = SITE_SCHEME . "://" . SITE_HOST . $path . $url;
				} else {
					$dirname = dirname( $path );
					
					if ( $dirname[ 0 ] != '/' ) {
						$dirname = "/$dirname";
					}
					
					if ( substr( $dirname, -1 ) != '/' ) {
						$dirname = "$dirname/";
					}
					
					$url = SITE_SCHEME . "://" . SITE_HOST . $dirname . $url;
				}
			}
		}
		
		$url = $this->getEffectiveUrl( $url );
		
		if ( in_array( $url, $scanned ) )
			return false;
		
		return $url;
	}
	
	private function skipUrl( $url )
	{
		if ( isset ( $this->skip_urls ) ) {
			foreach ( $this->skip_urls as $v ) {
				if ( substr( $url, 0, strlen( $v ) ) == $v )
					return true;
			}
		}
		
		return false;
	}
	
	private function scan( $url )
	{
		global $scanned;
		
		$scanned[] = $url;
		
		if ( $this->skipUrl( $url ) ) {
			return false;
		}
		
		if ( substr( $url, -2 ) == "//" ) {
			$url = substr( $url, 0, -2 );
		}
		if ( substr( $url, -1 ) == "/" ) {
			$url = substr( $url, 0, -1 );
		}
		
		
		$headers = get_headers( $url, 1 );
		
		if ( strpos( $headers[ 0 ], "404" ) !== false ) {
			return false;
		}
		
		if ( strpos( $headers[ 0 ], "301" ) !== false ) {
			$url = $headers[ "Location" ];
		} else if ( strpos( $headers[ 0 ], "200" ) == false ) {
			$url = $headers[ "Location" ];
			
			return false;
		}
		
		if ( is_array( $headers[ "Content-Type" ] ) ) {
			$content = explode( ";", $headers[ "Content-Type" ][ 0 ] );
		} else {
			$content = explode( ";", $headers[ "Content-Type" ] );
		}
		
		$content_type = trim( strtolower( $content[ 0 ] ) );
		
		if ( $content_type != "text/html" ) {
			if ( $content_type == "" && $this->ignoreemptycontenttype ) {
				
			} else {
				if ( $content_type == "" ) {
				} else {
				}
				
				return false;
			}
		}
		
		$html = $this->getPage( $url );
		$html = trim( $html );
		if ( $html == "" )
			return true;
		
		$html = str_replace( "\r", " ", $html );
		$html = str_replace( "\n", " ", $html );
		$html = str_replace( "\t", " ", $html );
		$html = str_replace( "<A ", "<a ", $html );
		
		$first_anchor = strpos( $html, "<a " );
		
		if ( $first_anchor === false )
			return true;
		
		$html = substr( $html, $first_anchor );
		
		$a1 = explode( "<a ", $html );
		foreach ( $a1 as $next_url ) {
			$next_url = trim( $next_url );
			
			if ( $next_url == "" )
				continue;
			
			$next_url = $this->getHrefValue( $next_url );
			
			$next_url = $this->validateUrl( $url, $next_url );
			
			if ( $next_url == false )
				continue;
			
			if ( $this->scan( $next_url ) ) {
				fwrite(
					$this->pf,
					"  <url>\n" . "    <loc>" . htmlentities(
						$next_url
					) . "</loc>\n" . "    <changefreq>" . $this->frequency . "</changefreq>\n" . "    <priority>" . $this->priority . "</priority>\n" . "  </url>\n"
				);
			}
		}
		
		return true;
	}
	
	public function generate()
	{
		define( "SITE_SCHEME", parse_url( $this->site, PHP_URL_SCHEME ) );
		define( "SITE_HOST", parse_url( $this->site, PHP_URL_HOST ) );
		
		error_reporting( E_ERROR | E_WARNING | E_PARSE );
		
		$this->pf = fopen( $this->outputfile, "w" );
		
		if ( !$this->pf ) {
			return;
		}
		
		fwrite(
			$this->pf,
			"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" . "<!-- Created with SPQR Sitemap Generator " . $this->version . " https://spqr.wtf -->\n" . "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"\n" . "        xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n" . "        xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9\n" . "        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">\n" . "  <url>\n" . "    <loc>" . $this->site . "/</loc>\n" . "    <changefreq>" . $this->frequency . "</changefreq>\n" . "  </url>\n"
		);
		
		$scanned = [];
		$this->scan( $this->getEffectiveUrl( $this->site ) );
		
		fwrite( $this->pf, "</urlset>\n" );
		fclose( $this->pf );
		
		return true;
	}
	
}