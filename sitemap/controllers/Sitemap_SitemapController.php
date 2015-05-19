<?php

namespace Craft;

class Sitemap_SitemapController extends BaseController
{
	protected $allowAnonymous = true;

	/**
	 * Outputs the sitemap
	 */
	public function actionOutput()
	{
		HeaderHelper::setContentTypeByExtension('xml');

		echo craft()->sitemap->sitemap;
	}
}
