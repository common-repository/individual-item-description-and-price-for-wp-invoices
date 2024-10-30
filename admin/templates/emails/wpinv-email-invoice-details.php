<?php
// don't load directly
if ( !defined('ABSPATH') )
    die('-1');

global $wpinv_euvat;

$sent_to_admin  = !empty( $sent_to_admin ) ? true : false;
$invoice_url = $invoice->get_view_url( true );
$use_taxes = wpinv_use_taxes();
$vat_name = $wpinv_euvat->get_vat_name();

do_action( 'wpinv_email_before_invoice_details', $invoice, $sent_to_admin ); ?>
<?php
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
?>
<div id="wpinv-email-details">
    <h3 class="wpinv-details-t"><?php echo apply_filters( 'wpinv_email_details_title', __( 'Invoice Details', 'invoicing' ), $invoice ); ?></h3>
    <table class="table table-bordered table-sm">
        <?php if ( $invoice_number = $invoice->get_number() ) { ?>
            <tr>
                <td><?php echo apply_filters( 'wpinv_invoice_number_label', __( 'Invoice Number', 'invoicing' ), $invoice ); ?></td>
                <td><a href="<?php echo esc_url( $invoice_url ) ;?>"><?php echo $invoice_number; ?></a></td>
            </tr>
        <?php } ?>
        <tr>
            <td><?php echo apply_filters( 'wpinv_invoice_status_label', __( 'Invoice Status', 'invoicing' ), $invoice ); ?></td>
            <td><?php echo $invoice->get_status( true ); ?></td>
        </tr>
        <?php if ( $invoice->is_renewal() ) { ?>
        <tr>
            <td><?php echo apply_filters( 'wpinv_invoice_parent_invoice_label', __( 'Parent Invoice', 'invoicing' ), $invoice ); ?></td>
            <td><?php echo wpinv_invoice_link( $invoice->parent_invoice ); ?></td>
        </tr>
        <?php } ?>
        <?php if ( ( $gateway_title = $invoice->get_gateway_title() ) && ( $invoice->is_paid() || $invoice->is_refunded() ) ) { ?>
            <td><?php echo apply_filters( 'wpinv_invoice_payment_method_label', __( 'Payment Method', 'invoicing' ), $invoice ); ?></td>
            <td><?php echo $gateway_title; ?></td>
        <?php } ?>
        <?php if ( $invoice_date = $invoice->get_invoice_date( false ) ) { ?>
            <tr>
                <td><?php echo apply_filters( 'wpinv_invoice_date_label', __( 'Invoice Date', 'invoicing' ), $invoice ); ?></td>
                <td><?php echo wp_sprintf( '<time datetime="%s">%s</time>', date_i18n( 'c', strtotime( $invoice_date ) ), $invoice->get_invoice_date() ); ?></td>
            </tr>
        <?php } ?>
        <?php if ( wpinv_get_option( 'overdue_active' ) && $invoice->needs_payment() && ( $due_date = $invoice->get_due_date() ) ) { ?>
            <tr>
                <td><?php echo apply_filters( 'wpinv_invoice_due_date_label', __( 'Due Date', 'invoicing' ), $invoice ); ?></td>
                <td><?php echo wp_sprintf( '<time datetime="%s">%s</time>', date_i18n( 'c', strtotime( $due_date ) ), $invoice->get_due_date( true ) ); ?></td>
            </tr>
        <?php } ?>
        <?php do_action( 'wpinv_email_invoice_details_after_due_date', $invoice->ID ); ?>
        <?php if ( empty( $sent_to_admin ) && ( $owner_vat_number = $wpinv_euvat->get_vat_number() ) ) { ?>
            <tr>
                <td><?php echo apply_filters( 'wpinv_invoice_owner_vat_number_label', wp_sprintf( __( 'Owner %s Number', 'invoicing' ), $vat_name ), $invoice, $vat_name ); ?></td>
                <td><?php echo $owner_vat_number; ?></td>
            </tr>
        <?php } ?>
        <?php if ( $use_taxes && ( $user_vat_number = $invoice->vat_number ) ) { ?>
            <tr>
                <td><?php echo apply_filters( 'wpinv_invoice_user_vat_number_label', wp_sprintf( __( 'Invoice %s Number', 'invoicing' ), $vat_name ), $invoice, $vat_name ); ?></td>
                <td><?php echo $user_vat_number; ?></td>
            </tr>
        <?php } ?>
        <tr class="table-active">
            <td><strong><?php _e( 'Total Amount', 'invoicing' ) ?></strong></td>
            <td><strong><?php echo wpinv_price(wpinv_format_amount($extraPriceTotal), $invoice->get_currency());?></strong></td>
        </tr>
    </table>
</div>
<?php do_action( 'wpinv_email_after_invoice_details', $invoice, $sent_to_admin ); ?>