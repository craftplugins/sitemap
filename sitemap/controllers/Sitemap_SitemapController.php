<?php
namespace Craft;

class Sitemap_SitemapController extends BaseController
{
	/**
	 * @inheritDoc BaseController::$allowAnonymous
	 * @var boolean
	 */
	protected $allowAnonymous = true;

	/**
	 * Outputs the returned sitemap
	 * @return string
	 */
	public function actionOutput()
	{
		HeaderHelper::setContentTypeByExtension('xml');

		echo craft()->sitemap->sitemap;
	}
}
