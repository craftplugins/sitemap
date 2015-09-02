<?php

namespace Craft;

class Sitemap_UrlModel extends BaseModel
{
    /**
     * Array of Sitemap_AlternateUrlModel instances.
     *
     * @var array
     */
    protected $alternateUrls = array();

    /**
     * Add an altnative URL.
     *
     * @param Sitemap_AlternateUrlModel $alternateUrl [description]
     */
    public function addAlternateUrl(Sitemap_AlternateUrlModel $alternateUrl)
    {
        $this->alternateUrls[] = $alternateUrl;
    }

    /**
     * @return array Array of assigned Sitemap_AlternateUrlModel instances
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

        $lastmod = $document->createElement('loc', $this->lastmod->w3c());
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
            'changefreq' => AttributeType::Enum,
            'priority' => AttributeType::Number,
        );
    }
}
