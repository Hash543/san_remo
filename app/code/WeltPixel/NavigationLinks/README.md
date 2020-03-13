# m2-weltpixel-navigation-links

### Installation

With composer:

```sh
$ composer config repositories.welpixel-m2-weltpixel-navigation-links git git@github.com:rusdragos/m2-weltpixel-navigation-links.git
$ composer require weltpixel/m2-weltpixel-navigation-links:dev-master
```

Manually:

Copy the zip into app/code/WeltPixel/NavigationLinks directory


#### After installation by either means, enable the extension by running following commands:

```sh
$ php bin/magento module:enable WeltPixel_NavigationLinks --clear-static-content
$ php bin/magento setup:upgrade
```
