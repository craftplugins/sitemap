# Craft Sitemap

A simple plugin for [Craft](http://craftcms.com) that generates a [sitemap.xml](http://www.sitemaps.org/) based on enabled sections.

![Settings](http://i.imgur.com/DhXTn2f.jpg)

## Installation

1. Copy the `sitemap/` folder into `craft/plugins/`
2. Go to Settings → Plugins and click the “Install” button next to “Sitemap”

## Usage

Within the plugin settings, check the boxes in the “Enabled” column to include them in the sitemap.

To view the output visit `/sitemap.xml`.

## Advanced

This plugin exposes various [service methods](#service-methods), which can be used to add custom items to the sitemap through the [`renderSitemap` hook](#rendersitemap). Please read the official [‘Hooks and Events’ documentation](http://buildwithcraft.com/docs/plugins/hooks-and-events), if you’re not sure how this works.

### Hooks

##### `renderSitemap`

Add a `renderSitemap` method to your plugin to add items via the various [service methods](#service-methods) listed below.

Here’s an example plugin hook method with comments:

```php
public function renderSitemap()
{
    // Get an ElementCriteriaModel from the ElementsService
    $criteria = craft()->elements->getCriteria(ElementType::Entry);

    // Specify that we want entries within the ‘locations’ section
    $criteria->section = 'locations';

    // Loop through any entries that were found
    foreach ($criteria->find() as $locationEntry)
    {
        // Here we’re building a path using the entry slug.
        // This might match a custom route you’ve defined that
        // should be included in the sitemap.
        $path = 'cars-for-sale-in-' . $locationEntry->slug;

        // Make sure that we’re using a full URL, not just the path.
        $url = UrlHelper::getSiteUrl($path);

        // For the sake of this example, we’re setting the $lastmod
        // value to the most recent time the location entry was
        // updated. You can pass any time using the DateTime class.
        $lastmod = $locationEntry->dateUpdated;

        // Add the URL to the sitemap
        craft()->sitemap->addUrl($url, $lastmod, Sitemap_ChangeFrequency::Daily, 0.5);
    }
}
```

And here’s an example of the resulting element in the sitemap XML:

```xml
<url>
  <loc>http://example.com/cars-for-sale-in-scotland</loc>
  <lastmod>2015-08-28T15:08:28+00:00</lastmod>
</url>
```

### Service Methods

There’s several service methods made available to add items to the sitemap.

##### `addUrl($loc, $lastmod, [$changefreq, [$priority]])`
Adds a URL to the sitemap.

```php
$loc = UrlHelper::getSiteUrl('special/route');
$lastmod = new DateTime('now');
craft()->sitemap->addUrl($loc, $lastmod, Sitemap_ChangeFrequency::Yearly, 0.1);
```

##### `addElement(BaseElementModel $element, [$changefreq, [$priority]])`
Adds an element to the sitemap.

```php
$element = craft()->elements->getElementById(2);
craft()->sitemap->addElement($element, Sitemap_ChangeFrequency::Daily, 1.0);
```

##### `addSection(SectionModel $section, [$changefreq, [$priority]])`
Adds all entries in the section to the sitemap.

```php
$section = craft()->sections->getSectionByHandle('homepage');
craft()->sitemap->addSection($section, Sitemap_ChangeFrequency::Weekly, 1.0);
```

##### `addCategoryGroup(CategoryGroupModel $categoryGroup, [$changefreq, [$priority]])`
Adds all categories in the group to the sitemap.

```php
$group = craft()->categories->getGroupByHandle('news');
craft()->sitemap->addCategoryGroup($group);
```

##### `getElementUrlForLocale(BaseElementModel $element, $locale)`
Gets a element URL for the specified locale. The locale must be enabled.

```php
echo $element->url;
// http://example.com/en/hello-world

echo craft()->sitemap->getElementUrlForLocale($element, 'fr');
// http://example.com/fr/bonjour-monde
```

##### `getUrlForLocale($path, $locale)`
Gets a URL for the specified locale. The locale must be enabled.

```php
echo UrlHelper::getSiteUrl('foo/bar');
// http://example.com/en/foo/bar

echo craft()->sitemap->getUrlForLocale('foo/bar', 'fr');
// http://example.com/fr/foo/bar
```

#### Helper Classes

##### `Sitemap_ChangeFrequency`
Enumeration of valid `changefreq` values.

```php
Sitemap_ChangeFrequency::Always
Sitemap_ChangeFrequency::Hourly
Sitemap_ChangeFrequency::Daily
Sitemap_ChangeFrequency::Weekly
Sitemap_ChangeFrequency::Monthly
Sitemap_ChangeFrequency::Yearly
Sitemap_ChangeFrequency::Never
```
