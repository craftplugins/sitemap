<?php

namespace Craft;

class Sitemap_AlternateUrlModel extends BaseModel
{
    public function getDomElement(\DOMDocument $document)
    {
        $element = $document->createElement('xhtml:link');
        $element->setAttribute('rel', 'alternate');
        $element->setAttribute('hreflang', $this->hreflang);
        $element->setAttribute('href', $this->href);

        return $element;
    }

    protected function defineAttributes()
    {
        return array(
            'hreflang' => AttributeType::String,
            'href' => AttributeType::Url,
        );
    }
}
