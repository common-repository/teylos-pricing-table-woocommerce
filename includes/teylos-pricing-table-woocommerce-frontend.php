<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action('init', 'teylos_ptw_load_ajax_functions');
function teylos_ptw_load_ajax_functions(){
    add_action('wp_ajax_teylos_pricing_table_backend_update_data', 'teylos_pricing_table_backend_update_data');
    add_action('wp_ajax_nopriv_teylos_pricing_table_backend_update_data', 'teylos_pricing_table_backend_update_data');

    add_action('wp_ajax_teylos_pricing_table_backend_add_to_cart', 'teylos_pricing_table_backend_add_to_cart');
    add_action('wp_ajax_nopriv_teylos_pricing_table_backend_add_to_cart', 'teylos_pricing_table_backend_add_to_cart');

    add_action('wp_ajax_teylos_pt_add_to_cart_simple', 'teylos_pt_add_to_cart_simple');
    add_action('wp_ajax_nopriv_teylos_pt_add_to_cart_simple', 'teylos_pt_add_to_cart_simple');

}



function teylos_pricing_table_woocommerce_shortcode_function()
{

    $productos=teylos_ptw_get_product_with_options();
    $count_columns = count($productos);
?>
    <div class="teylos-pricing-table-woocommerce-frontend">
        <div class="row">
            <?php foreach($productos as $producto){
                $producto=wc_get_product($producto);
           if($producto->is_purchasable()){?>
                <div class="col-md-<?php echo 12 / $count_columns ?>">
                <h2 style="background:<?php echo get_option('escoger_ptw_color' . $producto->get_id()) ?>"
                    class="teylos-pricing-table-title"><?php echo sanitize_text_field( $producto->get_name() );?></h2>

            <div style="background:<?php echo get_option('escoger_ptw_color' . $producto->get_id()) ?>" class="teylos-pricing-table-content"><?php echo get_woocommerce_currency_symbol();?><span id="teylos-pricing-table-price-<?php echo esc_attr( $producto->get_id() ); ?>" ><?php echo esc_html( $producto->get_price() ) ?></span></div>
            <div class="teylos-pricing-table-features">
           <?php
           $description=$producto->get_description();
           if (!empty($description))?>
            <div class="teylos-pricing-table-description"><?php echo $producto->get_description() ; ?></div>
          <?php if($producto->get_type()=='variable' || $producto->get_type()=='variable-subscription' ){ ?>
           <form method="post"  action="" id="pricing-table-form-<?php echo esc_attr( $producto->get_id() ); ?>" class="variations_form cart" enctype='multipart/form-data' data-product_id="<?php echo absint( $producto->get_id() ); ?>" >
               <div class="teylos-pricing-table-cart">
                   <ul style="list-style: none">
            <?php
            foreach($producto->get_variation_attributes() as $attribute_name => $options ){

                ?>
               <li>
               <select id="<?php echo esc_attr( $attribute_name );?>" >
                   <?php foreach ($options as $option) {?>
               <option value="<?php echo $option?>" ><?php echo strtoupper($option)." - ". wc_attribute_label( $attribute_name );?></option>
                <?php } ?>
               </select>

               </li>

               <?php } ?>
                   </ul>
                   <input type="button" style="background:<?php echo get_option('escoger_ptw_color' . $producto->get_id()) ?>" value="<?php echo esc_html( $producto->single_add_to_cart_text() ); ?>" id="teylos-pricing-table-cart-button-<?php echo $producto->get_id()?>"  class="single_add_to_cart_button button alt teylos-pricing-table-cart-button " />

               </div>

           </form>

              <script type="text/javascript">
                  jQuery(document).ready(function($){
                      var arr=[];
                      $('#pricing-table-form-<?php echo $producto->get_id(); ?> select').change(function(){
                          arr=[];
                          $('#pricing-table-form-<?php echo $producto->get_id(); ?> select').each(function() {
                              arr.push({
                                  attribute: $(this).attr("id"),
                                  valor: $(this).val()
                              });
                          });
                          teylos_pricing_table_change_price("<?php echo $producto->get_id(); ?>",arr);
                      });

                      $('#teylos-pricing-table-cart-button-<?php echo $producto->get_id(); ?>').click(function(){
                          arr=[];
                          $('#pricing-table-form-<?php echo $producto->get_id(); ?> select').each(function() {
                              arr.push({
                                  attribute: $(this).attr("id"),
                                  valor: $(this).val()
                              });
                          });
                          teylos_add_to_cart_pricing_table_woocommerce("<?php echo $producto->get_id(); ?>",arr);
                      });
                  });
              </script>
          <?php } else{?>
                   <?php if($producto->get_type()=='simple' ){ ?>
                       <form method="post"  action="" id="pricing-table-form-<?php echo esc_attr( $producto->get_id() ); ?>" class="variations_form cart" enctype='multipart/form-data' data-product_id="<?php echo absint( $producto->get_id() ); ?>" >
                           <div class="teylos-pricing-table-cart">
                               <input type="button" style="background:<?php echo get_option('escoger_ptw_color' . $producto->get_id()) ?>" value="<?php echo esc_html( $producto->single_add_to_cart_text() ); ?>" id="teylos-pricing-table-cart-button-<?php echo $producto->get_id()?>"  class="single_add_to_cart_button button alt teylos-pricing-table-cart-button " />

                           </div>

                       </form>
              <script type="text/javascript">
                  jQuery(document).ready(function($){
                      $('#teylos-pricing-table-cart-button-<?php echo $producto->get_id(); ?>').click(function(){
                          teylos_add_to_cart_pricing_table_woocommerce_simple("<?php echo $producto->get_id(); ?>");
                      });
                  });
              </script>
                <?php }?>
                <?php }?>
                    </div>

                    </div>


                    <?php
                    }}?>
                </div>
             </div>
<?php

}






