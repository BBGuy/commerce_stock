# Commerce stock
The following are basic instructions for the dev version of the stock module.
Feel free to suggest changes!

### Install the module
Install with [Composer](https://getcomposer.org/) (recommended):
```
composer require 'drupal/commerce_stock:^1.0'
```
or

Download or clone the
[Commerce stock project](https://www.drupal.org/project/commerce_stock)

### Standard setup for Commerce stock
1. Enable the following modules
    * Commerce Stock API
    * Commerce Stock Field
    * Commerce Stock Local Storage
    * Commerce Stock UI

2. Commerce >> Configuration >> Stock >> Stock configuration
    * Set Default service to "Local stock" (optionally select Local stock only
    for product variations that should be controlled by stock)

3. Commerce >> Configuration >> Products >> Product variation types assuming you
 only have Default Product variation type
    * Manage fields
    * Add field
    * Select "Stock Level" under the "General" section and name the field "stock
     level"
    * Save and continue
    * Set "Allowed number of values" to 1 and "Save field settings"
    * "Save settings" one last time

### Other configuration

#### Event handling
By default, the stock system reacts only on "order complete" events - creates a
negative transaction resulting with that stock no longer available.
You can enable 2 more events by going to:
Commerce >> Configuration >> Stock >> Stock configuration
  * Automatically return stock on cancel - Creates a positive stock transaction
  and makes the stock available again
  * Adjust stock on order updates (after the order was completed) - Allows to
  modify a placed order and any changes to quantities will get reflected in
  stock levels.

#### Stock enforcement
By default, the stock system allows stock to go into negative (i.e. a user can
purchase 10 items if the product has only 5 in stock). To have the module
enforce the stock levels you must enable Commerce Stock Enforcement module.

#### Support multiple stores
Each store will have a primary location for creating transactions against. Each
store will have a list of locations available for fulfilment (this is for
checking of stock not for creating transactions).

To support multiple stores you must add the following fields to relevant Store
types (we may automate this later on):
  *  Available stock locations (field_available_stock_locations) - Entity
  reference to stock location - unlimited
  *  Stock allocation location (field_stock_allocation_location) - Entity
  reference to stock location - 1

You can then edit each of the stores and set the locations.
