<?php

namespace Craft;

class Sitemap_UrlModel extends BaseModel
{
    protected $alternateUrls = array();

    public function addAlternateUrl(Sitemap_AlternateUrlModel $alternateUrl)
    {
        $this->alternateUrls[] = $alternateUrl;
    }

    public function getAlternateUrls()
    {
        return $this->alternateUrls;
    }

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