function teylos_ptw_get_product_with_options(){
    $arreglo=array();
    $productos=teylos_ptw_update_array_to_show();
    foreach($productos as $producto){
        $producto=wc_get_product($producto);
        array_push($arreglo,teylos_ptw_get_product_by_id(get_option('PricingTable#'. $producto->get_id())));
    }
    return $arreglo;
}

function teylos_ptw_get_product_by_id($id){
    $productos=teylos_ptw_update_array_to_show();
    foreach($productos as $producto){
        $producto=wc_get_product($producto);
        if($producto->get_id()==$id)
            return $producto;
    }
}

function teylos_get_pricing_table_variation($product_id, $arr){
    foreach(wc_get_product(teylos_ptw_get_product_by_id($product_id))->get_children() as $variation){
        $variation=wc_get_product($variation);
        $positivo=0;
       foreach($arr as $key=>$arreglo) {
           if (strcasecmp($variation->get_attribute( sanitize_text_field( $arreglo[attribute])),sanitize_text_field( $arreglo[valor]))==0) {
               $positivo++;
           }
       }
        if($positivo==count(wc_get_product(teylos_ptw_get_product_by_id($product_id))->get_variation_attributes())){
            return $variation;
        }
    }

}

function teylos_ptw_add_to_cart_variation($product_id, $arr){
    global $woocommerce;
    foreach(wc_get_product(teylos_ptw_get_product_by_id($product_id))->get_children() as $variation){
        $variation=wc_get_product($variation);
        $positivo=0;
        foreach($arr as $key=>$arreglo) {
            if (strcasecmp($variation->get_attribute(sanitize_text_field( $arreglo[attribute]) ),sanitize_text_field( $arreglo[valor]) )==0) {
                $positivo++;
            }
        }
        if($positivo==count(wc_get_product(teylos_ptw_get_product_by_id($product_id))->get_variation_attributes())){
            $woocommerce->cart->add_to_cart( $product_id, 1, $variation->get_id(), $arr, null );

        }
    }

}

function teylos_pricing_table_backend_update_data(){
    if(!isset($_POST['teylos_pt_nextNonce']) || !isset($_POST['product_id']) || !isset($_POST['arr']) ){
        return;
    }

    // check nonce
    $nonce = sanitize_key($_POST['teylos_pt_nextNonce']);
    if ( ! wp_verify_nonce( $nonce, 'teylos-pricing-table-next-nonce' ) ) {
        die ( 'Busted!' );
    }
    $product_id= sanitize_text_field( $_POST['product_id'] ) ;
    $arr=$_POST['arr'];
    print_r( sanitize_text_field( teylos_get_pricing_table_variation($product_id, $arr)->get_price() ) );
    wp_die();
}

function teylos_pricing_table_backend_add_to_cart(){
    if(!isset($_POST['teylos_pt_nextNonce']) || !isset($_POST['add_to_cart_product_id']) || !isset($_POST['add_to_cart_arr']) ){
        return;
    }

    // check nonce
    $nonce = sanitize_key($_POST['teylos_pt_nextNonce']);
    if ( ! wp_verify_nonce( $nonce, 'teylos-pricing-table-next-nonce' ) ) {
        die ( 'Busted!' );
    }
    $product_id= sanitize_text_field( $_POST['add_to_cart_product_id'] );
    $arr=$_POST['add_to_cart_arr'];
    print_r(sanitize_text_field( teylos_ptw_add_to_cart_variation($product_id, $arr) ) );
    wp_die();
}

function teylos_pt_add_to_cart_simple(){
    if(!isset($_POST['teylos_pt_nextNonce']) || !isset($_POST['add_to_cart_product_id']) ){
        return;
    }

    // check nonce
    $nonce = sanitize_key($_POST['teylos_pt_nextNonce']);
    if ( ! wp_verify_nonce( $nonce, 'teylos-pricing-table-next-nonce' ) ) {
        die ( 'Busted!' );
    }
    $product_id= sanitize_text_field( $_POST['add_to_cart_product_id'] );
    global $woocommerce;
    $woocommerce->cart->add_to_cart( $product_id, 1, 0, null, null );
    wp_die();
}


