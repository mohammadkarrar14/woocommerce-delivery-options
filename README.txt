=== WooCommerce Delivery Options ===
Developer: https://muhammadkarrar.com/
Tags: woocommerce, delivery, checkout options, billing, shipping  
Requires at least: 3.0.1
Tested up to: 6.7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WooCommerce Delivery Options allows you to add custom delivery options on the WooCommerce checkout page. The "Deliver to me" option is always included by default. The plugin hides billing fields until a delivery option is selected.

== Description ==

**WooCommerce Delivery Options** enhances your WooCommerce checkout page by providing dynamic delivery options. The plugin ensures that the "Deliver to me" option is always displayed, and you can manage additional delivery options through a settings page in the WordPress dashboard.

Key Features:
- Add custom delivery options dynamically from the admin settings.
- Display "Deliver to me" as a default option.
- Billing fields are hidden until a delivery option is selected (unless only "Deliver to me" is available).
- Save the selected delivery option to WooCommerce order meta.
- Display the selected delivery option on the order details page and in the WooCommerce admin order list.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/woocommerce-delivery-options` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Ensure that WooCommerce is installed and active.
4. Go to WooCommerce > Delivery Options to configure your custom delivery options.

== Usage ==

1. **Configure Delivery Options**: Go to WooCommerce > Delivery Options in the WordPress admin to add custom delivery locations.
   - The "Deliver to me" option is always present and will be shown by default on the checkout page.
   - You can add as many custom delivery options as needed.
   
2. **Checkout Behavior**: On the checkout page:
   - The "Deliver to me" option is selected by default.
   - Billing fields are hidden until a delivery option is selected. If only "Deliver to me" is available, billing fields are always displayed.

3. **Order Meta**: The selected delivery option is saved as part of the order meta data and can be viewed on the order details page and in the WooCommerce order list.

== Frequently Asked Questions ==

= Does this plugin work with WooCommerce? =
Yes, the WooCommerce Delivery Options plugin requires WooCommerce to be installed and active. If WooCommerce is deactivated, the plugin will not function.

= Can I add multiple delivery options? =
Yes, you can add as many delivery options as you like from the WooCommerce > Delivery Options settings page.

= What happens if I don't configure any delivery options? =
The "Deliver to me" option will always be available by default, even if no other delivery options are configured.

== Changelog ==

= 1.0.0 =
* Initial release.

