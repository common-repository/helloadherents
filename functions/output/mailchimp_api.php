<?php

function helladh_mailchimp_api($info_adherent) {

	$helloadhrents_options = get_option( 'ha_option_name' ); // Array of All Options

	foreach ($info_adherent as $adh) {

	    // Ajouter ou mettre à jour un adhérent - Gestion des Merge Fields

		$i = 0;
		$keys_merge_field = explode(';', $helloadhrents_options['key_merge_fields_mailchimp']);
		$merge_fields = explode(';', $helloadhrents_options['merge_fields_mailchimp']);
		$merge_fields_curl_array = [];

	    foreach ($merge_fields as $merge_field) {
			if ($merge_field == "ADDRESS") {
				if ($adh[$keys_merge_field[$i]] != '') { $city = $adh[$keys_merge_field[$i]]; } else { $city = '.'; }
				$merge_fields_curl_part = '"' . $merge_field . '":{' . '"addr1":".","city":"' . $city . '","zip":".","country":"FR"' . '}';	
			} else {
				$merge_fields_curl_part = '"' . $merge_field . '":"' . $adh[$keys_merge_field[$i]] . '"';	
			}
	    	
	    	array_push($merge_fields_curl_array, $merge_fields_curl_part);
	    	$i++;
	    }
    	$merge_fields_curl = implode(',', $merge_fields_curl_array);

    	$url = "https://" . $helloadhrents_options['mailchimp_server'] . ".api.mailchimp.com/3.0/lists/" . $helloadhrents_options['mailchimp_id_audience'] . "/members/" . md5(strtolower($adh['mail'])) . "?skip_merge_validation=false";

    	$body = '{"email_address":"' . $adh['mail'] . '","status_if_new":"subscribed","merge_fields":{' . $merge_fields_curl . '}}';

   // Améliorations à prévoir : date de naissance,"BIRTHDAY":"' . date('m/d', strtotime($date_naissance)) . '
   // et partie Adresse : addr1, city, zip, country devront être demandés dans un champ séparé, peut-être faire une option pour intégrer ou non la partie adresse ...

		$encodedAuth = base64_encode('anystring:' . $helloadhrents_options['mailchimp_cle_api']);

	    $args = [
	        'headers' => array(
	        	'Content-Type' => 'application/x-www-form-urlencoded',
	        	"Authorization" => "Basic " . $encodedAuth
	        ),
	        'method' => 'PUT',
	        'blocking' => true,
	        'body' => $body
	    ];

	    $json = wp_remote_request($url, $args);
	    $json = json_decode($json['body']);

	    switch( current_filter() ) {
	        case 'wp_ajax_ajax_helladh_test_api_mailchimp':
			    echo("<strong>Réponse mailchimp ajout/MAJ contact pour " . esc_attr($adh['mail']) . " :</strong> " . wp_kses_post(print_r($json->merge_fields, true)));
	        break;
	    }

	    helladh_write_log("Réponse mailchimp ajout/MAJ contact pour " . $adh['mail'] . " : " . print_r($json->merge_fields, true));


	    // Ajout des tags

    	$j = 0;
		$autres_champs = explode(';', $helloadhrents_options['key_tags_mailchimp']);
		$tags_mailchimp = explode(';', $helloadhrents_options['tags_mailchimp']);
		$tags_array = [];
		$tags_output = '';

	    foreach ($autres_champs as $champ) {
	    	$str_replace = str_replace('$valeur', $adh[$champ], $tags_mailchimp[$j]);
	    	$tags_output .= $str_replace . '<br>';
	    	$tags_array[$j] = '{"name": "' . $str_replace . '", "status": "active"}';
	    	$tags_curl = implode(', ', $tags_array);
	    	$j++;
	    }


    	$url = "https://" . $helloadhrents_options['mailchimp_server'] . ".api.mailchimp.com/3.0/lists/" . $helloadhrents_options['mailchimp_id_audience'] . "/members/" . md5(strtolower($adh['mail'])) . "/tags";

    	$body = '{"tags":[' . $tags_curl . '], "is_syncing":false}';

	    $args = [
	        'headers' => array(
	        	'Content-Type' => 'application/x-www-form-urlencoded',
	        	"Authorization" => "Basic " . $encodedAuth
	        ),
	        'method' => 'POST',
	        'blocking' => true,
	        'body' => $body
	    ];

	    $json = wp_remote_request($url, $args);
	    $json = json_decode($json['body']);


	    helladh_write_log("<strong>Réponse mailchimp requête Tags pour " . $adh['mail'] . " :</strong> " . print_r($json, true) . 	'Ajout des tags suivants <br>' . $tags_output);

	    switch( current_filter() ) {
	        case 'wp_ajax_ajax_helladh_test_api_mailchimp':
	    		echo("<strong>Réponse mailchimp requête Tags pour " . esc_attr($adh['mail']) . " :</strong> " . wp_kses_post(print_r($json, true)) . 	'Ajout des tags suivants <br>' . wp_kses_post($tags_output));
	        break;
	    }
	}
}


function helladh_test_api_mailchimp() {
    echo "Pour tester les fonctionnalités Mailchimp, veuillez sauvegarder les paramètres puis appuyer sur le bouton suivant : ";
    echo '<button class="button_test_api_mailchimp" type="button" role="button">Test API Mailchimp</button>';

    echo '<script>
        jQuery(".button_test_api_mailchimp").click(function(){
            jQuery(".result_area_mailchimp").css({"background-color": "black", "height": "15em", "resize": "vertical"});
            jQuery.get(ajaxurl,{"action": "ajax_helladh_test_api_mailchimp"},
            function (msg) { jQuery(".result_area_mailchimp").html(msg);});
        });
        </script>';
    echo '<pre class="result_area result_area_mailchimp">Chargement en cours...</pre>';
}

function helladh_test_mailchimp_api_with_data() {
	$info_adherent = helladh_helloasso_api();
	helladh_mailchimp_api($info_adherent);
}

add_action('wp_ajax_ajax_helladh_test_api_mailchimp', 'helladh_test_mailchimp_api_with_data');


?>