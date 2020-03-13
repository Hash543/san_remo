# m2-weltpixel-sitemap-extender

### Installation

Dependencies:
 - m2-weltpixel-backend

With composer:

```sh
$ composer config repositories.welpixel-m2-weltpixel-sitemap git git@github.com:rusdragos/m2-weltpixel-sitemap-extender.git
$ composer require weltpixel/module-sitemap:dev-master
```
Manually:

Copy the zip into app/code/WeltPixel/Sitemap directory

#### After installation by either means, enable the extension by running following commands:

```sh
$ php bin/magento module:enable WeltPixel_Sitemap --clear-static-content
$ php bin/magento setup:upgrade
```