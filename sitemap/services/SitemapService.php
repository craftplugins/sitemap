<?php

namespace Craft;

class SitemapService extends BaseApplicationComponent
{
	/**
	 * Returns all sections that have a URL format defined
	 */
	public function getSections()
	{
		$sections = array();

		foreach (craft()->sections->allSections as $section)
		{
			if ($section->isHomepage() || $section->urlFormat)
			{
				$sections[] = $section;
			}
		}

		return $sections;
	}

	/**
	 * Builds the sitemap based on the plugin settings as returns a string
	 */
	public function getSitemap()
	{
		$dom = new \DOMDocument('1.0', 'utf-8');

		$urlset = $dom->createElement('urlset');
		$urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

		$dom->appendChild($urlset);

		// Get settings
		$settings = $this->settings;

		foreach ($this->sections as $section)
		{
			if ( ! empty($settings['sections'][$section->id]))
			{
				$changefreq = $settings['sections'][$section->id]['changefreq'];
				$priority = $settings['sections'][$section->id]['priority'];

				$criteria = craft()->elements->getCriteria(ElementType::Entry);

				$entries  = $criteria->find(array(
					'section' => $section->handle
				));

				foreach ($entries as $entry)
				{
					$url = $dom->createElement('url');

					$urlLoc = $dom->createElement('loc');
					$urlLoc->nodeValue = $entry->getUrl();
					$url->appendChild($urlLoc);

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
	 * Get the plugin settings
	 */
	protected function getSettings()
	{
		$plugin = craft()->plugins->getPlugin('sitemap');

		if (is_null($plugin))
		{
			return array();
		}

		return $plugin->settings;
	}
}
