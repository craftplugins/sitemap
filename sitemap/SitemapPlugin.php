<?php

namespace Craft;

class SitemapPlugin extends BasePlugin
{
	/**
	 * The name
	 */
	public function getName()
	{
		return 'Sitemap';
	}

	/**
	 * The version
	 */
	public function getVersion()
	{
		return '0.9.0';
	}

	/**
	 * The person
	 */
	public function getDeveloper()
	{
		return 'Joshua Baker';
	}

	/**
	 * Follow?
	 */
	public function getDeveloperUrl()
	{
		return 'http://joshuabaker.com/';
	}

	/**
	 * Define the settings
	 */
	protected function defineSettings()
	{
		return array(
			'sections' => array(),
		);
	}

	/**
	 * Render the plugin settings page
	 */
	public function getSettingsHtml()
	{
		return craft()->templates->render('sitemap/_settings', array(
			'sections' => craft()->sitemap->sections,
			'settings' => $this->settings,
		));
	}

	/**
	 * Parse the settings before saving to the database
	 */
	public function prepSettings($input)
	{
		// We’re rewriting every time
		$settings = $this->defineSettings();

		// Loop through valid sections
		foreach (craft()->sitemap->sections as $section)
		{
			// Check if the section is enabled
			if ($input['enabled'][$section->id])
			{
				// If it is, save the changefreq and priority values into settings
				$settings['sections'][$section->id] = array(
					'changefreq' => $input['changefreq'][$section->id],
					'priority' => $input['priority'][$section->id],
				);
			}
		}

		// Return the parsed settings ready for the database
		return $settings;
	}

	/**
	 * Register sitemap.xml as a route
	 */
	public function registerSiteRoutes()
	{
		return array(
			'sitemap.xml' => array(
				'action' => 'sitemap/sitemap/output'
			)
		);
	}
}
