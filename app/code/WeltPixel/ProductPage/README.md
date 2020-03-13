# m2-weltpixel-product-page

### Installation

Dependencies:
 - m2-weltpixel-backend
 - m2-weltpixel-frontend-options

With composer:

```sh
$ composer config repositories.welpixel-m2-weltpixel-product-page git git@github.com:rusdragos/m2-weltpixel-product-page.git
$ composer require weltpixel/m2-weltpixel-product-page:dev-master
```

Manually:

Copy the zip into app/code/WeltPixel/ProductPage directory


#### After installation by either means, enable the extension by running following commands:

```sh
$ php bin/magento module:enable WeltPixel_ProductPage --clear-static-content
$ php bin/magento setup:upgrade
```
