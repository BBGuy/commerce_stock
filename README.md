Commerce Stock Module
=====================

This module provides stock management for Drupal Commerce stores.

It currently does these things:

1. Provides a UI at admin/commerce/config/stock that lets you determine which
   product types should have stock management. If you turn on stock management
   a stock field is added to the product type.
2. Form-alters the add-to-cart and cart forms so that out-of-stock items are 
   marked and you can't but something that is out of stock (or buy more than
   stock allows).
3. A default rule is provided that decrements the stock value when the order
   is completed. If you want to decrement it at a different time, you can 
   change the rule or provide your own.

To configure:
-------------

1. Install and enable the module.
2. Enable stock management on the products types you want it on by visiting
   admin/commerce/config/stock.
3. Set the starting value of stock on each product, using the product edit form,
   commerce_feeds, commerce_migrate, VBO, or whatever works.

If the stock level is 0 or below, the item will display as "out of stock" and 
adding it to the cart will be prevented.

If you would like a rule other than the default rule for updating stock levels,
you can edit or replace the default rule.