<?php

namespace Craft;

class Sitemap_UrlModel extends Sitemap_BaseModel
{
    /**
     * Array of Sitemap_AlternateUrlModel instances.
     *
     * @var array
     */
    protected $alternateUrls = array();

    /**
     * Constructor.
     *
     * @param string|urlModel    $loc
     * @param \DateTimeInterface $lastmod
     * @param string             $changefreq
     * @param string             $priority
     */
    public function __construct($loc, \DateTimeInterface $lastmod, $changefreq = null, $priority = null)
    {
        $this->loc = $loc;
        $this->lastmod = $lastmod;
        $this->changefreq = $changefreq;
        $this->priority = $priority;
    }

    /**
     * Add an altnative URL.
     *
     * @param Sitemap_AlternateUrlModel $alternateUrl
     */
    public function addAlternateUrl(Sitemap_AlternateUrlModel $alternateUrl)
    {
        $this->alternateUrls[$alternateUrl->hreflang] = $alternateUrl;
    }

    /**
     * Returns an array of assigned Sitemap_AlternateUrlModel instances.
     *
     * @return array
     */
    public function getAlternateUrls()
    {
        return $this->alternateUrls;
    }

    /**
     * Generates the relevant DOMElement instances.
     *
     * @param \DOMDocument $document
     *
     * @return \DOMElement
     */
    public function getDomElement(\DOMDocument $document)
    {
        $url = $document->createElement('url');

        $loc = $document->createElement('loc', $this->loc);
        $url->appendChild($loc);

        $lastmod = $document->createElement('lastmod', $this->lastmod->w3c());
        $url->appendChild($lastmod);

        if ($this->changefreq) {
            $changefreq = $document->createElement('changefreq', $this->changefreq);
            $url->appendChild($changefreq);
        }

        if ($this->priority) {
            $priority = $document->createElement('priority', $this->priority);
            $url->appendChild($priority);
        }

        foreach ($this->alternateUrls as $alternateUrl) {
            $link = $alternateUrl->getDomElement($document);
            $url->appendChild($link);
        }

        return $url;
    }

    /**
     * {@inheritdoc} BaseModel::defineAttributes()
     */
    protected function defineAttributes()
    {
        return array(
            'loc' => AttributeType::Url,
            'lastmod' => AttributeType::DateTime,
            'changefreq' => array(AttributeType::Enum, 'values' => SitemapChangeFrequency::getConstants()),
            'priority' => array(AttributeType::Enum, 'values' => SitemapPriority::getConstants()),
        );
    }
}
