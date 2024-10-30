<?php
// don't load directly
if ( !defined('ABSPATH') )
    die('-1');

global $wpinv_euvat, $ajax_cart_details;
$ajax_cart_details = $invoice->get_cart_details();
$cart_items        = $ajax_cart_details;

$invoice_id         = $invoice->ID;
$quantities_enabled = wpinv_item_quantities_enabled();
$use_taxes          = wpinv_use_taxes();
$zero_tax           = !(float)$invoice->get_tax() > 0 ? true : false;
$tax_label          = $use_taxes && $invoice->has_vat() ? $wpinv_euvat->get_vat_name() : __( 'Tax', 'invoicing' );
$tax_title          = !$zero_tax && $use_taxes ? ( wpinv_prices_include_tax() ? wp_sprintf( __( '(%s Incl.)', 'invoicing' ), $tax_label ) : wp_sprintf( __( '(%s Excl.)', 'invoicing' ), $tax_label ) ) : '';

do_action( 'wpinv_before_email_items', $invoice ); ?>
<?php
$cart_items = $invoice->get_cart_details();
$extraPrice = 0;
$extraPriceSubTotal = 0;
$extraPriceTotal = 0;
$custom_dp_descriptionArr = (get_post_meta($invoice->ID, 'dp_description')) ? get_post_meta($invoice->ID, 'dp_description')[0] : 0;
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
?>
<div id="wpinv-email-items">
    <h3 class="wpinv-items-t"><?php echo apply_filters( 'wpinv_email_items_title', __( 'Items', 'invoicing' ) ); ?></h3>
    <table id="wpinv_checkout_cart" class="table table-bordered table-hover">
        <thead>
            <tr class="wpinv_cart_header_row">
                <?php do_action( 'wpinv_email_items_table_header_first' ); ?>
                <th class="wpinv_cart_item_name text-left"><?php _e( 'Item Name test', 'invoicing' ); ?></th>
                <th class="wpinv_cart_item_price text-right"><?php _e( 'Item Price', 'invoicing' ); ?></th>
                <?php if ( $quantities_enabled ) { ?>
                <th class="wpinv_cart_item_qty text-right"><?php _e( 'Qty', 'invoicing' ); ?></th>
                <?php } ?>
                <?php if ( !$zero_tax && $use_taxes ) { ?>
                <th class="wpinv_cart_item_tax text-right"><?php echo $tax_label . ' <span class="normal small">(%)</span>'; ?></th>
                <?php } ?>
                <th class="wpinv_cart_item_subtotal text-right"><?php echo __( 'Item Total', 'invoicing' ) . ' <span class="normal small">' . $tax_title . '<span>'; ?></th>
                <?php do_action( 'wpinv_email_items_table_header_last' ); ?>
            </tr>
        </thead>
        <tbody>
            <?php
                do_action( 'wpinv_email_items_before' );
                if ( $cart_items ) {
                    foreach ( $cart_items as $key => $item ) {
                        $wpi_item = $item['id'] ? new WPInv_Item( $item['id'] ) : NULL;

                        $item_extra_description = ($custom_dp_descriptionArr[$item['id']]) ? $custom_dp_descriptionArr[$item['id']] : '';
                        $item_extra_price = ($custom_priceArr[$item['id']]) ? $custom_priceArr[$item['id']] : $item['item_price'];

                        $item_extra_price_tot = $item_extra_price * wpinv_get_cart_item_quantity( $item );
                    ?>
                    <tr class="wpinv_cart_item" id="wpinv_cart_item_<?php echo esc_attr( $key ) . '_' . esc_attr( $item['id'] ); ?>" data-item-id="<?php echo esc_attr( $item['id'] ); ?>">
                        <?php do_action( 'wpinv_email_items_table_body_first', $item ); ?>
                        <td class="wpinv_cart_item_name text-left">
                            <?php
                                if ( current_theme_supports( 'post-thumbnails' ) && has_post_thumbnail( $item['id'] ) ) {
                                    echo '<div class="wpinv_cart_item_image">';
                                        echo get_the_post_thumbnail( $item['id'], apply_filters( 'wpinv_checkout_image_size', array( 25,25 ) ) );
                                    echo '</div>';
                                }
                                $item_title = esc_html( wpinv_get_cart_item_name( $item ) ) . wpinv_get_item_suffix( $wpi_item );
                                echo '<span class="wpinv_email_cart_item_title">' . $item_title . '</span>';
                                
                                $summary = '<div>'.$item_extra_description.'</div>';
                                $summary .= apply_filters( 'wpinv_email_invoice_line_item_summary', '', $item, $wpi_item, $invoice );



                                if ( !empty( $summary ) ) {
                                    echo '<p class="small">' . $summary . '</p>';
                                }
    
                                do_action( 'wpinv_email_cart_item_title_after', $item, $key );
                            ?>
                        </td>
                        <td class="wpinv_cart_item_price text-right">
                            <?php 
                            echo wpinv_price( wpinv_format_amount($item_extra_price));
                            do_action( 'wpinv_email_cart_item_price_after', $item, $key );
                            ?>
                        </td>
                        <?php if ( $quantities_enabled ) { ?>
                        <td class="wpinv_cart_item_qty text-right">
                            <?php
                            echo wpinv_get_cart_item_quantity( $item );
                            do_action( 'wpinv_email_item_quantitiy', $item, $key );
                            ?>
                        </td>
                        <?php } ?>
                        <?php if ( !$zero_tax && $use_taxes ) { ?>
                        <td class="wpinv_cart_item_tax text-right">
                            <?php
                            echo wpinv_price( wpinv_format_amount( $cart_item['vat_rate'] * $item_extra_price_tot / 100 ), $invoice->get_currency() ).' ('.$cart_item['vat_rate'].')';
                            do_action( 'wpinv_email_item_tax', $item, $key );
                            ?>
                        </td>
                        <?php } ?>

                        <td class="wpinv_cart_item_subtotal text-right">
                            <?php
                            $subtotal   = isset( $item['subtotal'] ) ? $item['subtotal'] : 0;
                            $subtotal   = wpinv_price( wpinv_format_amount( $item_extra_price_tot ) );
                            echo $subtotal;
                            do_action( 'wpinv_email_item_subtotal', $item, $key );
                            ?>
                        </td>
                        <?php do_action( 'wpinv_email_items_table_body_last', $item, $key ); ?>
                    </tr>
                <?php } ?>
            <?php } ?>
            <?php do_action( 'wpinv_email_items_middle' ); ?>
            <?php do_action( 'wpinv_email_items_after' ); ?>
        </tbody>
        <tfoot>
            <?php $cart_columns = wpinv_checkout_cart_columns(); if ( $zero_tax && $use_taxes ) { $cart_columns--; } ?>
            <?php if ( has_action( 'wpinv_email_footer_buttons' ) ) { ?>
                <tr class="wpinv_cart_footer_row">
                    <?php do_action( 'wpinv_email_items_table_buttons_first', $cart_items ); ?>
                    <td colspan="<?php echo ( $cart_columns ); ?>">
                        <?php do_action( 'wpinv_email_footer_buttons' ); ?>
                    </td>
                    <?php do_action( 'wpinv_email_items_table_buttons_first', $cart_items ); ?>
                </tr>
            <?php } ?>

            <?php if ( !$zero_tax && $use_taxes && !wpinv_prices_include_tax() && wpinv_is_cart_taxed() ) { ?>
                <tr class="wpinv_cart_footer_row wpinv_cart_subtotal_row">
                    <?php do_action( 'wpinv_email_items_table_subtotal_first', $cart_items ); ?>
                    <td colspan="<?php echo ( $cart_columns - 1 ); ?>" class="wpinv_cart_subtotal_label text-right">
                        <strong><?php _e( 'Sub-Total', 'invoicing' ); ?>:</strong>
                    </td>
                    <td class="wpinv_cart_subtotal text-right">
                        <span class="wpinv_cart_subtotal_amount bold"><?php echo wpinv_price( wpinv_format_amount( $extraPriceSubTotal ), $invoice->get_currency()); ?></span>
                    </td>
                    <?php do_action( 'wpinv_email_items_table_subtotal_last', $cart_items, $invoice ); ?>
                </tr>
            <?php } ?>
            
            <?php if ( wpinv_discount( $invoice_id, false ) > 0 ) { ?>
                <tr class="wpinv_cart_footer_row wpinv_cart_discount_row">
                    <?php do_action( 'wpinv_receipt_items_table_discount_first', $cart_items, $invoice ); ?>
                    <td colspan="<?php echo ( $cart_columns - 1 ); ?>" class="wpinv_cart_discount_label text-right">
                        <strong><?php wpinv_get_discount_label( wpinv_discount_code( $invoice_id ) ); ?>:</strong>
                    </td>
                    <td class="wpinv_cart_discount text-right">
                        <span class="wpinv_cart_discount_amount"><?php echo wpinv_discount( $invoice_id, true, true ); ?></span>
                    </td>
                    <?php do_action( 'wpinv_receipt_items_table_discount_last', $cart_items, $invoice ); ?>
                </tr>
            <?php } ?>

            <?php if ( !$zero_tax && $use_taxes && wpinv_is_cart_taxed() ) { ?>
                <tr class="wpinv_cart_footer_row wpinv_cart_tax_row">
                    <?php do_action( 'wpinv_email_items_table_tax_first', $cart_items, $invoice ); ?>
                    <td colspan="<?php echo ( $cart_columns - 1 ); ?>" class="wpinv_cart_tax_label text-right">
                        <strong><?php echo $tax_label; ?>:</strong>
                    </td>
                    <td class="wpinv_cart_tax text-right">
                        <span class="wpinv_cart_tax_amount"><?php echo $invoice->get_tax( true ); ?></span>
                    </td>
                    <?php do_action( 'wpinv_email_items_table_tax_last', $cart_items, $invoice ); ?>
                </tr>
            <?php } ?>

            <tr class="wpinv_cart_footer_row">
                <?php do_action( 'wpinv_email_items_table_footer_first', $cart_items, $invoice ); ?>
                <td colspan="<?php echo ( $cart_columns - 1 ); ?>" class="wpinv_cart_total_label text-right">
                    <?php echo apply_filters( 'wpinv_email_cart_total_label', '<strong>' . __( 'Total', 'invoicing' ) . ':</strong>', $invoice ); ?>
                </td>
                <td class="wpinv_cart_total text-right">
                    <span class="wpinv_cart_amount bold"><?php echo wpinv_price(wpinv_format_amount($extraPriceTotal), $invoice->get_currency()); ?></span>
                </td>
                <?php do_action( 'wpinv_email_items_table_footer_last', $cart_items, $invoice ); ?>
            </tr>
        </tfoot>
    </table>
</div>
<?php do_action( 'wpinv_after_email_items', $invoice ); ?>