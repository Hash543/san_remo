# m2-weltpixel-instagramwidget

### Installation

Dependencies:
 - m2-weltpixel-backend

With composer:

```sh
$ composer config repositories.welpixel-m2-weltpixel-instagramwidget git git@github.com:rusdragos/m2-instagramwidget.git
$ composer require weltpixel/module-instagramwidget:dev-master
```
Manually:

Copy the zip into app/code/WeltPixel/InstagramWidget directory

#### After installation by either means, enable the extension by running following commands:

```sh
$ php bin/magento module:enable WeltPixel_InstagramWidget --clear-static-content
$ php bin/magento setup:upgrade
```