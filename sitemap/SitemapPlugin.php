<?php

namespace Craft;

class SitemapPlugin extends BasePlugin
{
    /**
     * {@inheritdoc} IPlugin::getName()
     */
    public function getName()
    {
        return 'Sitemap';
    }

    /**
     * {@inheritdoc} IPlugin::getVersion()
     */
    public function getVersion()
    {
        return 'v1.0.0-alpha.4';
    }

    /**
     * {@inheritdoc} IPlugin::getDeveloper()
     */
    public function getDeveloper()
    {
        return 'Joshua Baker';
    }

    /**
     * {@inheritdoc} IPlugin::getDeveloperUrl()
     */
    public function getDeveloperUrl()
    {
        return 'http://joshuabaker.com/';
    }

    /**
     * {@inheritdoc} BaseSavableComponentType::defineSettings()
     */
    protected function defineSettings()
    {
        return array(
            'sections' => array(),
        );
    }

    /**
     * {@inheritdoc} BaseSavableComponentType::getSettingsHtml()
     */
    public function getSettingsHtml()
    {
        return craft()->templates->render('sitemap/_settings', array(
            'sections' => craft()->sitemap->sectionsWithUrls,
            'settings' => $this->settings,
        ));
    }

    /**
     * {@inheritdoc} BaseSavableComponentType::prepSettings()
     */
    public function prepSettings($input)
    {
        // We’re rewriting every time
        $settings = $this->defineSettings();

        // Loop through valid sections
        foreach (craft()->sitemap->sectionsWithUrls as $section) {
            // Check if the section is enabled
            if ($input['enabled'][$section->id]) {
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
     * Registers the /sitemap.xml route.
     *
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
