# m2-weltpixel-h1-title-rewrite

### Installation

Dependencies:

- m2-weltpixel-backend


With composer:

```sh
$ composer config repositories.m2-weltpixel-h1-title-rewrite git git@github.com:rusdragos/m2-weltpixel-h1-title-rewrite.git
$ composer require weltpixel/m2-weltpixel-h1-title-rewrite:dev-master
```

Manually:

Copy the zip into app/code/WeltPixel/TitleRewrite directory


#### After installation by either means, enable the extension by running following commands:

```sh
$ php bin/magento module:enable WeltPixel_TitleRewrite --clear-static-content
$ php bin/magento setup:upgrade
```
