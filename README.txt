Commerce Stock Module 7.x-2.0
==============================

This module provides stock management for Drupal Commerce stores.


This module includes two modules:

Commerce Stock API: A common API for managing stock using sub modules.

Commerce Simple stock modules: A basic stock sub-module providing a stock field and a set of rules to control stock (it can be configured by modifying the rules or creating new ones).


To install and get working
============================
1. Download commerce_stock
2. Enable the Commerce Stock API & Commerce Simple stock and Commerce Simple stock rules modules
3. Go to Home » Administration » Store » Configuration » Stock management
4. Select the “simple stock management” tab
5. Check the product types you want simple stock to manage and hit submit

Important: 
  You may need to clear caches after installing and enabling stock for your products.
  Rules will show errors for the stock rules until you enable stock on at least one product.

If you want to be able to disable stock checking for individual products check the “Allow stock override for Product (product)”


About the modules:
Commerce Stock API: The Stock API used by all modules. Implements validation events and actions.

Commerce Simple stock: Creates a stock field and a rule to decrement it on order completion

Commerce Simple stock rules: Provides validation rules for:
 Disabling add to cart
 Validate add to cart
 Validate checkout 
