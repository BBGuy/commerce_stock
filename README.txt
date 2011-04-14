Commerce Stock Module
=====================

This module provides stock management for Drupal Commerce stores.

It currently does three things:

1. Form-alters the add-to-cart form so that out-of-stock items are marked.
2. Validates the add-to-cart form so that out-of-stock items cannot be added.
3. Optionally (via rules) decrements stock, as during order completion.

To configure:
-------------

1. Install and enable the module.
2. Add a field of type stock (and named field_stock) to your product.
3. Set the starting value of stock on each product.
4. (Optional) create a rule which decrements stock when an order is completed.

If you do #1-3, if the stock level is 0 or below, the item will display as
"out of stock" and adding it to the cart will be prevented.

To add a rule to decrement stock when an order completes:

* Edit the rule "Update the order status on order completion"
(admin/config/workflow/rules/reaction/manage/commerce_checkout_order_status_update)
* In Actions, add a loop acting on order:commerce-line-items
* Add an action to the loop: Adjust the product stock level, given a line item
* Configure the action to act on list-item.