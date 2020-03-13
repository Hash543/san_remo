# m2-weltpixel-custom-header

### Installation

Dependencies:
 - m2-weltpixel-backend

With composer:

```sh
$ composer config repositories.welpixel-m2-weltpixel-custom-header git git@github.com:rusdragos/m2-weltpixel-custom-header.git
$ composer require weltpixel/m2-weltpixel-custom-header:dev-master
```

Manually:

Copy the zip into app/code/WeltPixel/CustomHeader directory


#### After installation by either means, enable the extension by running following commands:

```sh
$ php bin/magento module:enable WeltPixel_CustomHeader --clear-static-content
$ php bin/magento setup:upgrade
```
