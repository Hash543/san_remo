# m2-weltpixel-category-page

### Installation

Dependencies:
 - m2-weltpixel-backend

With composer:

```sh
$ composer config repositories.welpixel-m2-weltpixel-category-page git git@github.com:rusdragos/m2-weltpixel-category-page.git
$ composer require weltpixel/m2-weltpixel-category-page:dev-master
```

Manually:

Copy the zip into app/code/WeltPixel/CategoryPage directory


#### After installation by either means, enable the extension by running following commands:

```sh
$ php bin/magento module:enable WeltPixel_CategoryPage --clear-static-content
$ php bin/magento setup:upgrade
```
