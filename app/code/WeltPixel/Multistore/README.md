# m2-weltpixel-multistore

### Installation

Dependencies:
 - m2-weltpixel-backend

With composer:

```sh
$ composer config repositories.welpixel-m2-weltpixel-multistore git git@github.com:rusdragos/m2-weltpixel-multistore.git
$ composer require weltpixel/m2-weltpixel-multistore:dev-master
```

Manually:

Copy the zip into app/code/WeltPixel/Multistore directory


#### After installation by either means, enable the extension by running following commands:

```sh
$ php bin/magento module:enable WeltPixel_Multistore --clear-static-content
$ php bin/magento setup:upgrade
```
