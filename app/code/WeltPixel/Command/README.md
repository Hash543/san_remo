# m2-weltpixel-command

### Installation

Dependencies:
 - m2-weltpixel-backend

With composer:

```sh
$ composer config repositories.welpixel-m2-weltpixel-command git git@github.com:rusdragos/m2-weltpixel-command.git
$ composer require weltpixel/module-command:dev-master
```

Manually:

Copy the zip into app/code/WeltPixel/Command directory


#### After installation by either means, enable the extension by running following commands:

```sh
$ php bin/magento module:enable WeltPixel_Command --clear-static-content
$ php bin/magento setup:upgrade
```
