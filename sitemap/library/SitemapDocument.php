<?php

namespace Craft;

use DOMDocument;

class SitemapDocument
{
    /**
     * DOMDocument instance.
     *
     * @var DOMDocument
     */
    protected $document;

    /**
     * The urlset element.
     *
     * @var DOMElement
     */
    protected $urlsetElement;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->document = new DOMDocument('1.0', 'utf-8');

        // Format XML output when devMode is active for easier debugging
        if (craft()->config->get('devMode')) {
            $this->document->formatOutput = true;
        }

        $this->urlsetElement = $this->document->createElement('urlset');
        $this->urlsetElement->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $this->urlsetElement->setAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');

        $this->document->appendChild($this->urlsetElement);
    }

    /**
     * Adds the URL to the sitemap.
     *
     * @param string $url
     * @param string $changefreq http://www.sitemaps.org/protocol.html#changefreqdef
     * @param string $priority   http://www.sitemaps.org/protocol.html#prioritydef
     */
    public function addUrl($url, $changefreq = null, $priority = null)
    {
        $urlElement = $this->document->createElement('url');

        $locElement = $this->document->createElement('loc', $url);
        $urlElement->appendChild($locElement);

        if ($changefreq) {
            $urlChangeFreq = $this->document->createElement('changefreq', $changefreq);
            $urlElement->appendChild($urlChangeFreq);
        }

        if ($priority) {
            $urlPriority = $this->document->createElement('priority', $priority);
            $urlElement->appendChild($urlPriority);
        }

        $this->urlsetElement->appendChild($urlElement);

        return $urlElement;
    }

    /**
     * Adds the element to the sitemap.
     *
     * @param BaseElementModel $element
     * @param string           $changefreq http://www.sitemaps.org/protocol.html#changefreqdef
     * @param string           $priority   http://www.sitemaps.org/protocol.html#prioritydef
     */
    public function addElement(BaseElementModel $element, $changefreq = null, $priority = null)
    {
        $urlElement = $this->addUrl($element->url, $changefreq, $priority);

        $locales = craft()->elements->getEnabledLocalesForElement($element->id);
        foreach ($locales as $locale) {
            $localeUrl = craft()->sitemap->getElementUrlForLocale($element, $locale);

            $localeElement = $this->document->createElement('xhtml:link');
            $localeElement->setAttribute('rel', 'alternate');
            $localeElement->setAttribute('hreflang', $locale);
            $localeElement->setAttribute('href', $localeUrl);
            $urlElement->appendChild($localeElement);
        }

        return $urlElement;
    }

    /**
     * Returns the sitemap XML.
     *
     * @return string
     */
    public function getXml()
    {
        return $this->document->saveXML();
    }
}