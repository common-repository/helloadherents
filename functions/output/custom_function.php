<?php 

function helladh_test_custom_function() {
    echo "Pour tester les fonctionnalités de votre fonction personnalisée, veuillez sauvegarder les paramètres puis appuyer sur le bouton suivant : ";
    echo '<button class="button_test_custom_function" type="button" role="button">Test fonction personnalisée</button>';

    echo '<script>
        jQuery(".button_test_custom_function").click(function(){
            jQuery(".result_area_custom_function").css({"background-color": "black", "height": "15em", "resize": "vertical"});
            jQuery.get(ajaxurl,{"action": "ajax_helladh_test_custom_function"},
            function (msg) { jQuery(".result_area_custom_function").html(msg);});
        });
        </script>';
    echo '<pre class="result_area result_area_custom_function">Chargement en cours...</pre>';
}

function helladh_test_custom_function_with_data() {
	$info_adherent = helladh_helloasso_api();

	$helloadhrents_options = get_option( 'ha_option_name' ); // Array of All Options
	$function = $helloadhrents_options['custom_function'];

	$function($info_adherent);
}

add_action('wp_ajax_ajax_helladh_test_custom_function', 'helladh_test_custom_function_with_data');

?>