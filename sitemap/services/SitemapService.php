<?php

namespace Craft;

class SitemapService extends BaseApplicationComponent
{
    /**
     * Array of Sitemap_UrlModel instances.
     *
     * @var array
     */
    protected $urls = array();

    /**
     * {@inheritdoc} CApplicationComponent::init()
     */
    public function init()
    {
        Craft::import('plugins.sitemap.library.*');

        parent::init();
    }

    /**
     * Returns all sections that have URLs.
     *
     * @return array An array of Section instances
     */
    public function getSectionsWithUrls()
    {
        return array_filter(craft()->sections->allSections, function ($section) {
            return $section->isHomepage() || $section->urlFormat;
        });
    }

    /**
     * Return the sitemap as a string.
     *
     * @return string
     */
    public function getSitemap()
    {
        $settings = $this->pluginSettings;

        // Loop through and add the sections checked in the plugin settings
        foreach ($this->sectionsWithUrls as $section) {
            if (!empty($settings['sections'][$section->id])) {
                $changefreq = $settings['sections'][$section->id]['changefreq'];
                $priority = $settings['sections'][$section->id]['priority'];
                $this->addSection($section, $changefreq, $priority);
            }
        }

        // Hook: renderSitemap
        craft()->plugins->call('renderSitemap');

        // Use DOMDocument to generate XML
        $document = new \DOMDocument('1.0', 'utf-8');

        // Format XML output when devMode is active for easier debugging
        if (craft()->config->get('devMode')) {
            $document->formatOutput = true;
        }

        // Append a urlset node
        $urlset = $document->createElement('urlset');
        $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $urlset->setAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
        $document->appendChild($urlset);

        // Loop through and append Sitemap_UrlModel elements
        foreach ($this->urls as $url) {
            $urlElement = $url->getDomElement($document);
            $urlset->appendChild($urlElement);
        }

        return $document->saveXML();
    }

    /**
     * Adds a URL to the sitemap.
     *
     * @param string   $loc
     * @param DateTime $lastmod
     * @param string   $changefreq
     * @param string   $priority
     *
     * @return Sitemap_UrlModel
     */
    public function addUrl($loc, $lastmod, $changefreq = null, $priority = null)
    {
        $url = new Sitemap_UrlModel($loc, $lastmod, $changefreq, $priority);

        if ($url->validate()) {
            $this->urls[$url->loc] = $url;
        }

        return $url;
    }

    /**
     * Adds an element to the sitemap.
     *
     * @param BaseElementModel $element
     * @param string           $changefreq
     * @param string           $priority
     */
    public function addElement(BaseElementModel $element, $changefreq = null, $priority = null)
    {
        $url = $this->addUrl($element->url, $element->dateUpdated, $changefreq, $priority);

        $locales = craft()->elements->getEnabledLocalesForElement($element->id);
        foreach ($locales as $locale) {
            $href = craft()->sitemap->getElementUrlForLocale($element, $locale);
            $url->addAlternateUrl($locale, $href);
        }
    }

    /**
     * Adds all entries in the section to the sitemap.
     *
     * @param SectionModel $section
     * @param string       $changefreq
     * @param string       $priority
     */
    public function addSection(SectionModel $section, $changefreq = null, $priority = null)
    {
        $criteria = craft()->elements->getCriteria(ElementType::Entry);
        $criteria->section = $section;
        foreach ($criteria->find() as $element) {
            $this->addElement($element, $changefreq, $priority);
        }
    }

    /**
     * Adds all categories in the group to the sitemap.
     *
     * @param CategoryGroupModel $categoryGroup
     * @param string             $changefreq
     * @param string             $priority
     */
    public function addCategoryGroup(CategoryGroupModel $categoryGroup, $changefreq = null, $priority = null)
    {
        $criteria = craft()->elements->getCriteria(ElementType::Category);
        $criteria->group = $categoryGroup;

        $categories = $criteria->find();
        foreach ($categories as $category) {
            $this->addElement($category, $changefreq, $priority);
        }
    }

    /**
     * Gets a element URL for the specified locale.
     *
     * @param Element            $element
     * @param string|LocaleModel $locale
     *
     * @return string
     */
    public function getElementUrlForLocale(BaseElementModel $element, $locale)
    {
        $this->validateLocale($locale);

        $oldLocale = $element->locale;
        $oldUri = $element->uri;
        $element->locale = $locale;
        $element->uri = craft()->elements->getElementUriForLocale($element->id, $locale);
        $url = $element->getUrl();
        $element->locale = $oldLocale;
        $element->uri = $oldUri;

        return $url;
    }

    /**
     * Gets a URL for the specified locale.
     *
     * @param string             $path
     * @param string|LocaleModel $locale
     *
     * @return string
     */
    public function getUrlForLocale($path, $locale)
    {
        $this->validateLocale($locale);

        // Get the site URL for the current locale
        $siteUrl = craft()->siteUrl;

        if (UrlHelper::isFullUrl($path)) {
            // Return $path if it’s a remote URL
            if (!stripos($path, $siteUrl)) {
                return $path;
            }

            // Remove the current locale siteUrl
            $path = str_replace($siteUrl, '', $path);
        }

        // Get the site URL for the specified locale
        $localizedSiteUrl = craft()->config->getLocalized('siteUrl', $locale);

        // Trim slahes
        $localizedSiteUrl = rtrim($localizedSiteUrl, '/');
        $path = trim($path, '/');

        return UrlHelper::getUrl($localizedSiteUrl.'/'.$path);
    }

    /**
     * Ensures that the requested locale is valid.
     *
     * @param string|LocaleModel $locale
     */
    protected function validateLocale($locale)
    {
        if (!in_array($locale, craft()->i18n->siteLocales)) {
            throw new Exception(Craft::t('“{locale}” is not a valid site locale.', array('locale' => $locale)));
        }
    }

    /**
     * Gets the plugin settings.
     *
     * @return array
     */
    protected function getPluginSettings()
    {
        $plugin = craft()->plugins->getPlugin('sitemap');

        if (is_null($plugin)) {
            return array();
        }

        return $plugin->settings;
    }
}
