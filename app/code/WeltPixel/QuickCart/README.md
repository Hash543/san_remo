# m2-weltpixel-quickcart

### Installation

Dependencies:
 - m2-weltpixel-backend

With composer:

```sh
$ composer config repositories.welpixel-m2-weltpixel-quickcart git git@github.com:rusdragos/m2-weltpixel-quickcart.git
$ composer require weltpixel/m2-weltpixel-quickcart:dev-master
```

Manually:

Copy the zip into app/code/WeltPixel/QuickCart directory


#### After installation by either means, enable the extension by running following commands:

```sh
$ php bin/magento module:enable WeltPixel_QuickCart --clear-static-content
$ php bin/magento setup:upgrade
```
