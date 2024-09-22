<?php
/**
 * Plugin Name: WooCommerce Delivery Options
 * Description: Dynamically adds delivery options on the checkout page, hides billing details until a delivery option is selected, and allows managing options in the admin.
 * Version:           1.0.0
 * Author:            Imagen Web Pro
 * Author URI:        https://imagenwebpro.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-delivery-options
 * Domain Path:       /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// WooCommerce dependency check
function wcdo_check_woocommerce_active() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'wcdo_woocommerce_missing_notice');
        deactivate_plugins(plugin_basename(__FILE__));
    }
}
add_action('admin_init', 'wcdo_check_woocommerce_active');

// Admin notice if WooCommerce is missing
function wcdo_woocommerce_missing_notice() {
    echo '<div class="error"><p><strong>' . esc_html__('WooCommerce Delivery Options requires WooCommerce to be installed and active.', 'wc-delivery-options') . '</strong></p></div>';
}

// Add a settings page to manage delivery options under the WooCommerce menu
add_action('admin_menu', 'wcdo_add_settings_page');
function wcdo_add_settings_page() {
    // Add as a submenu under WooCommerce
    add_submenu_page(
        'woocommerce', // Parent slug for WooCommerce
        __('Delivery Options Settings', 'wc-delivery-options'), // Page title
        __('Delivery Options', 'wc-delivery-options'), // Menu title
        'manage_options', // Capability
        'wcdo-settings', // Menu slug
        'wcdo_render_settings_page' // Callback function to render the page
    );
}

// Render the settings page for managing delivery options
function wcdo_render_settings_page() {
    if (isset($_POST['wcdo_save_options'])) {
        check_admin_referer('wcdo_save_options_verify');

        $delivery_options = array_map('sanitize_text_field', $_POST['delivery_options']);
        update_option('wcdo_delivery_options', $delivery_options);

        echo '<div class="notice notice-success is-dismissible"><p>' . __('Delivery options saved successfully.', 'wc-delivery-options') . '</p></div>';
    }

    $delivery_options = get_option('wcdo_delivery_options', []);
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Delivery Options Settings', 'wc-delivery-options'); ?></h1>
        <p><?php esc_html_e('Configure your delivery locations. These options will be available at checkout.', 'wc-delivery-options'); ?></p>
        
        <form method="post" id="wcdo-options-form">
            <?php wp_nonce_field('wcdo_save_options_verify'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="delivery-options"><?php esc_html_e('Delivery Options', 'wc-delivery-options'); ?></label></th>
                    <td>
                        <div id="delivery-options-wrapper">
                            <?php
                            if (!empty($delivery_options)) {
                                foreach ($delivery_options as $index => $option) {
                                    echo '<div class="delivery-option-row"><input type="text" name="delivery_options[]" value="' . esc_attr($option) . '" class="regular-text" placeholder="Enter delivery option" /> <button type="button" class="button remove-option">' . esc_html__('Remove', 'wc-delivery-options') . '</button></div>';
                                }
                            }
                            ?>
                        </div>
                        <button type="button" class="button button-primary" id="add-delivery-option"><?php esc_html_e('Add New Option', 'wc-delivery-options'); ?></button>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="wcdo_save_options" class="button-primary" value="<?php esc_attr_e('Save Options', 'wc-delivery-options'); ?>" />
            </p>
        </form>
    </div>

    <style>
        #wcdo-options-form {
            background: #fff;
            padding: 20px;
            border: 1px solid #e5e5e5;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        #delivery-options-wrapper {
            margin-top: 15px;
        }
        .delivery-option-row {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .delivery-option-row input {
            margin-right: 10px;
            flex: 1;
        }
        .delivery-option-row button {
            flex-shrink: 0;
        }
        .button-primary {
            margin-top: 15px;
        }
    </style>

    <script>
        (function($) {
            // Add new option field
            $('#add-delivery-option').on('click', function() {
                $('#delivery-options-wrapper').append('<div class="delivery-option-row"><input type="text" name="delivery_options[]" value="" class="regular-text" placeholder="Enter delivery option" /> <button type="button" class="button remove-option"><?php esc_html_e('Remove', 'wc-delivery-options'); ?></button></div>');
            });

            // Remove option field
            $(document).on('click', '.remove-option', function(e) {
                e.preventDefault();
                $(this).closest('.delivery-option-row').remove();
            });
        })(jQuery);
    </script>
    <?php
}

// Add custom dynamic delivery options before billing details, include "Deliver to me"
add_action('woocommerce_checkout_billing', 'wcdo_add_custom_delivery_options', 10);
function wcdo_add_custom_delivery_options() {
    $delivery_options = get_option('wcdo_delivery_options', []);

    echo '<div id="delivery_options">';
    echo '<h3 class="delivery-header">' . __('DELIVERY OPTIONS', 'wc-delivery-options') . '</h3>';
    echo '<ul class="delivery-options-list">';


    // Dynamically include other delivery options
    if (!empty($delivery_options)) {
        foreach ($delivery_options as $option) {
            $option_id = sanitize_title($option);
            echo '<li><input type="radio" name="delivery_option" value="' . esc_attr($option) . '" id="' . esc_attr($option_id) . '"> <label for="' . esc_attr($option_id) . '">' . esc_html($option) . '</label></li>';
        }
    }
    // Always include the "Deliver to me" option as a default
    echo '<li><input type="radio" name="delivery_option" value="deliver_to_me" id="deliver_to_me" checked> <label for="deliver_to_me">' . __('Deliver to me', 'wc-delivery-options') . '</label></li>';

    echo '</ul></div>';
}

// Hide billing form unless other delivery options exist or a delivery option is selected
add_action('wp_footer', 'wcdo_hide_billing_form_conditionally');
function wcdo_hide_billing_form_conditionally() {
    $delivery_options = get_option('wcdo_delivery_options', []);
    
    // Check if there are any dynamic options
    $has_other_options = !empty($delivery_options);

    ?>
    <script>
        jQuery(function($) {
            var hasOtherOptions = <?php echo $has_other_options ? 'true' : 'false'; ?>;

            if (hasOtherOptions) {
                // Hide billing fields initially when there are multiple options
                $('.woocommerce-billing-fields').hide();

                // Show billing fields after a delivery option is selected
                $('input[name="delivery_option"]').change(function() {
                    $('.woocommerce-billing-fields').show();
                });
            } else {
                // Always show billing fields when only "Deliver to me" is present
                $('.woocommerce-billing-fields').show();
            }
        });
    </script>
    <?php
}

// Save the selected delivery option in order meta
add_action('woocommerce_checkout_update_order_meta', 'wcdo_save_delivery_option');
function wcdo_save_delivery_option($order_id) {
    if (isset($_POST['delivery_option'])) {
        update_post_meta($order_id, '_delivery_option', sanitize_text_field($_POST['delivery_option']));
    }
}

// Add delivery option column to WooCommerce orders list
add_filter('manage_edit-shop_order_columns', 'wcdo_add_delivery_option_column');
function wcdo_add_delivery_option_column($columns) {
    $columns['delivery_option'] = __('Delivery Option', 'wc-delivery-options');
    return $columns;
}

// Populate the delivery option column in WooCommerce orders list
add_action('manage_shop_order_posts_custom_column', 'wcdo_show_delivery_option_column', 10, 2);
function wcdo_show_delivery_option_column($column, $post_id) {
    if ($column === 'delivery_option') {
        $delivery_option = get_post_meta($post_id, '_delivery_option', true);
        if ($delivery_option) {
            echo esc_html($delivery_option);
        } else {
            echo '—';
        }
    }
}

// Show delivery option in the order details on the admin order edit page
add_action('woocommerce_admin_order_data_after_order_details', 'wcdo_show_delivery_option_in_admin');
function wcdo_show_delivery_option_in_admin($order) {
    $delivery_option = get_post_meta($order->get_id(), '_delivery_option', true);
    if ($delivery_option) {
        echo '<p><strong>' . __('Delivery Option:', 'wc-delivery-options') . '</strong> ' . esc_html($delivery_option) . '</p>';
    }
}
