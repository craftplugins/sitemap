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
     * The urlset node.
     *
     * @var DOMElement
     */
    protected $urlset;

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

        $this->urlset = $this->document->createElement('urlset');
        $this->urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $this->urlset->setAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');

        $this->document->appendChild($this->urlset);
    }

    /**
     * Adds the relevant nodes for the element.
     *
     * @param BaseElementModel $element
     * @param string           $changefreq http://www.sitemaps.org/protocol.html#changefreqdef
     * @param string           $priority   http://www.sitemaps.org/protocol.html#prioritydef
     */
    public function addElement(BaseElementModel $element, $changefreq = null, $priority = null)
    {
        $url = $this->document->createElement('url');

        $urlLoc = $this->document->createElement('loc');
        $urlLoc->nodeValue = $element->getUrl();
        $url->appendChild($urlLoc);

        $locales = craft()->elements->getEnabledLocalesForElement($element->id);
        foreach ($locales as $locale) {
            $elementLocaleUrl = craft()->sitemap->getElementUrlForLocale($element, $locale);

            $localeLoc = $this->document->createElement('xhtml:link');
            $localeLoc->setAttribute('rel', 'alternate');
            $localeLoc->setAttribute('hreflang', $locale);
            $localeLoc->setAttribute('href', $elementLocaleUrl);
            $url->appendChild($localeLoc);
        }

        $urlModified = $this->document->createElement('lastmod');
        $urlModified->nodeValue = $element->dateUpdated->w3c();
        $url->appendChild($urlModified);

        if ($changefreq) {
            $urlChangeFreq = $this->document->createElement('changefreq');
            $urlChangeFreq->nodeValue = $changefreq;
            $url->appendChild($urlChangeFreq);
        }

        if ($priority) {
            $urlPriority = $this->document->createElement('priority');
            $urlPriority->nodeValue = $priority;
            $url->appendChild($urlPriority);
        }

        $this->urlset->appendChild($url);
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
