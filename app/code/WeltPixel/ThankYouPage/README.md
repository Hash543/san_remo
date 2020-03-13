# m2-weltpixel-thankyou-page

### Installation

Dependencies:
 - m2-weltpixel-backend

With composer:

```sh
$ composer config repositories.welpixel-m2-weltpixel-thankyou-page git git@github.com:rusdragos/m2-weltpixel-thankyou-page.git
$ composer require weltpixel/m2-thankyou-page:dev-master
```

Manually:

Copy the zip into app/code/WeltPixel/ThankYouPage directory


#### After installation by either means, enable the extension by running following commands:

```sh
$ php bin/magento module:enable WeltPixel_ThankYouPage --clear-static-content
$ php bin/magento setup:upgrade
```
