# m2-weltpixel-ajax-infinite-scroll

### Installation

Dependencies:
 - m2-weltpixel-backend

With composer:

```sh
$ composer config repositories.m2-weltpixel-ajax-infinite-scroll git git@github.com:rusdragos/m2-weltpixel-ajax-infinite-scroll.git
$ composer require weltpixel/m2-weltpixel-ajax-infinite-scroll:dev-master
```

Manually:

Copy the zip into app/code/WeltPixel/AjaxInfiniteScroll directory


#### After installation by either means, enable the extension by running following commands:

```sh
$ php bin/magento module:enable WeltPixel_AjaxInfiniteScroll --clear-static-content
$ php bin/magento setup:upgrade
```
