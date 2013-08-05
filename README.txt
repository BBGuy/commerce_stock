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


rules configuration
===================
If you need to make changes to rules you also need the permission
"Make rule based changes to commerce stock".
