# m2-weltpixel-design-elements

### Installation

Dependencies:
 - m2-weltpixel-backend

With composer:

```sh
$ composer config repositories.welpixel-m2-weltpixel-design-elements git git@github.com:rusdragos/m2-weltpixel-design-elements.git
$ composer require weltpixel/m2-weltpixel-design-elements:dev-master
```

Manually:

Copy the zip into app/code/WeltPixel/DesignElements directory


#### After installation by either means, enable the extension by running following commands:

```sh
$ php bin/magento module:enable WeltPixel_DesignElements --clear-static-content
$ php bin/magento setup:upgrade
```
