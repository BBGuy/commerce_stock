Commerce Stock Module 7.x-2.0
==============================
This module provides stock management for Drupal Commerce stores.

This module includes three modules:
- Commerce Stock API: A common API for managing stock using sub modules.
  Implements validation events and actions.
- Commerce Simple Stock: A basic stock sub-module providing a stock field.
- Commerce Simple Stock Rules: A set of rules to control stock (it can be
  configured by modifying the rules or creating new ones). Varlidation rules
  created:
  - Disabling add to cart
  - Validate add to cart
  - Validate checkout


To install and get working
============================
1. Download commerce_stock.
2. Enable the Commerce Stock API, Commerce Simple Stock, and Commerce Simple
   Stock Rules modules.
3. Go to people > permissions  and make sure that that you and other relevent
   roles have the "Administer commerce stock settings".
4. Go to Home > Administration > Store > Configuration > Stock management.
5. Select the "simple stock management" tab.
6. Check the product types you want simple stock to manage and hit submit.


Important:
  You may need to clear caches after installing and enabling stock for your
  products. Rules will show errors for the stock rules until you enable stock on
  at least one product.

If you want to be able to disable stock checking for individual products check
the "Allow stock override for Product <product>".

To Uninstall
=============
1. Go to Home > Administration > Store > Configuration > Stock management.
2. Select the "simple stock management" tab.
3. Un-Check all product types hit submit and confirm the "I understand that all
   stock data will be permanently removed".
4. go to the modules page & disable all stock modules.
5. Go to the uninstall tab and uninstall all stock modules.

Notes on Uninstall:
* If you are planing on using a different version of stock or replace the stock
  module with another / a custom system, you can keep the stock field and skip
  steps 1 to 3. the stock field will be preserved and you will be able to use it
  as any other drupal field.
* If you forgot to follow steps 1 to 3 before uninstalling you can visit each of
  the product bundles and delete the stock field from each of those.


rules configuration
===================
If you need to make changes to rules you also need the permission
"Make rule based changes to commerce stock".
to view and edit the rules see:
admin/commerce/config/stock/validation
and
admin/commerce/config/stock/control

Decimal Stock
=============
The editing of stock levels support decimal quantities, to enable this feature
edit a product type (admin/commerce/products/types) and check the box
"Allow decimal quantities for stock administration"
to support decimal quantities on the add to basket use the module
https://drupal.org/project/commerce_decimal_quantities
