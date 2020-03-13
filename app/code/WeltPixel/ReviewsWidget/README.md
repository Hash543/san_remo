# m2-reviews-widget

### Installation

Dependencies:
 - m2-weltpixel-backend

With composer:

```sh
$ composer config repositories.welpixel-m2-reviews-widget git git@github.com:rusdragos/m2-reviews-widget.git
$ composer require weltpixel/m2-reviews-widget:dev-master
```

Manually:

Copy the zip into app/code/WeltPixel/ReviewsWidget directory


#### After installation by either means, enable the extension by running following commands:

```sh
$ php bin/magento module:enable WeltPixel_ReviewsWidget --clear-static-content
$ php bin/magento setup:upgrade
```
