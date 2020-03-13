# m2-weltpixel-sample-data

### Installation

With composer:

```sh
$ composer config repositories.welpixel-m2-weltpixel-sample-data git git@github.com:rusdragos/m2-weltpixel-sample-data.git
$ composer require weltpixel/m2-weltpixel-sample-data:dev-master
```

Manually:

Copy the zip into app/code/WeltPixel/SampleData directory


#### After installation by either means, enable the extension by running following commands:

```sh
$ php bin/magento module:enable WeltPixel_SampleData --clear-static-content
$ php bin/magento setup:upgrade
```
