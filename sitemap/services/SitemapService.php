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
     */
    public function addUrl(Sitemap_UrlModel $url)
    {
        if ($url->validate()) {
            $this->urls[$url->loc] = $url;
        }
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
        $url = new Sitemap_UrlModel($element->url, $element->dateUpdated, $changefreq, $priority);

        $locales = craft()->elements->getEnabledLocalesForElement($element->id);
        foreach ($locales as $locale) {
            $alternateUrl = new Sitemap_AlternateUrlModel();
            $alternateUrl->hreflang = $locale;
            $alternateUrl->href = craft()->sitemap->getElementUrlForLocale($element, $locale);

            $url->addAlternateUrl($alternateUrl);
        }

        $this->addUrl($url);
    }

    /**
     * Adds all entries related to the section to the sitemap.
     *
     * @param SectionModel $section
     * @param string       $changefreq
     * @param string       $priority
     */
    public function addSection(SectionModel $section, $changefreq = null, $priority = null)
    {
        $criteria = craft()->elements->getCriteria(ElementType::Entry);
        $criteria->section = $section;

        $entries = $criteria->find();
        foreach ($entries as $entry) {
            $this->addElement($entry, $changefreq, $priority);
        }
    }

    /**
     * Adds all entries related to the category to the sitemap.
     *
     * @param CategoryModel $category
     * @param string        $changefreq
     * @param string        $priority
     */
    public function addCategory(CategoryModel $category, $changefreq = null, $priority = null)
    {
        $criteria = craft()->elements->getCriteria(ElementType::Entry);
        $criteria->category = $category;

        $entries = $criteria->find();
        foreach ($entries as $entry) {
            $this->addElement($entry, $changefreq, $priority);
        }
    }

    /**
     * Adds all categories related to the group to the sitemap.
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
            $this->addCategory($category, $changefreq, $priority);
        }
    }

    /**
     * Gets a element URL for the specified locale.
     *
     * @param Element $element
     * @param Locale  $locale
     *
     * @return string
     */
    public function getElementUrlForLocale(BaseElementModel $element, $locale)
    {
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
     * @param string $url
     * @param string $locale
     *
     * @return string
     */
    public function getUrlForLocale($url, $locale)
    {
        $oldLanguage = craft()->language;
        craft()->setLanguage($locale);
        $url = UrlHelper::getSiteUrl($url);
        craft()->setLanguage($oldLanguage);

        return $url;
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
