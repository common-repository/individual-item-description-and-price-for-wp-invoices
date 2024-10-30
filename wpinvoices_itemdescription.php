<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.iflair.com
 * @since             1.0.0
 * @package           Wpinvoices_itemdescription
 *
 * @wordpress-plugin
 * Plugin Name:       Individual item description and price for WP Invoices
 * Plugin URI:        https://profiles.wordpress.org/iflairwebtechnologies
 * Description:       It's stable add on plugin provide option for Wp Invoices to change individual item description and price, it's great add on feature who using wp invoices plugin to add description and price and it's helps to your account.
 * Version:           1.0.0
 * Author:            iFlair Web Technologies Pvt. Ltd.
 * Author URI:        https://www.iflair.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       Individual item description and price for WP Invoices
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WPINVOICES_ITEMDESCRIPTION_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wpinvoices_itemdescription-activator.php
 */
function activate_wpinvoices_itemdescription() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpinvoices_itemdescription-activator.php';
 // Require parent plugin
    if ( ! is_plugin_active( 'invoicing/invoicing.php' ) and current_user_can( 'activate_plugins' ) ) {
        // Stop activation redirect and show error
        wp_die('Sorry, but this plugin requires the Invoicing Plugin to be installed and active. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
    }
	Wpinvoices_itemdescription_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wpinvoices_itemdescription-deactivator.php
 */
function deactivate_wpinvoices_itemdescription() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpinvoices_itemdescription-deactivator.php';
	Wpinvoices_itemdescription_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wpinvoices_itemdescription' );
register_deactivation_hook( __FILE__, 'deactivate_wpinvoices_itemdescription' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wpinvoices_itemdescription.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wpinvoices_itemdescription() {

	$plugin = new Wpinvoices_itemdescription();
	$plugin->run();

}
run_wpinvoices_itemdescription();



function wpinv_dp_admin_get_line_items($invoice)
{
    $item_quantities    = wpinv_item_quantities_enabled();
    $use_taxes          = wpinv_use_taxes();

    if ( empty( $invoice ) ) {
        return NULL;
    }

    $cart_items = $invoice->get_cart_details();
    if ( empty( $cart_items ) ) {
        return NULL;
    }
    ob_start();

    do_action( 'wpinv_admin_before_line_items', $cart_items, $invoice );

    $count = 0;
    foreach ( $cart_items as $key => $cart_item ) {
        $item_id    = $cart_item['id'];
        $wpi_item   = $item_id > 0 ? new WPInv_Item( $item_id ) : NULL;

        if (empty($wpi_item)) {
            continue;
        }

        $item_price     = wpinv_price( wpinv_format_amount( $cart_item['item_price'] ), $invoice->get_currency() );
        $quantity       = !empty( $cart_item['quantity'] ) && $cart_item['quantity'] > 0 ? $cart_item['quantity'] : 1;
        $item_price_f     = $cart_item['item_price'] * $quantity;
        $item_subtotal  = wpinv_price( wpinv_format_amount( $cart_item['subtotal'] ), $invoice->get_currency() );
        $custom_priceArr = (get_post_meta($invoice->ID, 'dp_price')) ? get_post_meta($invoice->ID, 'dp_price')[0] : 0;

        if(isset($custom_priceArr[$item_id]) && $custom_priceArr[$item_id] != 0)
        {
            $item_price = wpinv_price( wpinv_format_amount($custom_priceArr[$item_id]));
            $item_price_f = $custom_priceArr[$item_id] * $quantity;
            $item_subtotal = wpinv_price( wpinv_format_amount($custom_priceArr[$item_id] * $quantity));
        }

        if(isset($_POST['extraPrice'][$item_id]) && abs($_POST['extraPrice'][$item_id]) != 0)
        {
            $item_price = wpinv_price( wpinv_format_amount(abs($_POST['extraPrice'][$item_id])), $invoice->get_currency());
            $item_price_f = abs($_POST['extraPrice'][$item_id]) * $quantity;

            $item_subtotal = wpinv_price( wpinv_format_amount(abs($_POST['extraPrice'][$item_id]) * $quantity), $invoice->get_currency());
        }
        $can_remove     = true;

        $summary = apply_filters( 'wpinv_admin_invoice_line_item_summary', '', $cart_item, $wpi_item, $invoice );

        $item_tax       = '';
        $tax_rate       = '';
        if ( $cart_item['vat_rate'] > 0 && $item_price_f > 0 ) {
            $item_tax = wpinv_price( wpinv_format_amount( $cart_item['vat_rate'] * $item_price_f / 100 ), $invoice->get_currency() );
            $tax_rate = !empty( $cart_item['vat_rate'] ) ? $cart_item['vat_rate'] : ( $cart_item['tax'] / $item_price_f ) * 100;
            $tax_rate = $tax_rate > 0 ? (float)wpinv_round_amount( $tax_rate, 4 ) : '';
            $tax_rate = $tax_rate != '' ? ' <span class="tax-rate">(' . $tax_rate . '%)</span>' : '';
        }
        $line_item_tax = $item_tax . $tax_rate;

        if ( $line_item_tax === '' ) {
            $line_item_tax = 0; // Zero tax
        }

        $line_item = '<tr class="item item-' . ( ($count % 2 == 0) ? 'even' : 'odd' ) . '" data-item-id="' . $item_id . '">';
            $line_item .= '<td class="id">' . $item_id . '</td>';
            $line_item .= '<td class="title"><a href="' . get_edit_post_link( $item_id ) . '" target="_blank">' . $cart_item['name'] . '</a>' . wpinv_get_item_suffix( $wpi_item );
            if ( $summary !== '' ) {
                $line_item .= '<span class="meta1">' . $summary . '</span>';
            }
            $line_item .= '</td>';
            $line_item .= '<td class="price">' . $item_price . '</td>';
            
            if ( $item_quantities ) {
                if ( count( $cart_items ) == 1 && $quantity <= 1 ) {
                    $can_remove = false;
                }
                $line_item .= '<td class="qty" data-quantity="' . $quantity . '">&nbsp;&times;&nbsp;' . $quantity . '</td>';
            } else {
                if ( count( $cart_items ) == 1 ) {
                    $can_remove = false;
                }
            }
            $line_item .= '<td class="total">' . $item_subtotal . '</td>';
            
            if ( $use_taxes ) {
                $line_item .= '<td class="tax">' . $line_item_tax . '</td>';
            }
            $line_item .= '<td class="action">';
            if ( !$invoice->is_paid() && !$invoice->is_refunded() && $can_remove ) {
                $line_item .= '<i class="fa fa-remove wpinv-dp-item-remove"></i>';
            }
            $line_item .= '</td>';
        $line_item .= '</tr>';

        echo apply_filters( 'wpinv_admin_line_item', $line_item, $cart_item, $invoice );

        $count++;
    } 

    do_action( 'wpinv_admin_after_line_items', $cart_items, $invoice );

    return ob_get_clean();
}


function wpinv_dp_display_line_items( $invoice_id = 0 ) {
    global $wpinv_euvat, $ajax_cart_details;
    $invoice            = wpinv_get_invoice( $invoice_id );
    $quantities_enabled = wpinv_item_quantities_enabled();
    $use_taxes          = wpinv_use_taxes();
    if ( !$use_taxes && (float)$invoice->get_tax() > 0 ) {
        $use_taxes = true;
    }
    $zero_tax           = !(float)$invoice->get_tax() > 0 ? true : false;
    $tax_label           = $use_taxes && $invoice->has_vat() ? $wpinv_euvat->get_vat_name() : __( 'Tax', 'invoicing' );
    $tax_title          = !$zero_tax && $use_taxes ? ( wpinv_prices_include_tax() ? wp_sprintf( __( '(%s Incl.)', 'invoicing' ), $tax_label ) : wp_sprintf( __( '(%s Excl.)', 'invoicing' ), $tax_label ) ) : '';

    $cart_details       = $invoice->get_cart_details();
    $ajax_cart_details  = $cart_details;
    ob_start();
    ?>
    <table class="table table-sm table-bordered table-responsive">
        <thead>
            <tr>
                <th class="name"><strong><?php _e( "Item Name", "invoicing" );?></strong></th>
                <th class="rate"><strong><?php _e( "Price", "invoicing" );?></strong></th>
                <?php if ($quantities_enabled) { ?>
                    <th class="qty"><strong><?php _e( "Qty", "invoicing" );?></strong></th>
                <?php } ?>
                <?php if ($use_taxes && !$zero_tax) { ?>
                    <th class="tax"><strong><?php echo $tax_label . ' <span class="normal small">(%)</span>'; ?></strong></th>
                <?php } ?>
                <th class="total"><strong><?php echo __( "Item Total", "invoicing" ) . ' <span class="normal small">' . $tax_title . '<span>';?></strong></th>
            </tr>
        </thead>
        <tbody>
        <?php 
            if ( !empty( $cart_details ) ) {
                do_action( 'wpinv_display_line_items_start', $invoice );

                $count = 0;
                $cols  = 3;
                $extraPrice = 0;
                $custom_dp_descriptionArr = (get_post_meta($invoice->ID, 'dp_description')) ? get_post_meta($invoice->ID, 'dp_description')[0] : 0;
                $custom_priceArr = (get_post_meta($invoice->ID, 'dp_price')) ? get_post_meta($invoice->ID, 'dp_price')[0] : 0;

                if(!empty($custom_priceArr))
                {
                    foreach ($custom_priceArr as $key => $value) 
                    {
                        $extraPrice += $value;
                    }
                }
                $tot_price = 0;
                $tot_tax = $invoice->get_tax();
                $tot_discount = $invoice->get_discount();

                foreach ( $cart_details as $key => $cart_item ) {
                    $item_id    = !empty($cart_item['id']) ? absint( $cart_item['id'] ) : '';
                    $item_price = isset($cart_item["item_price"]) ? wpinv_round_amount( $cart_item["item_price"] ) : 0;
                    $quantity   = !empty($cart_item['quantity']) && (int)$cart_item['quantity'] > 0 ? absint( $cart_item['quantity'] ) : 1;
                    $item_price_f     = $cart_item['item_price'] * $quantity;

                    $custom_priceArr = (get_post_meta($invoice->ID, 'dp_price')) ? get_post_meta($invoice->ID, 'dp_price')[0] : 0;
                    if(isset($custom_priceArr[$item_id]) && $custom_priceArr[$item_id] != 0)
                    {
                        $item_price = wpinv_price( wpinv_format_amount($custom_priceArr[$item_id]));
                        $item_price_f = $custom_priceArr[$item_id] * $quantity;
                        $item_subtotal = wpinv_price( wpinv_format_amount($custom_priceArr[$item_id] * $cart_item['quantity']));
                    }

                    $line_total = isset($cart_item["subtotal"]) ? wpinv_round_amount( $cart_item["subtotal"] ) : 0;


                    $item       = $item_id ? new WPInv_Item( $item_id ) : NULL;
                    $summary    = '';
                    $item_name    = '';
                    $cols       = 3;
                    if ( !empty($item) ) {
                        $item_name  = $item->get_name();
                        $summary    = $item->get_summary();
                    }
                    $item_name  = !empty($cart_item['name']) ? $cart_item['name'] : $item_name;

                    $item_extra_description = ($custom_dp_descriptionArr[$item->ID]) ? $custom_dp_descriptionArr[$item->ID] : '';
                    $item_extra_price = ($custom_priceArr[$item->ID]) ? $custom_priceArr[$item->ID] : $item_price;

                    $tot_price += $quantity*$item_extra_price;

                    $summary = '<div>'.$item_extra_description.'</div>';
                    $summary = apply_filters( 'wpinv_print_invoice_line_item_summary', $summary, $cart_item, $item, $invoice );

                    $item_tax       = '';
                    $tax_rate       = '';
                    if ( $use_taxes && $item_price_f > 0 ) {
                        $item_tax = wpinv_price( wpinv_format_amount( $item_price_f * $cart_item['vat_rate'] / 100 ), $invoice->get_currency() );
                        $tax_rate = !empty( $cart_item['vat_rate'] ) ? $cart_item['vat_rate'] : ( $cart_item['vat_rate'] / $item_price_f ) * 100;
                        $tax_rate = $tax_rate > 0 ? (float)wpinv_round_amount( $tax_rate, 4 ) : '';
                        $tax_rate = $tax_rate != '' ? ' <small class="tax-rate">(' . $tax_rate . '%)</small>' : '';
                    }

                    $line_item_tax = $item_tax . $tax_rate;

                    if ( $line_item_tax === '' ) {
                        $line_item_tax = 0; // Zero tax
                    }

                    $action = apply_filters( 'wpinv_display_line_item_action', '', $cart_item, $invoice, $cols );

                    $line_item = '<tr class="row-' . ( ($count % 2 == 0) ? 'even' : 'odd' ) . ' wpinv-item">';
                        $line_item .= '<td class="name">' . $action. esc_html__( $item_name, 'invoicing' ) . wpinv_get_item_suffix( $item );
                        if ( $summary !== '' ) {
                            $line_item .= '<br/><small class="meta">' . wpautop( wp_kses_post( $summary ) ) . '</small>';
                        }
                        $line_item .= '</td>';

                        $line_item .= '<td class="rate">' . esc_html__( wpinv_price( wpinv_format_amount( $item_extra_price ), $invoice->get_currency() ) ) . '</td>';
                        if ($quantities_enabled) {
                            $cols++;
                            $line_item .= '<td class="qty">' . $quantity . '</td>';
                        }
                        if ($use_taxes && !$zero_tax) {
                            $cols++;
                            $line_item .= '<td class="tax">' . $line_item_tax . '</td>';
                        }
                        $line_item .= '<td class="total">' . esc_html__( wpinv_price( wpinv_format_amount( $quantity*$item_extra_price ), $invoice->get_currency() ) ) . '</td>';
                    $line_item .= '</tr>';

                    echo apply_filters( 'wpinv_display_line_item', $line_item, $cart_item, $invoice, $cols );

                    $count++;
                }

                do_action( 'wpinv_display_before_subtotal', $invoice, $cols );
                ?>
                <tr class="row-sub-total row_odd">
                    <td class="rate" colspan="<?php echo ( $cols - 1 ); ?>"><?php echo apply_filters( 'wpinv_print_cart_subtotal_label', '<strong>' . __( 'Sub Total', 'invoicing' ) . ':</strong>', $invoice ); ?></td>
                    <td class="total"><strong><?php _e( wpinv_price( wpinv_format_amount($tot_price ), $invoice->get_currency()) ) ?></strong></td>
                </tr>
                <?php
                do_action( 'wpinv_display_after_subtotal', $invoice, $cols );
                
                if ( wpinv_discount( $invoice_id, false ) > 0 ) {
                    do_action( 'wpinv_display_before_discount', $invoice, $cols );
                    ?>
                        <tr class="row-discount">
                            <td class="rate" colspan="<?php echo ( $cols  -1 ); ?>"><?php wpinv_get_discount_label( wpinv_discount_code( $invoice_id ) ); ?>:</td>
                            <td class="total"><?php echo wpinv_discount( $invoice_id, true, true ); ?></td>
                        </tr>
                    <?php
                    do_action( 'wpinv_display_after_discount', $invoice, $cols );
                }

                if ( $use_taxes ) {
                    do_action( 'wpinv_display_before_tax', $invoice, $cols );
                    ?>
                    <tr class="row-tax">
                        <td class="rate" colspan="<?php echo ( $cols - 1  ); ?>"><?php echo apply_filters( 'wpinv_print_cart_tax_label', '<strong>' . $tax_label . ':</strong>', $invoice ); ?></td>
                        <td class="total"><?php _e( wpinv_tax( $invoice_id, true ) ) ?></td>
                    </tr>
                    <?php
                    do_action( 'wpinv_display_after_tax', $invoice, $cols );
                }

                do_action( 'wpinv_display_before_total', $invoice, $cols );

                $tot_price = $tot_price + $tot_tax - $tot_discount;
                ?>
                <tr class="table-active row-total">
                    <td class="rate" colspan="<?php echo ( $cols - 1 ); ?>"><?php echo apply_filters( 'wpinv_print_cart_total_label', '<strong>' . __( 'Total', 'invoicing' ) . ':</strong>', $invoice ); ?></td>
                    <td class="total"><strong><?php _e( wpinv_price( wpinv_format_amount($tot_price ), $invoice->get_currency()) ) ?></strong></td>
                </tr>
                <?php
                do_action( 'wpinv_display_after_total', $invoice, $cols );

                do_action( 'wpinv_display_line_end', $invoice, $cols );
            }
        ?>
        </tbody>
    </table>
    <?php
    echo ob_get_clean();
}


function wpinv_dp_display_invoice_details( $invoice ) {
    global $wpinv_euvat;
    
    $invoice_id = $invoice->ID;
    $vat_name   = $wpinv_euvat->get_vat_name();
    $use_taxes  = wpinv_use_taxes();

    $cart_items = $invoice->get_cart_details();
    $extraPrice = 0;
    $extraPriceSubTotal = 0;
    $extraPriceTotal = 0;
    $custom_priceArr = (get_post_meta($invoice->ID, 'dp_price')) ? get_post_meta($invoice->ID, 'dp_price')[0] : 0;
    
    if(isset($custom_priceArr) && !empty($custom_priceArr))
    {
        foreach ( $cart_items as $key => $cart_item ) 
        {
            if(isset($custom_priceArr[$cart_item['id']]) && $custom_priceArr[$cart_item['id']] != '0' && $custom_priceArr[$cart_item['id']] != '')
            {
                $c_price = $custom_priceArr[$cart_item['id']];
            }
            else
            {
                $c_price = $cart_item['item_price'];
            }

            $extraPriceSubTotal += $c_price * $cart_item['quantity'];
            $extraPriceTotal += $c_price * $cart_item['quantity'];
        }
        $extraPriceTotal = $extraPriceTotal + $invoice->get_tax() - $invoice->get_discount();
    }
    else
    {
        $extraPriceSubTotal = $subtotal;
        $extraPriceTotal = $invoice->get_total();
    }
    
    $invoice_status = wpinv_get_invoice_status( $invoice_id );
    ?>
    <table class="table table-bordered table-sm">
        <?php if ( $invoice_number = wpinv_get_invoice_number( $invoice_id ) ) { ?>
            <tr class="wpi-row-number">
                <th><?php echo apply_filters( 'wpinv_invoice_number_label', __( 'Invoice Number', 'invoicing' ), $invoice ); ?></th>
                <td><?php echo esc_html( $invoice_number ); ?></td>
            </tr>
        <?php } ?>
        <tr class="wpi-row-status">
            <th><?php echo apply_filters( 'wpinv_invoice_status_label', __( 'Invoice Status', 'invoicing' ), $invoice ); ?></th>
            <td><?php echo wpinv_invoice_status_label( $invoice_status, wpinv_get_invoice_status( $invoice_id, true ) ); ?></td>
        </tr>
        <?php if ( $invoice->is_renewal() ) { ?>
        <tr class="wpi-row-parent">
            <th><?php echo apply_filters( 'wpinv_invoice_parent_invoice_label', __( 'Parent Invoice', 'invoicing' ), $invoice ); ?></th>
            <td><?php echo wpinv_invoice_link( $invoice->parent_invoice ); ?></td>
        </tr>
        <?php } ?>
        <?php if ( ( $gateway_name = wpinv_get_payment_gateway_name( $invoice_id ) ) && ( $invoice->is_paid() || $invoice->is_refunded() ) ) { ?>
            <tr class="wpi-row-gateway">
                <th><?php echo apply_filters( 'wpinv_invoice_payment_method_label', __( 'Payment Method', 'invoicing' ), $invoice ); ?></th>
                <td><?php echo $gateway_name; ?></td>
            </tr>
        <?php } ?>
        <?php if ( $invoice_date = wpinv_get_invoice_date( $invoice_id ) ) { ?>
            <tr class="wpi-row-date">
                <th><?php echo apply_filters( 'wpinv_invoice_date_label', __( 'Invoice Date', 'invoicing' ), $invoice ); ?></th>
                <td><?php echo $invoice_date; ?></td>
            </tr>
        <?php } ?>
        <?php if ( wpinv_get_option( 'overdue_active' ) && $invoice->needs_payment() && ( $due_date = $invoice->get_due_date( true ) ) ) { ?>
            <tr class="wpi-row-date">
                <th><?php echo apply_filters( 'wpinv_invoice_due_date_label', __( 'Due Date', 'invoicing' ), $invoice ); ?></th>
                <td><?php echo $due_date; ?></td>
            </tr>
        <?php } ?>
        <?php do_action( 'wpinv_display_details_after_due_date', $invoice_id ); ?>
        <?php if ( $owner_vat_number = $wpinv_euvat->get_vat_number() ) { ?>
            <tr class="wpi-row-ovatno">
                <th><?php echo apply_filters( 'wpinv_invoice_owner_vat_number_label', wp_sprintf( __( 'Owner %s Number', 'invoicing' ), $vat_name ), $invoice, $vat_name ); ?></th>
                <td><?php echo $owner_vat_number; ?></td>
            </tr>
        <?php } ?>
        <?php do_action( 'wpinv_display_details_after_due_date', $invoice_id ); ?>
        <?php if ( $use_taxes && ( $user_vat_number = wpinv_get_invoice_vat_number( $invoice_id ) ) ) { ?>
            <tr class="wpi-row-uvatno">
                <th><?php echo apply_filters( 'wpinv_invoice_user_vat_number_label', wp_sprintf( __( 'Invoice %s Number', 'invoicing' ), $vat_name ), $invoice, $vat_name ); ?></th>
                <td><?php echo $user_vat_number; ?></td>
            </tr>
        <?php } ?>
        <tr class="table-active tr-total wpi-row-total">
            <th><strong><?php _e( 'Total Amount', 'invoicing' ) ?></strong></th>
            <td><strong><?php echo wpinv_price( wpinv_format_amount($extraPriceTotal ), $invoice->get_currency()); ?></strong></td>
        </tr>
    </table>
<?php
}