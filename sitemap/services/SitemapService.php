<?php

namespace Craft;

class SitemapService extends BaseApplicationComponent
{
    /**
     * SitemapDocument instance.
     *
     * @var SitemapDocument
     */
    protected $document;

    /**
     * {@inheritdoc} CApplicationComponent::init()
     */
    public function init()
    {
        Craft::import('plugins.sitemap.library.*');

        $this->document = new SitemapDocument();

        parent::init();
    }

    /**
     * Returns all Craft sections that have URLs.
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

        foreach ($this->sectionsWithUrls as $section) {
            if (!empty($settings['sections'][$section->id])) {
                $changefreq = $settings['sections'][$section->id]['changefreq'];
                $priority = $settings['sections'][$section->id]['priority'];
                $this->addSectionToSitemap($section, $changefreq, $priority);
            }
        }

        return $this->document->getXml();
    }

    /**
     * Adds all elements in a section to the sitemap.
     *
     * @param SectionModel $section
     * @param string       $changefreq
     * @param string       $priority
     */
    public function addSectionToSitemap(SectionModel $section, $changefreq = null, $priority = null)
    {
        $criteria = craft()->elements->getCriteria(ElementType::Entry);
        $criteria->section = $section;

        $elements = $criteria->find();
        foreach ($elements as $element) {
            $this->addElementToSitemap($element, $changefreq, $priority);
        }
    }

    /**
     * Adds an element to the sitemap.
     *
     * @param BaseElementModel $element
     * @param string           $changefreq
     * @param string           $priority
     */
    public function addElementToSitemap(BaseElementModel $element, $changefreq = null, $priority = null)
    {
        $this->document->addElement($element, $changefreq, $priority);
    }

    /**
     * Returns the localized URL for an element.
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
