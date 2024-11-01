<?php
/**
 * Plugin Name: TeYlos Pricing Table Woocommerce
 * Description: Build the woocommerce product like pricing table in frontend
 * Version: 1.1
 * Author: TeYlos Enterprise
 * Author URI: https://teylos.com
 * License: GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )  {

    define('TEYLOSPRICINGTABLE_PLUGIN_PATH', plugins_url('/', __FILE__));


    function teylos_ptw_color() {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('teylos_ptw_color_js',TEYLOSPRICINGTABLE_PLUGIN_PATH.'assets/js/color_picker.js' , array('wp-color-picker'), '', true);
    }


    add_action( 'plugins_loaded', 'teylos_pricing_table_woocommerce_init' );
    function teylos_pricing_table_woocommerce_init()
    {
        add_action('admin_enqueue_scripts', 'teylos_ptw_color');
        add_action('add_meta_boxes', 'teylos_ptw_add_meta_box');
        wp_enqueue_style('teylos-pricing-table-woocommerce-style', TEYLOSPRICINGTABLE_PLUGIN_PATH . 'assets/css/style.css', array(), 1.0);
        include_once('includes/teylos-pricing-table-woocommerce-frontend.php');
        add_shortcode( 'teylos_pricing_table_woocommerce_shortcode', 'teylos_pricing_table_woocommerce_shortcode_function' );

        wp_enqueue_script( 'teylos_pricing_table_ajax_form_data', TEYLOSPRICINGTABLE_PLUGIN_PATH.'assets/js/teylos_pricing_table_ajax_form_data.js', array( 'jquery' ) );
        wp_localize_script( 'teylos_pricing_table_ajax_form_data', 'TeYlosTableAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'teylosnextNonce' => wp_create_nonce( 'teylos-pricing-table-next-nonce' ) ) );


    }


    function teylos_ptw_update_array_to_show(){
        //update array_product
        $products_array=array();

        $teylos_ptw_query_args = array(
            'status' => array('private', 'publish'),
            'type' => array_merge(array_keys(wc_get_product_types())));
        foreach (wc_get_products($teylos_ptw_query_args) as $product) {
            $product = wc_get_product($product);
            if (get_post_meta($product->get_id(), 'teylos_ptwc_checkbox_metabox')) {
                foreach (get_post_meta($product->get_id(), 'teylos_ptwc_checkbox_metabox') as  $value) {
                    if ($value == 'true') {
                        array_push($products_array, $product);
                    }
                }
                }
        }
        return $products_array;
    }
    /**
     * Adds a box opction chechbox to product
     */
    function teylos_ptw_add_meta_box() {
        add_meta_box(
            'teylos_ptwc_checkbox_metabox',
            __( 'TeYlos Pricing Table Selector','teylos-pricing-table-woocommerce' ),
            'teylos_pricing_table_checkbox_into_product_function',
            'product',
            'normal'
        );
    }
    function teylos_pricing_table_checkbox_into_product_function($post){
        // Add a nonce field so we can check for it later.
        wp_nonce_field( 'teylos_ptw_save_meta_box_data', 'teylos_pricing_teylos_meta_box_nonce' );
        $teylos_ptwc = get_post_meta( $post->ID, 'teylos_ptwc_checkbox_metabox', true );
        ?>
        <ul class="show_meta_box_check_tptw">
            <li><input type="checkbox" name="teylos_ptwc_checkbox_metabox"  id="teylos_ptwc_checkbox_metabox" value="true" <?php if($teylos_ptwc=="true") {echo "checked"; } else { echo ""; } ?>></li>
            <li>Select for Table Pricing this Product</li>
        </ul>
        <?php

    }
    function teylos_ptw_save_meta_box_data( $post_id ) {
        /*
             * We need to verify this came from our screen and with proper authorization,
             * because the save_post action can be triggered at other times.
             */

        // Check if our nonce is set.
        if ( ! isset( $_POST['teylos_pricing_teylos_meta_box_nonce'] ) ) {
            return;
        }

        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( sanitize_key($_POST['teylos_pricing_teylos_meta_box_nonce']), 'teylos_ptw_save_meta_box_data' ) ) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }


        $teylos_ptwc_value = "false";


        if(isset($_POST["teylos_ptwc_checkbox_metabox"]))
        {
            $teylos_ptwc_value = sanitize_text_field( $_POST["teylos_ptwc_checkbox_metabox"] );
        }
        update_post_meta($post_id, "teylos_ptwc_checkbox_metabox", $teylos_ptwc_value);


    }
    add_action( 'save_post', 'teylos_ptw_save_meta_box_data' );

    add_action( 'admin_init', 'teylos_ptw_register_settings' );

    if (!function_exists("teylos_ptw_register_settings")) {
        function teylos_ptw_register_settings(){
            $products=teylos_ptw_update_array_to_show();
            foreach($products as $product ){
                $product=wc_get_product($product);
                    register_setting(
                        "teylos_ptw_group_admin",
                        'PricingTable#'.$product->get_id(),
                        "select"
                    );
                    register_setting(
                        "teylos_ptw_group_admin",
                        'escoger_ptw_color'.$product->get_id(),
                        "color"

                    );


                }
            }


        }


    if (!function_exists('admin_menu_teylos')) {
        add_action('admin_menu', 'admin_menu_teylos');//init de menu teylos
        function admin_menu_teylos()
        {
            //menu page parameters

            $page_title = 'TeYlos Plugins';
            $menu_title = 'TeYlos Plugins';
            $capability = 'manage_options';
            $menu_slug = 'teylos-plugins-menu';
            $function = 'teylos_plugins_menu';
            $icon_url = TEYLOSPRICINGTABLE_PLUGIN_PATH . 'images/teylos_solo.jpg';
            $position = 50;
            add_menu_page($page_title,
                $menu_title,
                $capability,
                $menu_slug,
                $function,
                $icon_url,
                $position);

        }

        if (!function_exists("teylos_plugins_menu")) {
            function teylos_plugins_menu()
            {
                ?>
                <h1>TeYlos Plugins Menu</h1>

            <?php

            }
        }
    }
    add_action('admin_menu', 'admin_menu_teylos_pricing_table_woocommerce');//add submenu to menu teylos plugins
    function admin_menu_teylos_pricing_table_woocommerce()
    {

        add_submenu_page('teylos-plugins-menu', 'TeYlos Pricing Table Woocommerce', 'TeYlos Pricing Table Woocommerce', 'manage_options', 'teylos-pricing-table-woocommerce', 'teylos_pricing_table_woocommerce_function');

    }

    if (!function_exists("teylos_pricing_table_woocommerce_function")) {
        function teylos_pricing_table_woocommerce_function()
        {
            ?>
            <h1>TeYlos Pricing Table Woocommerce</h1>

            <form method="post" action="options.php">
                <?php settings_fields("teylos_ptw_group_admin");
                do_settings_sections("teylos_ptw_group_admin");
                $contador=1;
                ?>
                <table>
                    <?php
                    foreach(teylos_ptw_update_array_to_show() as $producto){
                       $producto= wc_get_product($producto);
                        ?>
                        <tr>
                            <th>Pricing Table#<?php echo $contador; ?></th>
                            <td><select name="PricingTable#<?php echo $producto->get_id(); ?>">
                                    <?php
                                    $teylos_ptw_product_id_selected=$producto->get_id();
                                    foreach(teylos_ptw_update_array_to_show() as $product){
                                        echo '<option value="'. esc_attr( $product->get_id() ).'"';
                                        if(get_option('PricingTable#'. $producto->get_id())==$product->get_id()){
                                            echo 'selected="selected"';
                                            $teylos_ptw_product_id_selected=$product->get_id();
                                        }
                                        echo '>'.$product->get_name().'</option>';
                                    }
                                    ?>
                                </select>
                            </td>
                            <?php $color_picker=get_option('escoger_ptw_color'.$teylos_ptw_product_id_selected); ?>
                            <td>
                                <input type="text" class="teylos_picker_color" name="<?php echo 'escoger_ptw_color'. esc_attr( $teylos_ptw_product_id_selected); ?>" value="<?php if(!empty($color_picker)) { echo esc_attr( get_option('escoger_ptw_color'.$teylos_ptw_product_id_selected) ); } else { echo "#8553a0"; } ?>"/>
                            </td>
                        </tr>
                        <?php
                        $contador++;

                    }?>
                </table>
                <?php
                submit_button();
                ?>
            </form>

            <div class="teylos_pricing_table_shortcode">
                <h2>Shortcode </h2>
                <p>Use following shortcode to display the Pricing Table:</p>
                <textarea cols="25" rows="1" onclick="this.select();">[teylos_pricing_table_woocommerce_shortcode]</textarea> <br>

                <p>If you need to put the shortcode in code/theme file, use this:</p>
                <textarea cols="54" rows="1" onclick="this.select();">&lt;?php echo do_shortcode("[teylos_pricing_table_woocommerce_shortcode]"); ?&gt;</textarea> <p></p>
            </div>

        <?php
        }
    }



//settings link page
    function plugin_add_settings_link_teylos_pricing_table_woocommerce( $links ) {
        $settings_link = '<a href="admin.php?page=teylos-pricing-table-woocommerce">' . __( 'Settings' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }
    $plugin_pricing_table = plugin_basename( __FILE__ );
    add_filter( "plugin_action_links_$plugin_pricing_table", 'plugin_add_settings_link_teylos_pricing_table_woocommerce' );





}
else{
    if(is_admin())
    {
        add_action('admin_notices', 'print_teylos_notices');
    }
    function print_teylos_notices()
    {
        print_r('<div class="message"><p>' . __('TeYlos Pricing Table Woocommerce require the Woocommerce Plugins', 'teylos-pricing-table-woocommerce') . '</p></div>');
    }
}







?>
