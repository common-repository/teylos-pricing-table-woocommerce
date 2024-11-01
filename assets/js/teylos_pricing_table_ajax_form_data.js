function teylos_pricing_table_change_price(product_id, arr){


jQuery(document).ready(function($) {
    var data = {
        action: "teylos_pricing_table_backend_update_data",
        product_id: product_id,
        teylos_pt_nextNonce: TeYlosTableAjax.teylosnextNonce,
        arr:arr
    };
    jQuery.post(TeYlosTableAjax.ajaxurl, data, function(response) {
        $("#teylos-pricing-table-price-"+product_id).text(response);
    });

});
   }
function teylos_add_to_cart_pricing_table_woocommerce(add_to_cart_product_id, add_to_cart_arr){


    jQuery(document).ready(function($) {

        var datos= {
            action: "teylos_pricing_table_backend_add_to_cart",
            add_to_cart_product_id: add_to_cart_product_id,
            teylos_pt_nextNonce: TeYlosTableAjax.teylosnextNonce,
            add_to_cart_arr:add_to_cart_arr

        };
        jQuery.post(TeYlosTableAjax.ajaxurl, datos, function(response) {
            //alert(response);
            //$('#teylos-message').css('display','block');
            window.location.reload(true);

        });

    });

}


function teylos_add_to_cart_pricing_table_woocommerce_simple(add_to_cart_product_id){


    jQuery(document).ready(function($) {

        var datos= {
            action: "teylos_pt_add_to_cart_simple",
            add_to_cart_product_id: add_to_cart_product_id,
            teylos_pt_nextNonce: TeYlosTableAjax.teylosnextNonce

        };
        jQuery.post(TeYlosTableAjax.ajaxurl, datos, function(response) {
            window.location.reload(true);

        });

    });

}
