1.3.2
=============
* Improvements:
    * Implement default values. Admin can set default simple product on configurable product edit form.
    
* Fixed bugs:
    * Fixed an issue with swatch and configurable renderer.
    * Fixed an issue with identical cache on simple product pages.
    * Fixed an issue with swatches on category pages.
    
* Code quality improvements:
    * Fixed code violations according to Magento Extension Quality Program

1.4.0
=============
* Improvements:
    * Add matrix grid for configurable products.
    * Add new config in admin panel for options matrix.
    * Add tier price for matrix grid.
    * Disable default magento qty field and stock status for products.
    * Now when you add to the cart add all products that have been specified field qty in matrix.
    * Grid displaying columns: option, Available qty, Stock Status, Price, Tier Price(if enable in config.), Qty.
    * Add configurable product from category page. 
    * If disable Manage Stock in magento, Available qty column hide.
    * Add custom shipping logic with displaying attributes in text input.
* Fixed bugs:
    * Stable work with module Magenerds_BasePrise.
    * Added scrolling for matrix grid when table more than display.
    * Remove QTY field if option not available.
    * "in Stock" text change to "In Stock".
    * Validate qty fields in matrix.
    * Fixed Custom content options value in frontend.
    * Fixed issue with cacheable block.
    * Fixed style for matrix.
    
1.4.4
=============
* Improvements:
    * Added compatibility with Mirasvit_GiftRegistry.
    * Added new fields in edit product page for custom attributes.
* Fixed bugs:
    * Fixed bugs with any custom attributes types.
    * Fixed issue with the same attribute type in frontend.
    
1.4.5
=============
* Improvements:
    * Added the ability to link configurable products to the same attributes on the product bundle page.
    * Increased speed of loading the product page
    * Added changing some metadata when changing a variation of configurable product
    * Added the ability to choose whether custom content will be added existing content or whether existing content will be replaced
* Fixed bugs:
    * Bug when bundle products disappear after placing an order.
    * Bug when to add a configurable product with two or more attributes to the cart (Error: "You need to choose options for your item.").
    * Issue with incorrect currency display in bundle products
    * Issue with incorrect price update in the matrix
    * Issue on configurable product page on magento version 2.2.7
    * Wrong image order when changing swatches in configurable product
    * Issue with video playback in gallery
    * Price on List page doesn't show correctly when Tax is enabled.
    * Issue where a configurable product was shown as an "Out of stock", when all its variations have required custom options
    * Error in the admin panel when editing a bundle product on the Magento version 2.3
    * Issue with re-index when tables in DB have prefix
    * Issues with different product bundle options
    * Added separation of cart positions when adding a bundle product, which has an option with a configurable product
    * Added information about the selected options of the configurable product that is included in the bundle product in Admin/Sales/Order
    * Fix Bug with default simple product id not getting removed when all the options are deselected and clicked on add to cart.
    * Fixed problem when adding bundle product to the cart when the matrix enabled
    * Fixed display of custom options when the matrix enabled
    * Fixed display of some custom options (date, date_time, time, area, field)
    * Fixed display of the matrix, if the option of the visual swatch is not set
    * Fixed issue with configurable product options when added to bundle product
    * Fixed an issue that occurred when a user parameter has a price in percent, and the product is included in the package

