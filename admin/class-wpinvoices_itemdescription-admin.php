<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.michely-web-engineering.de/
 * @since      1.0.0
 *
 * @package    Wpinvoices_itemdescription
 * @subpackage Wpinvoices_itemdescription/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wpinvoices_itemdescription
 * @subpackage Wpinvoices_itemdescription/admin
 * @author     Marco Michely <marco.michely@michely-web-engineering.de>
 */
class Wpinvoices_itemdescription_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpinvoices_itemdescription_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpinvoices_itemdescription_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wpinvoices_itemdescription-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpinvoices_itemdescription_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpinvoices_itemdescription_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wpinvoices_itemdescription-admin.js', array( 'jquery' ), $this->version, false );

	}

    public function dp_add_editor()
    {
        add_post_type_support('wpi_item', 'editor');
    }

	public function wpinv_dp_admin_invoice_line_item_summary($summary, $cart_item, $wpi_item, $invoice )
	{
		$htmls = '';
        $item_id = $wpi_item->ID;
		if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit' && $_REQUEST['post'] != '')
		{

            $custom_descrption = '';
            $custom_descrptionArr = get_post_meta($_REQUEST['post'], 'dp_description')[0];

            if(!empty($custom_descrptionArr))
            {
                foreach ($custom_descrptionArr as $key => $value) {
                    if($key == $wpi_item->ID)
                    {
                        $custom_descrption = $value;
                    }
                }
            }

            ob_start();
            echo '<div class="dp_description-wrap"><label>'.__('Description', 'invoicing').'</label>';
            wp_editor( $custom_descrption, 'dp_description_'.$wpi_item->ID, array( 'media_buttons' => false, 'quicktags' => false, 'editor_class' => "dp_description dp_description_".$wpi_item->ID."" ) );
            
            \_WP_Editors::enqueue_scripts();
            print_footer_scripts();
            \_WP_Editors::editor_js();
            echo '</div>';
            $temp = ob_get_clean();
            $htmls .= $temp; 
            

            $custom_price = '';
            $custom_priceArr = get_post_meta($_REQUEST['post'], 'dp_price')[0];
            if(!empty($custom_priceArr))
            {
                foreach ($custom_priceArr as $key => $value) {
                    if($key == $wpi_item->ID)
                    {
                        $custom_price = $value;
                    }
                }
            }

			$htmls .= '<div class="dp_price-wrap">
			<label>'.__('Price', 'invoicing').'</label>
			<input type="text" name="dp_price_'.$wpi_item->ID.'" class="dp_price dp_price_'.$wpi_item->ID.'" data-invoiceId="'.$wpi_item->ID.'" value="'.$custom_price.'" />
			</div>';
		}
		else
		{

            $default_price = 0;
            $extraPrice = sanitize_text_field($_POST['extraPrice'][$item_id]);
            if($extraPrice != '' && $default_price != $extraPrice)
            {
                $default_price = $extraPrice;
            }

			ob_start();
            echo '<div class="dp_description-wrap"><label>'.__('Description', 'invoicing').'</label>';
 

            $default_content = get_post_field('post_content', $wpi_item->ID);
            if(sanitize_text_field($_POST['customDescription'][$item_id]) != '' && $default_content != sanitize_text_field($_POST['customDescription'][$item_id]))
            {
                $default_content = sanitize_text_field($_POST['customDescription'][$item_id]);
            }
		    wp_editor( $default_content, 'dp_description_'.$wpi_item->ID, array( 'media_buttons' => false, 'quicktags' => false, 'editor_class' => "dp_description dp_description_".$wpi_item->ID."" ) );
		    
		    \_WP_Editors::enqueue_scripts();
		    print_footer_scripts();
		    \_WP_Editors::editor_js();
            echo '</div>';
			$temp = ob_get_clean();
            $htmls .= $temp; 

			$htmls .= '<div class="dp_price-wrap" '.sanitize_text_field($_POST['item_id']).'><label>'.__('Price', 'invoicing').'</label><input name="dp_price_'.$wpi_item->ID.'" class="dp_price dp_price_'.$wpi_item->ID.'" value="'.$default_price.'" data-invoiceId="'.$wpi_item->ID.'"></div>';
		}
        $htmls .= '<input type="hidden" name="dp_item[]" value="'.$wpi_item->ID.'" />';
		$htmls .= $summary;

		return $htmls;
	}
	public function wpinv_dp_ajax_nopriv_admin_recalculate_totals()
	{
		global $wpi_userID, $wpinv_ip_address_country;
        
        check_ajax_referer( 'wpinv-nonce', '_nonce' );
        if ( !current_user_can( 'manage_options' ) ) {
            die(-1);
        }
        
        $invoice_id = absint( $_POST['invoice_id'] );        
        $invoice    = wpinv_get_invoice( $invoice_id );
        if ( empty( $invoice ) ) {
            die();
        }
        
        $checkout_session = wpinv_get_checkout_session();
        
        $data                   = array();
        $data['invoice_id']     = $invoice_id;
        $data['cart_discounts'] = $invoice->get_discounts( true );
        
        wpinv_set_checkout_session( $data );
        
        if ( !empty( $_POST['user_id'] ) ) {
            $wpi_userID = absint( $_POST['user_id'] ); 
        }
        
        if ( empty( $_POST['country'] ) ) {
            $_POST['country'] = !empty($invoice->country) ? $invoice->country : wpinv_get_default_country();
        }
            
        $invoice->country = sanitize_text_field( $_POST['country'] );
        $invoice->set( 'country', sanitize_text_field( $_POST['country'] ) );
        if ( isset( $_POST['state'] ) ) {
            $invoice->state = sanitize_text_field( $_POST['state'] );
            $invoice->set( 'state', sanitize_text_field( $_POST['state'] ) );
        }
        
        $wpinv_ip_address_country = $invoice->country;
        


        $invoice = $invoice->recalculate_totals(true);

        $cart_items = $invoice->get_cart_details();
        $extraPrice = 0;
        if(isset($_POST['extraPrice']) && !empty($_POST['extraPrice']))
        {
            foreach ( $cart_items as $key => $cart_item ) 
            {
                if(isset($_POST['extraPrice'][$cart_item['id']]) && abs($_POST['extraPrice'][$cart_item['id']]) != '0' && $_POST['extraPrice'][$cart_item['id']] != '')
                {
                    $c_price = abs($_POST['extraPrice'][$cart_item['id']]);
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
            $extraPriceSubTotal = $invoice->get_subtotal();
            $extraPriceTotal = $invoice->get_total();
        }

        $response                       = array();
        $response['success']            = true;
        $response['data']['items']      = wpinv_dp_admin_get_line_items( $invoice );
        $response['data']['subtotal']   = $extraPriceSubTotal;
        $response['data']['subtotalf']  = wpinv_price(wpinv_format_amount($extraPriceSubTotal), $invoice->get_currency());
        $response['data']['tax']        = $invoice->get_tax();
        $response['data']['taxf']       = $invoice->get_tax(true);
        $response['data']['discount']   = $invoice->get_discount();
        $response['data']['extraPrice']   = wpinv_price(wpinv_format_amount($extraPriceTotal));
        $response['data']['discountf']  = $invoice->get_discount(true);
        $response['data']['total']      = $extraPriceTotal;
        $response['data']['totalf']     = wpinv_price(wpinv_format_amount($extraPriceTotal), $invoice->get_currency());
        
        wpinv_set_checkout_session($checkout_session);
        
        wp_send_json( $response );
	}
	
	public function wpinv_dp_insert_post_data($data, $postarr)
	{
		if ( current_user_can( 'manage_options' ) && !empty( $data['post_type'] ) && ( 'wpi_invoice' == $data['post_type'] || 'wpi_quote' == $data['post_type'] ) ) {
			if ( !empty($postarr['dp_item']) ) 
			{
                $dp_descriptionArr = array();
                $dp_priceArr = array();
                foreach ($postarr['dp_item'] as $key => $item_id ) 
                {
                    $dp_descriptionArr[$item_id] = $postarr['dp_description_'.$item_id];
                    $dp_priceArr[$item_id] = $postarr['dp_price_'.$item_id];
                }
                update_post_meta($postarr['ID'], 'dp_description', $dp_descriptionArr );
                update_post_meta($postarr['ID'], 'dp_price', $dp_priceArr);
			}
		}
		return $data;
	}

	public function wpinv_dp_add_meta_boxes_post()
	{
        remove_meta_box( 'wpinv-items', 'wpi_invoice', 'core' );
		remove_meta_box( 'wpinv-items', 'wpi_quote', 'normal' );
    	add_meta_box( 'wpinv-items', __( 'Invoice Items', 'invoicing' ), 'Wpinvoices_itemdescription_Admin::output', 'wpi_invoice', 'normal', 'high' );
        add_meta_box( 'wpinv-items', __( 'Quote Items', 'invoicing' ), 'Wpinvoices_itemdescription_Admin::output', 'wpi_quote', 'normal', 'high' );
	}
	public static function output()
	{
		global $wpinv_euvat, $ajax_cart_details, $post;

        $post_id            = !empty( $post->ID ) ? $post->ID : 0;
        $invoice            = new WPInv_Invoice( $post_id );
        $ajax_cart_details  = $invoice->get_cart_details();
        $subtotal           = $invoice->get_subtotal( true );
        $discount_raw       = $invoice->get_discount();
        $discount           = wpinv_price( $discount_raw, $invoice->get_currency() );
        $discounts          = $discount_raw > 0 ? $invoice->get_discounts() : '';
        $tax                = $invoice->get_tax( true );
        $total              = $invoice->get_total( true );
        $item_quantities    = wpinv_item_quantities_enabled();
        $use_taxes          = wpinv_use_taxes();
        if ( !$use_taxes && (float)$invoice->get_tax() > 0 ) {
            $use_taxes = true;
        }
        $item_types         = apply_filters( 'wpinv_item_types_for_quick_add_item', wpinv_get_item_types(), $post );
        $is_recurring       = $invoice->is_recurring();
        $post_type_object   = get_post_type_object($invoice->post_type);
        $type_title         = $post_type_object->labels->singular_name;

        $cols = 5;
        if ( $item_quantities ) {
            $cols++;
        }
        if ( $use_taxes ) {
            $cols++;
        }
        $class = '';
        if ( $invoice->is_paid() ) {
            $class .= ' wpinv-paid';
        }
        if ( $invoice->is_refunded() ) {
            $class .= ' wpinv-refunded';
        }
        if ( $is_recurring ) {
            $class .= ' wpi-recurring';
        }
        ?>
        <div class="wpinv-items-wrap<?php echo $class; ?>" id="wpinv_items_wrap" data-status="<?php echo $invoice->status; ?>">
            <table id="wpinv_items" class="wpinv-items" cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th class="id"><?php _e( 'ID', 'invoicing' );?></th>
                        <th class="title"><?php _e( 'Item', 'invoicing' );?></th>
                        <th class="price"><?php _e( 'Price', 'invoicing' );?></th>
                        <?php if ( $item_quantities ) { ?>
                        <th class="qty"><?php _e( 'Qty', 'invoicing' );?></th>
                        <?php } ?>
                        <th class="total"><?php _e( 'Total', 'invoicing' );?></th>
                        <?php if ( $use_taxes ) { ?>
                        <th class="tax"><?php _e( 'Tax (%)', 'invoicing' );?></th>
                        <?php } ?>
                        <th class="action"></th>
                    </tr>
                </thead>
                <tbody class="wpinv-line-items">
                    <?php echo wpinv_dp_admin_get_line_items( $invoice ); ?>
                </tbody>
                <tfoot class="wpinv-totals">
                    <tr>
                        <td colspan="<?php echo $cols; ?>" style="padding:0;border:0">
                            <div id="wpinv-quick-add">
                                <table cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td class="id">
                                        </td>
                                        <td class="title">
                                            <input type="text" class="regular-text" placeholder="<?php _e( 'Item Name', 'invoicing' ); ?>" value="" name="_wpinv_quick[name]">
                                            <?php if ( $wpinv_euvat->allow_vat_rules() ) { ?>
                                            <div class="wp-clearfix">
                                                <label class="wpi-vat-rule">
                                                    <span class="title"><?php _e( 'VAT rule type', 'invoicing' );?></span>
                                                    <span class="input-text-wrap">
                                                        <?php echo wpinv_html_select( array(
                                                            'options'          => $wpinv_euvat->get_rules(),
                                                            'name'             => '_wpinv_quick[vat_rule]',
                                                            'id'               => '_wpinv_quick_vat_rule',
                                                            'show_option_all'  => false,
                                                            'show_option_none' => false,
                                                            'class'            => 'gdmbx2-text-medium wpinv-quick-vat-rule wpi_select2',
                                                        ) ); ?>
                                                    </span>
                                                </label>
                                            </div>
                                            <?php } if ( $wpinv_euvat->allow_vat_classes() ) { ?>
                                            <div class="wp-clearfix">
                                                <label class="wpi-vat-class">
                                                    <span class="title"><?php _e( 'VAT class', 'invoicing' );?></span>
                                                    <span class="input-text-wrap">
                                                        <?php echo wpinv_html_select( array(
                                                            'options'          => $wpinv_euvat->get_all_classes(),
                                                            'name'             => '_wpinv_quick[vat_class]',
                                                            'id'               => '_wpinv_quick_vat_class',
                                                            'show_option_all'  => false,
                                                            'show_option_none' => false,
                                                            'class'            => 'gdmbx2-text-medium wpinv-quick-vat-class wpi_select2',
                                                        ) ); ?>
                                                    </span>
                                                </label>
                                            </div>
                                            <?php } ?>
                                            <div class="wp-clearfix">
                                                <label class="wpi-item-type">
                                                    <span class="title"><?php _e( 'Item type', 'invoicing' );?></span>
                                                    <span class="input-text-wrap">
                                                        <?php echo wpinv_html_select( array(
                                                            'options'          => $item_types,
                                                            'name'             => '_wpinv_quick[type]',
                                                            'id'               => '_wpinv_quick_type',
                                                            'selected'         => 'custom',
                                                            'show_option_all'  => false,
                                                            'show_option_none' => false,
                                                            'class'            => 'gdmbx2-text-medium wpinv-quick-type wpi_select2',
                                                        ) ); ?>
                                                    </span>
                                                </label>
                                            </div>
                                            <div class="wp-clearfix">
                                                <label class="wpi-item-actions">
                                                    <span class="input-text-wrap">
                                                        <input type="button" value="Save" class="button button-primary" id="wpinv-save-item"><input type="button" value="Cancel" class="button button-secondary" id="wpinv-cancel-item">
                                                    </span>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="price"><input type="text" placeholder="0.00" class="wpi-field-price wpi-price" name="_wpinv_quick[price]" /></td>
                                        <?php if ( $item_quantities ) { ?>
                                        <td class="qty"><input type="number" class="small-text" step="1" min="1" value="1" name="_wpinv_quick[qty]" /></td>
                                        <?php } ?>
                                        <td class="total"></td>
                                        <?php if ( $use_taxes ) { ?>
                                        <td class="tax"></td>
                                        <?php } ?>
                                        <td class="action"></td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr class="clear">
                        <td colspan="<?php echo $cols; ?>"></td>
                    </tr>
                    <tr class="totals">
                        <td colspan="<?php echo ( $cols - 4 ); ?>"></td>
                        <td colspan="4">
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
                            <table cellspacing="0" cellpadding="0">
                                <tr class="subtotal">
                                    <td class="name"><?php _e( 'Sub Total:', 'invoicing' );?></td>
                                    <td class="total"><?php echo wpinv_price(wpinv_format_amount($extraPriceSubTotal), $invoice->get_currency());?></td>
                                    <td class="action"></td>
                                </tr>
                                <tr class="discount">
                                    <td class="name"><?php wpinv_get_discount_label( wpinv_discount_code( $invoice->ID ) ); ?>:</td>
                                    <td class="total"><?php echo wpinv_discount( $invoice->ID, true, true ); ?></td>
                                    <td class="action"></td>
                                </tr>
                                <?php if ( $use_taxes ) { ?>
                                <tr class="tax">
                                    <td class="name"><?php _e( 'Tax:', 'invoicing' );?></td>
                                    <td class="total"><?php echo $tax;?></td>
                                    <td class="action"></td>
                                </tr>
                                <?php } ?>
                                <tr class="total">
                                    <td class="name"><?php echo apply_filters( 'wpinv_invoice_items_total_label', __( 'Invoice Total:', 'invoicing' ), $invoice );?></td>
                                    <td class="total"><?php echo wpinv_price(wpinv_format_amount($extraPriceTotal), $invoice->get_currency());?></td>
                                    <td class="action"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tfoot>
            </table>
            <div class="wpinv-actions">
                <?php ob_start(); ?>
                <?php
                    if ( !$invoice->is_paid() && !$invoice->is_refunded() ) {
                        if ( !$invoice->is_recurring() ) {
                            echo wpinv_item_dropdown( array(
                                'name'             => 'wpinv_invoice_item',
                                'id'               => 'wpinv_invoice_item',
                                'show_recurring'   => true,
                                'class'            => 'wpi_select2',
                            ) );
                    ?>
                <input type="button" value="<?php echo sprintf(esc_attr__( 'Add item to %s', 'invoicing'), $type_title); ?>" class="button button-primary" id="wpinv-add-item"><input type="button" value="<?php esc_attr_e( 'Create new item', 'invoicing' );?>" class="button button-primary" id="wpinv-new-item"><?php } ?><input type="button" value="<?php esc_attr_e( 'Recalculate Totals', 'invoicing' );?>" class="button button-primary wpinv-flr" id="wpinv-recalc-totals">
                    <?php } ?>
                <?php do_action( 'wpinv_invoice_items_actions', $invoice ); ?>
                <?php $item_actions = ob_get_clean(); echo apply_filters( 'wpinv_invoice_items_actions_content', $item_actions, $invoice, $post ); ?>
            </div>
        </div>
        <?php
	}

    public static function wpinv_dp_add_invoice_item() {
        global $wpi_userID, $wpinv_ip_address_country;
        check_ajax_referer( 'invoice-item', '_nonce' );
        if ( !current_user_can( 'manage_options' ) ) {
            die(-1);
        }
        
        $item_id    = sanitize_text_field( $_POST['item_id'] );
        $invoice_id = absint( $_POST['invoice_id'] );
        
        if ( !is_numeric( $invoice_id ) || !is_numeric( $item_id ) ) {
            die();
        }
        
        $invoice    = wpinv_get_invoice( $invoice_id );
        if ( empty( $invoice ) ) {
            die();
        }
        
        if ( $invoice->is_paid() || $invoice->is_refunded() ) {
            die(); // Don't allow modify items for paid invoice.
        }
        
        if ( !empty( $_POST['user_id'] ) ) {
            $wpi_userID = absint( $_POST['user_id'] ); 
        }

        $item = new WPInv_Item( $item_id );
        if ( !( !empty( $item ) && $item->post_type == 'wpi_item' ) ) {
            die();
        }
        
        // Validate item before adding to invoice because recurring item must be paid individually.
        if ( !empty( $invoice->cart_details ) ) {
            $valid = true;
            
            if ( $recurring_item = $invoice->get_recurring() ) {
                if ( $recurring_item != $item_id ) {
                    $valid = false;
                }
            } else if ( wpinv_is_recurring_item( $item_id ) ) {
                $valid = false;
            }
            
            if ( !$valid ) {
                $response               = array();
                $response['success']    = false;
                $response['msg']        = __( 'You can not add item because recurring item must be paid individually!', 'invoicing' );
                wp_send_json( $response );
            }
        }
        
        $checkout_session = wpinv_get_checkout_session();
        
        $data                   = array();
        $data['invoice_id']     = $invoice_id;
        $data['cart_discounts'] = $invoice->get_discounts( true );
        
        wpinv_set_checkout_session( $data );
        
        $quantity = wpinv_item_quantities_enabled() && !empty($_POST['qty']) && (int)$_POST['qty'] > 0 ? (int)$_POST['qty'] : 1;

        $args = array(
            'id'            => $item_id,
            'quantity'      => $quantity,
            'item_price'    => $item->get_price(),
            'custom_price'  => '',
            'tax'           => 0.00,
            'discount'      => 0,
            'meta'          => array(),
            'fees'          => array()
        );

        $invoice->add_item( $item_id, $args );
        $invoice->save();
        
        if ( empty( $_POST['country'] ) ) {
            $_POST['country'] = !empty($invoice->country) ? $invoice->country : wpinv_get_default_country();
        }
        if ( empty( $_POST['state'] ) ) {
            $_POST['state'] = $invoice->state;
        }
         
        $invoice->country   = sanitize_text_field( $_POST['country'] );
        $invoice->state     = sanitize_text_field( $_POST['state'] );
        
        $invoice->set( 'country', sanitize_text_field( $_POST['country'] ) );
        $invoice->set( 'state', sanitize_text_field( $_POST['state'] ) );
        
        $wpinv_ip_address_country = $invoice->country;

        $invoice->recalculate_totals(true);
        
        $cart_items = $invoice->get_cart_details();
        $extraPrice = 0;
        if(isset($_POST['extraPrice']) && !empty($_POST['extraPrice']))
        {
            foreach ( $cart_items as $key => $cart_item ) 
            {
                if(isset($_POST['extraPrice'][$cart_item['id']]) && abs($_POST['extraPrice'][$cart_item['id']]) != '0' && abs($_POST['extraPrice'][$cart_item['id']]) != '')
                {
                    $c_price = $_POST['extraPrice'][$cart_item['id']];
                }
                else
                {
                    $c_price = $cart_item['item_price'];
                }
                $extraPriceSubTotal += $c_price * $cart_item['quantity'];
                $extraPriceTotal += $c_price * $cart_item['quantity'];
            }
            $extraPriceTotal = $extraPriceTotal - $invoice->get_discount() + $invoice->get_tax();
        }
        else
        {
            $extraPriceSubTotal = $invoice->get_subtotal();
            $extraPriceTotal = $invoice->get_total();
        }
        
        $response                       = array();
        $response['success']            = true;
        $response['data']['items']      = wpinv_dp_admin_get_line_items( $invoice );
        $response['data']['subtotal']   = $extraPriceSubTotal;
        $response['data']['subtotalf']  = wpinv_price(wpinv_format_amount($extraPriceSubTotal), $invoice->get_currency());
        $response['data']['tax']        = $invoice->get_tax();
        $response['data']['taxf']       = $invoice->get_tax(true);
        $response['data']['discount']   = $invoice->get_discount();
        $response['data']['discountf']  = $invoice->get_discount(true);
        $response['data']['total']      = $extraPriceTotal;
        $response['data']['extraPrice'] = wpinv_price(wpinv_format_amount($extraPriceTotal), $invoice->get_currency());
        $response['data']['totalf']     = wpinv_price(wpinv_format_amount($extraPriceTotal), $invoice->get_currency());
        
        wpinv_set_checkout_session($checkout_session);
        wp_send_json( $response );
    }

    
    public static function wpinv_dp_remove_invoice_item() {
        global $wpi_userID, $wpinv_ip_address_country;
        
        check_ajax_referer( 'invoice-item', '_nonce' );
        if ( !current_user_can( 'manage_options' ) ) {
            die(-1);
        }
        
        $item_id    = sanitize_text_field( $_POST['item_id'] );
        $invoice_id = absint( $_POST['invoice_id'] );
        $cart_index = isset( $_POST['index'] ) && $_POST['index'] >= 0 ? $_POST['index'] : false;
        
        if ( !is_numeric( $invoice_id ) || !is_numeric( $item_id ) ) {
            die();
        }

        $invoice    = wpinv_get_invoice( $invoice_id );
        if ( empty( $invoice ) ) {
            die();
        }
        
        if ( $invoice->is_paid() || $invoice->is_refunded() ) {
            die(); // Don't allow modify items for paid invoice.
        }
        
        if ( !empty( $_POST['user_id'] ) ) {
            $wpi_userID = absint( $_POST['user_id'] ); 
        }

        $item       = new WPInv_Item( $item_id );
        if ( !( !empty( $item ) && $item->post_type == 'wpi_item' ) ) {
            die();
        }
        
        $checkout_session = wpinv_get_checkout_session();
        
        $data                   = array();
        $data['invoice_id']     = $invoice_id;
        $data['cart_discounts'] = $invoice->get_discounts( true );
        
        wpinv_set_checkout_session( $data );

        $args = array(
            'id'         => $item_id,
            'quantity'   => 1,
            'cart_index' => $cart_index
        );

        $invoice->remove_item( $item_id, $args );
        $invoice->save();
        
        if ( empty( $_POST['country'] ) ) {
            $_POST['country'] = !empty($invoice->country) ? $invoice->country : wpinv_get_default_country();
        }
        if ( empty( $_POST['state'] ) ) {
            $_POST['state'] = $invoice->state;
        }
         
        $invoice->country   = sanitize_text_field( $_POST['country'] );
        $invoice->state     = sanitize_text_field( $_POST['state'] );
        
        $invoice->set( 'country', sanitize_text_field( $_POST['country'] ) );
        $invoice->set( 'state', sanitize_text_field( $_POST['state'] ) );
        
        $wpinv_ip_address_country = $invoice->country;
        
        $invoice->recalculate_totals(true);
        $cart_items = $invoice->get_cart_details();
        $extraPrice = 0;
        if(isset($_POST['extraPrice']) && !empty($_POST['extraPrice']))
        {
            foreach ( $cart_items as $key => $cart_item ) 
            {
                if(isset($_POST['extraPrice'][$cart_item['id']]) && abs($_POST['extraPrice'][$cart_item['id']]) != '0' && abs($_POST['extraPrice'][$cart_item['id']]) != '')
                {
                    $c_price = $_POST['extraPrice'][$cart_item['id']];
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
            $extraPriceSubTotal = $invoice->get_subtotal();
            $extraPriceTotal = $invoice->get_total();
        }
        
        $response                       = array();
        $response['success']            = true;
        $response['data']['items']      = wpinv_dp_admin_get_line_items( $invoice );
        $response['data']['subtotal']   = $extraPriceSubTotal;
        $response['data']['subtotalf']  = wpinv_price(wpinv_format_amount($extraPriceSubTotal), $invoice->get_currency());
        $response['data']['tax']        = $invoice->get_tax();
        $response['data']['taxf']       = $invoice->get_tax(true);
        $response['data']['discount']   = $invoice->get_discount();
        $response['data']['discountf']  = $invoice->get_discount(true);
         $response['data']['total']      = $extraPriceTotal;
        $response['data']['extraPrice'] = wpinv_price(wpinv_format_amount($extraPriceTotal), $invoice->get_currency());
        $response['data']['totalf']     = wpinv_price(wpinv_format_amount($extraPriceTotal), $invoice->get_currency());
        
        wpinv_set_checkout_session($checkout_session);
        wp_send_json( $response );
    }

    public function wpinv_dp_email_invoice_items( $invoice, $email_type = '', $sent_to_admin = false)
    {
         wpinv_get_template( 'emails/wpinv-email-invoice-items.php', array( 'invoice' => $invoice, 'email_type' => $email_type, 'sent_to_admin' => $sent_to_admin ), plugin_dir_path(__FILE__).'templates/', plugin_dir_path(__FILE__).'templates/' );
    }
    public function wpinv_dp_email_invoice_details( $invoice, $email_type = '', $sent_to_admin = false ) {
        wpinv_get_template( 'emails/wpinv-email-invoice-details.php', array( 'invoice' => $invoice, 'email_type' => $email_type, 'sent_to_admin' => $sent_to_admin ), plugin_dir_path(__FILE__).'templates/', plugin_dir_path(__FILE__).'templates/' );
    }


    public function wpinv_dp_template( $template ) 
    {
        global $post, $wp_query;
        
        if ( ( is_single() || is_404() ) && !empty( $post->ID ) && (get_post_type( $post->ID ) == 'wpi_invoice' or get_post_type( $post->ID ) == 'wpi_quote')) {
            if ( wpinv_user_can_view_invoice( $post->ID ) ) {
                $template = dirname(__FILE__).'/templates/wpinv-invoice-print.php';
            } else {
                $template = wpinv_get_template_part( 'wpinv-invalid-access', false, false );
            }
        }

        return $template;
    }
    public function wpinv_dp_get_invoice_tax($tax, $ID, $invoice, $currency)
    {
        $cart_items = $invoice->get_cart_details();
        $extraTax = 0;
        $extraPriceSubTotal = 0;
        $extraPriceTotal = 0;
        $custom_priceArr = (get_post_meta($invoice->ID, 'dp_price')) ? get_post_meta($invoice->ID, 'dp_price')[0] : 0;
        
        foreach ( $cart_items as $key => $cart_item ) 
        {
            if(isset($custom_priceArr) && !empty($custom_priceArr))
            {
            
                if(isset($_POST['extraPrice'][$cart_item['id']]) && abs($_POST['extraPrice'][$cart_item['id']]) != '0' && abs($_POST['extraPrice'][$cart_item['id']]) != '')
                {
                    $c_price = abs($_POST['extraPrice'][$cart_item['id']]);
                }
                else if(isset($custom_priceArr[$cart_item['id']]) && $custom_priceArr[$cart_item['id']] != '0' && $custom_priceArr[$cart_item['id']] != '')
                {
                    $c_price = $custom_priceArr[$cart_item['id']];
                }
                else
                {
                    $c_price = $cart_item['item_price'];
                }

                $extraPriceSubTotal = $c_price * $cart_item['quantity'];
                $extraTax += $extraPriceSubTotal * $cart_item['vat_rate'] / 100;
            }
            else
            {
                $c_price = $cart_item['item_price'];
                if(isset($_POST['extraPrice'][$cart_item['id']]) && abs($_POST['extraPrice'][$cart_item['id']]) != '0' && abs($_POST['extraPrice'][$cart_item['id']]) != '')
                {
                    $c_price = abs($_POST['extraPrice'][$cart_item['id']]);
                }
                $extraPriceSubTotal = $c_price * $cart_item['quantity'];
                $extraTax += $extraPriceSubTotal * $cart_item['vat_rate'] / 100;
            }
        }
        if($currency)
        {
            return wpinv_price( wpinv_format_amount($extraTax), $invoice->get_currency() );
        }
        else
        {
            return $extraTax;
        }

    }
    public static function wpinv_dp_create_invoice_item() 
    {
        check_ajax_referer( 'invoice-item', '_nonce' );
        if ( !current_user_can( 'manage_options' ) ) {
            die(-1);
        }
        
        $invoice_id = absint( $_POST['invoice_id'] );

        // Find the item
        if ( !is_numeric( $invoice_id ) ) {
            die();
        }        
        
        $invoice     = wpinv_get_invoice( $invoice_id );
        if ( empty( $invoice ) ) {
            die();
        }
        
        // Validate item before adding to invoice because recurring item must be paid individually.
        if ( !empty( $invoice->cart_details ) && $invoice->get_recurring() ) {
            $response               = array();
            $response['success']    = false;
            $response['msg']        = __( 'You can not add item because recurring item must be paid individually!', 'invoicing' );
            wp_send_json( $response );
        }        
        
        $save_item = $_POST['_wpinv_quick'];
        
        $meta               = array();
        $meta['type']       = !empty($save_item['type']) ? sanitize_text_field($save_item['type']) : 'custom';
        $meta['price']      = !empty($save_item['price']) ? wpinv_sanitize_amount( $save_item['price'] ) : 0;
        $meta['vat_rule']   = !empty($save_item['vat_rule']) ? sanitize_text_field($save_item['vat_rule']) : 'digital';
        $meta['vat_class']  = !empty($save_item['vat_class']) ? sanitize_text_field($save_item['vat_class']) : '_standard';
        
        $data                   = array();
        $data['post_title']     = sanitize_text_field($save_item['name']);
        $data['post_status']    = 'publish';
        $data['meta']           = $meta;
        
        $item = new WPInv_Item();
        $item->create( $data );
        
        if ( !empty( $item ) ) {
            $_POST['item_id']   = $item->ID;
            $_POST['qty']       = !empty($save_item['qty']) && $save_item['qty'] > 0 ? (int)$save_item['qty'] : 1;
            
            self::add_invoice_item();
        }
        die();
    }

    public static function add_invoice_item() {
        global $wpi_userID, $wpinv_ip_address_country;
        check_ajax_referer( 'invoice-item', '_nonce' );
        if ( !current_user_can( 'manage_options' ) ) {
            die(-1);
        }
        
        $item_id    = sanitize_text_field( $_POST['item_id'] );
        $invoice_id = absint( $_POST['invoice_id'] );
        
        if ( !is_numeric( $invoice_id ) || !is_numeric( $item_id ) ) {
            die();
        }
        
        $invoice    = wpinv_get_invoice( $invoice_id );
        if ( empty( $invoice ) ) {
            die();
        }
        
        if ( $invoice->is_paid() || $invoice->is_refunded() ) {
            die(); // Don't allow modify items for paid invoice.
        }
        
        if ( !empty( $_POST['user_id'] ) ) {
            $wpi_userID = absint( $_POST['user_id'] ); 
        }

        $item = new WPInv_Item( $item_id );
        if ( !( !empty( $item ) && $item->post_type == 'wpi_item' ) ) {
            die();
        }
        
        // Validate item before adding to invoice because recurring item must be paid individually.
        if ( !empty( $invoice->cart_details ) ) {
            $valid = true;
            
            if ( $recurring_item = $invoice->get_recurring() ) {
                if ( $recurring_item != $item_id ) {
                    $valid = false;
                }
            } else if ( wpinv_is_recurring_item( $item_id ) ) {
                $valid = false;
            }
            
            if ( !$valid ) {
                $response               = array();
                $response['success']    = false;
                $response['msg']        = __( 'You can not add item because recurring item must be paid individually!', 'invoicing' );
                wp_send_json( $response );
            }
        }
        
        $checkout_session = wpinv_get_checkout_session();
        
        $data                   = array();
        $data['invoice_id']     = $invoice_id;
        $data['cart_discounts'] = $invoice->get_discounts( true );
        
        wpinv_set_checkout_session( $data );
        
        $quantity = wpinv_item_quantities_enabled() && !empty($_POST['qty']) && (int)$_POST['qty'] > 0 ? (int)$_POST['qty'] : 1;

        $args = array(
            'id'            => $item_id,
            'quantity'      => $quantity,
            'item_price'    => $item->get_price(),
            'custom_price'  => '',
            'tax'           => 0.00,
            'discount'      => 0,
            'meta'          => array(),
            'fees'          => array()
        );

        $invoice->add_item( $item_id, $args );
        $invoice->save();
        
        if ( empty( $_POST['country'] ) ) {
            $_POST['country'] = !empty($invoice->country) ? $invoice->country : wpinv_get_default_country();
        }
        if ( empty( $_POST['state'] ) ) {
            $_POST['state'] = $invoice->state;
        }
         
        $invoice->country   = sanitize_text_field( $_POST['country'] );
        $invoice->state     = sanitize_text_field( $_POST['state'] );
        
        $invoice->set( 'country', sanitize_text_field( $_POST['country'] ) );
        $invoice->set( 'state', sanitize_text_field( $_POST['state'] ) );
        
        $wpinv_ip_address_country = $invoice->country;

        $invoice->recalculate_totals(true);
        
        $response                       = array();
        $response['success']            = true;
        $response['data']['items']      = wpinv_dp_admin_get_line_items( $invoice );
        $response['data']['subtotal']   = $invoice->get_subtotal();
        $response['data']['subtotalf']  = $invoice->get_subtotal(true);
        $response['data']['tax']        = $invoice->get_tax();
        $response['data']['taxf']       = $invoice->get_tax(true);
        $response['data']['discount']   = $invoice->get_discount();
        $response['data']['discountf']  = $invoice->get_discount(true);
        $response['data']['total']      = $invoice->get_total();
        $response['data']['totalf']     = $invoice->get_total(true);
        
        wpinv_set_checkout_session($checkout_session);
        wp_send_json( $response );
    }
}