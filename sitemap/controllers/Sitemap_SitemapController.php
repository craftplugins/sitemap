<?php

namespace Craft;

class Sitemap_SitemapController extends BaseController
{
    /**
     * {@inheritdoc} BaseController::$allowAnonymous
     *
     * @var bool
     */
    protected $allowAnonymous = true;

    /**
     * Outputs the returned sitemap.
     *
     * @return string
     */
    public function actionOutput()
    {
        HeaderHelper::setContentTypeByExtension('xml');

        echo craft()->sitemap->sitemap;
    }
}
