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
    public function __construct($loc, $lastmod, $changefreq = null, $priority = null)
    {
        $this->loc = $loc;
        $this->lastmod = $lastmod;
        $this->changefreq = $changefreq;
        $this->priority = $priority;
    }

    /**
     * Add an alternate URL.
     *
     * @param string|LocaleModel $hreflang
     * @param string             $href
     *
     * @return Sitemap_AlternateUrlModel
     */
    public function addAlternateUrl($hreflang, $href)
    {
        $alternateUrl = new Sitemap_AlternateUrlModel($hreflang, $href);

        if ($alternateUrl->validate()) {
            $this->alternateUrls[$alternateUrl->hreflang] = $alternateUrl;
        }

        return $alternateUrl;
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
     * Returns true if there’s one or more alternate URLs, excluding the current locale.
     *
     * @return bool
     */
    public function hasAlternateUrls()
    {
        return count(array_filter($this->alternateUrls, function ($alternateUrl) {
            return $alternateUrl->hreflang != craft()->language;
        })) > 0;
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

        if ($this->hasAlternateUrls()) {
            foreach ($this->alternateUrls as $alternateUrl) {
                $link = $alternateUrl->getDomElement($document);
                $url->appendChild($link);
            }
        }

        return $url;
    }

    /**
     * {@inheritdoc} BaseModel::setAttribute()
     */
    public function setAttribute($name, $value)
    {
        if ($name == 'loc') {
            $this->addAlternateUrl(craft()->language, $value);
        }

        if ($name == 'lastmod') {
            if (!$value instanceof \DateTime) {
                try {
                    $value = new DateTime($value);
                } catch (\Exception $e) {
                    $message = Craft::t('“{object}->{attribute}” must be a DateTime object or a valid Unix timestamp.', array('object' => get_class($this), 'attribute' => $name));
                    throw new Exception($message);
                }
            }
            if (new DateTime() < $value) {
                $message = Craft::t('“{object}->{attribute}” must be in the past.', array('object' => get_class($this), 'attribute' => $name));
                throw new Exception($message);
            }
        }

        return parent::setAttribute($name, $value);
    }

    /**
     * {@inheritdoc} BaseModel::rules()
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = array('loc', 'CUrlValidator');
        return $rules;
    }

    /**
     * {@inheritdoc} BaseModel::defineAttributes()
     */
    protected function defineAttributes()
    {
        return array(
            'loc' => AttributeType::Url,
            'lastmod' => AttributeType::DateTime,
            'changefreq' => array(AttributeType::Enum, 'values' => Sitemap_ChangeFrequency::getConstants()),
            'priority' => array(AttributeType::Enum, 'values' => Sitemap_Priority::getConstants()),
        );
    }
}
