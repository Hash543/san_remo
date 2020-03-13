# m2-weltpixel-searchautocomplete

### Installation

Dependencies:
 - m2-weltpixel-backend

With composer:

```sh
$ composer config repositories.m2-weltpixel-searchautocomplete git git@github.com:rusdragos/m2-weltpixel-searchautocomplete.git
$ composer require weltpixel/m2-weltpixel-searchautocomplet:dev-master
```

Manually:

Copy the zip into app/code/WeltPixel/SearchAutoComplete directory


#### After installation by either means, enable the extension by running following commands:

```sh
$ php bin/magento module:enable WeltPixel_SearchAutoComplete --clear-static-content
$ php bin/magento setup:upgrade
```
