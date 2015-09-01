<?php

namespace Craft;

class SitemapService extends BaseApplicationComponent
{
    /**
     * Returns all Craft sections that have URLs.
     *
     * @return array An array of Section instances
     */
    public function getSectionsWithUrls()
    {
        $enabledSections = $this->settings['sections'];
        $sections = array();

        foreach (craft()->sections->allSections as $section) {
            if ($section->isHomepage() || $section->urlFormat) {
                $sections[] = $section;
            }
        }

        return $sections;
    }

    /**
     * Renders the sitemap.
     *
     * @return string XML sitemap
     */
    public function getSitemap()
    {
        $dom = new \DOMDocument('1.0', 'utf-8');

        // Format XML output when devMode is active for easier debugging
        if (craft()->config->get('devMode')) {
            $dom->formatOutput = true;
        }

        $urlset = $dom->createElement('urlset');
        $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $urlset->setAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');

        $dom->appendChild($urlset);

        // Get settings
        $settings = $this->settings;

        foreach ($this->sectionsWithUrls as $section) {
            if (!empty($settings['sections'][$section->id])) {
                $changefreq = $settings['sections'][$section->id]['changefreq'];
                $priority = $settings['sections'][$section->id]['priority'];

                $criteria = craft()->elements->getCriteria(ElementType::Entry);

                $entries = $criteria->find(array(
                    'section' => $section->handle,
                ));

                foreach ($entries as $entry) {
                    $url = $dom->createElement('url');

                    $urlLoc = $dom->createElement('loc');
                    $urlLoc->nodeValue = $entry->getUrl();
                    $url->appendChild($urlLoc);

                    $enabledLocales = craft()->elements->getEnabledLocalesForElement($entry->id);

                    foreach ($enabledLocales as $locale) {
                        $entryLocaleUrl = $this->getElementUrlForLocale($entry, $locale);

                        $localeLoc = $dom->createElement('xhtml:link');
                        $localeLoc->setAttribute('rel', 'alternate');
                        $localeLoc->setAttribute('hreflang', $locale);
                        $localeLoc->setAttribute('href', $entryLocaleUrl);
                        $url->appendChild($localeLoc);
                    }

                    $urlModified = $dom->createElement('lastmod');
                    $urlModified->nodeValue = $entry->postDate->w3c();
                    $url->appendChild($urlModified);

                    $urlChangeFreq = $dom->createElement('changefreq');
                    $urlChangeFreq->nodeValue = $changefreq;
                    $url->appendChild($urlChangeFreq);

                    $urlPriority = $dom->createElement('priority');
                    $urlPriority->nodeValue = $priority;
                    $url->appendChild($urlPriority);

                    $urlset->appendChild($url);
                }
            }
        }

        return $dom->saveXML();
    }

    /**
     * Gets the plugin settings.
     *
     * @return array
     */
    protected function getSettings()
    {
        $plugin = craft()->plugins->getPlugin('sitemap');

        if (is_null($plugin)) {
            return array();
        }

        return $plugin->settings;
    }

    /**
     * A modified copy of BaseElementModel::getUrl.
     *
     * @param Element $element
     * @param Locale  $locale
     *
     * @return string
     */
    protected function getElementUrlForLocale($element, $locale)
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
}
