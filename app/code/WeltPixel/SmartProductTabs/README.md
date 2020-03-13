# m2-weltpixel-smartproducttabs

### Installation

Dependencies:
 - m2-weltpixel-backend

With composer:

```sh
$ composer config repositories.welpixel-m2-weltpixel-smartproducttabs git git@github.com:rusdragos/m2-smartblock.git
$ composer require weltpixel/module-smartproducttabs:dev-master
```
Manually:

Copy the zip into app/code/WeltPixel/SmartProductTabs directory

#### After installation by either means, enable the extension by running following commands:

```sh
$ php bin/magento module:enable WeltPixel_SmartProductTabs --clear-static-content
$ php bin/magento setup:upgrade
```