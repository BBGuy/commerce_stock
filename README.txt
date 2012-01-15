Commerce Stock Module 7.x-2.0
==============================

This module provides stock management for Drupal Commerce stores.


This module includes two modules:

Commerce Stock API: A common API for managing stock using sub modules

Commerce Simple stock modules: A basic stock sub module providing a stock field and a set of rules to control stock (it can be configured by modifying the rules or creating new once)


To install and get working
============================
1. download commerce_stock
2. Enable the Commerce Stock API & Commerce Simple stock modules
3. Go to Home » Administration » Store » Configuration » Stock management
4. Select the “simple stock management” tab
5. Check the product types you wont simple stock to manage and hit submit

Important: you may need to clear caches after installing and enabeling stock for your products 

if you wont to be able to disable stock checking for individual products check the “Allow stock override for Product (product)”
