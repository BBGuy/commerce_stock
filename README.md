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
    * Commerce Stock Enforcement (optional)

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
    * "Allowed number of values" is set to 1 - "Save field settings"
    * "Save settings" one last time
If you have more product variation types:
   * Reuse the field that was created for the Default Product variation type:
     "field_stock_level"

### Other configuration

#### Using Widgets for updating stock
Commerce stock comes with three widgets for stock level editing.
To set the widget, go to the "Manage form display" of the product variation
type you want to set and use the Widget drop-down of the "Stock Level" field.

The following are the widgets and their functionality:

"Absolute stock level" - This is the equivalent to the Drupal 7 version and
allows setting the current stock level. The list secure approach and not
recommended for live sites as other stock transactions can occur from the point
a stock count was made and the entering of the data. Can be handy for priming a
new site and stock takes while in maintenance mode.

"Simple stock transaction" - A simple form for creating transactions. Allows
for entering of positive (stock in) and negative (stock out) transactions.
Targeted at simple sites that don't require much extra metadata about their
transactions.

"Link to stock transaction form" - This provides a link to a transaction form
providing full transaction details.

Both "Absolute stock level" and "Simple stock transaction" also have the
options: "Allow custom note per transaction." and "Allow decimal quantities".
This and more are available on the "stock transaction form" so not needed as
an options for the "Link to stock transaction form" widget.

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

How to add a stock locations reference field.
1. Add a new field
2. Select Reference: Other.
3. Enter the label and make sure the machine name is correct.
4. Set the "Type of item to reference" to "Stock Location" and the "Allowed number of values"
5. Press Save Field settings
6. Set the "Stock location type"
7. Press Save Setting


You can then edit each of the stores and set the locations.
