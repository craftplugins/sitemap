<?php

namespace Craft;

class Sitemap_AlternateUrlModel extends Sitemap_BaseModel
{
    /**
     * Constructor.
     *
     * @param string|LocaleModel $hreflang
     * @param string             $href
     */
    public function __construct($hreflang = null, $href = null)
    {
        $this->hreflang = $hreflang;
        $this->href = $href;
    }

    /**
     * {@inheritdoc} Sitemap_UrlModel::getDomElement()
     */
    public function getDomElement(\DOMDocument $document)
    {
        $element = $document->createElement('xhtml:link');
        $element->setAttribute('rel', 'alternate');
        $element->setAttribute('hreflang', $this->hreflang);
        $element->setAttribute('href', $this->href);

        return $element;
    }

    /**
     * {@inheritdoc} BaseModel::rules()
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = array('href', 'CUrlValidator');
        return $rules;
    }

    /**
     * {@inheritdoc} BaseModel::defineAttributes()
     */
    protected function defineAttributes()
    {
        return array(
            'hreflang' => AttributeType::Locale,
            'href' => AttributeType::Url,
        );
    }
}
