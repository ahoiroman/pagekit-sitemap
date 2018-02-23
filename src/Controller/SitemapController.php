<?php

namespace Spqr\Sitemap\Controller;

use Pagekit\Application as App;
use Spqr\Sitemap\Helper\SitemapHelper;

/**
 * @Access(admin=true)
 */
class SitemapController
{
	/**
	 * @return mixed
	 */
	public function indexAction()
	{
		return App::response()->redirect( '@sitemap/settings' );
	}
	
	/**
	 * @Access("sitemap: manage settings")
	 */
	public function settingsAction()
	{
        $module = App::module('spqr/sitemap');
		$config = $module->config;
		
		return [
			'$view' => [
                'title' => __( 'Sitemap Settings' ),
                'name'  => 'spqr/sitemap:views/admin/settings.php',
			],
			'$data' => [
                'config' => $config,
			]
		];
	}
	
	/**
	 * @Request({"config": "array"}, csrf=true)
	 * @param array $config
	 *
	 * @return array
	 */
	public function saveAction( $config = [] )
	{
        App::config()->set('spqr/sitemap', $config);
		
		return [ 'message' => 'success' ];
	}
	
	/**
	 * @Route("/generate", methods="POST")
	 * @Request(csrf=true)
	 */
	public function generateAction()
	{
		$sitemap = new SitemapHelper;
		$result = $sitemap->generate();
		
		return $result;
	}
}