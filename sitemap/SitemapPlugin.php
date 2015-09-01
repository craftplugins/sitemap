<?php
namespace Craft;

class SitemapPlugin extends BasePlugin
{
    /**
     * @inheritDoc IPlugin::getName()
     */
    public function getName()
    {
        return 'Sitemap';
    }

    /**
     * @inheritDoc IPlugin::getVersion()
     */
    public function getVersion()
    {
        return 'v1.0.0-alpha.1';
    }

    /**
     * @inheritDoc IPlugin::getDeveloper()
     */
    public function getDeveloper()
    {
        return 'Joshua Baker';
    }

    /**
     * @inheritDoc IPlugin::getDeveloperUrl()
     */
    public function getDeveloperUrl()
    {
        return 'http://joshuabaker.com/';
    }

    /**
     * @inheritDoc BaseSavableComponentType::defineSettings()
     */
    protected function defineSettings()
    {
        return array(
            'sections' => array(),
        );
    }

    /**
     * @inheritDoc BaseSavableComponentType::getSettingsHtml()
     */
    public function getSettingsHtml()
    {
        return craft()->templates->render('sitemap/_settings', array(
            'sections' => craft()->sitemap->sectionsWithUrls,
            'settings' => $this->settings,
        ));
    }

    /**
     * @inheritDoc BaseSavableComponentType::prepSettings()
     */
    public function prepSettings($input)
    {
        // We’re rewriting every time
        $settings = $this->defineSettings();

		// Loop through valid sections
		foreach (craft()->sitemap->sectionsWithUrls as $section)
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
     * Registers the /sitemap.xml route
     * @return array
     */
    public function registerSiteRoutes()
    {
        return array(
            'sitemap.xml' => array(
                'action' => 'sitemap/sitemap/output',
            ),
        );
    }
}
