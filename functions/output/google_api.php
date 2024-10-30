<?php

use Firebase\JWT\JWT; 

function helladh_google_api($info_adherent) {

	$helloadhrents_options = get_option( 'ha_option_name' ); // Array of All Options

	// Génération du JWT
	$privateKey = str_replace('\n', "\n", $helloadhrents_options['cle_api_google']);

	$payload = array(
		"iss" => $helloadhrents_options['google_iss'],
	    "sub" => $helloadhrents_options['google_sub'],
	    "scope" => "https://www.googleapis.com/auth/admin.directory.group",
	    "aud" => "https://oauth2.googleapis.com/token",
	    "iat" => time(),
	    "exp" => time() + 3000
	);

	$jwt = JWT::encode($payload, $privateKey, 'RS256');

	// Demande token à google avec le JWT

	$url = 'https://oauth2.googleapis.com/token';

	$body = "grant_type=urn%3Aietf%3Aparams%3Aoauth%3Agrant-type%3Ajwt-bearer&assertion=" . print_r($jwt, true);

    $args = [
        'headers' => array(
        	'Content-Type' => 'application/x-www-form-urlencoded',
        ),
        'method' => 'POST',
        'blocking' => true,
        'body' => $body
    ];

    $json = wp_remote_request($url, $args);
    $json = json_decode($json['body']);
	$access_token = $json->access_token;


        // Test API
    switch( current_filter() ) {
        case 'wp_ajax_ajax_helladh_test_api_google':
		    echo("<strong>Réponse google pour l'obtention du token (demande d'accès) : </strong> " . wp_kses_post(print_r($json, true)));
        break;
    }

	// Ajout des emails aux groupes

	foreach ($info_adherent as $adh) {

	    $mail = $adh['mail'];
	    $tarifs = explode(';', $helloadhrents_options['helloasso_nom_tarif']);
	    $groupe = explode(';', $helloadhrents_options['google_groupkey']);
	    $i = 0;

	    foreach ($tarifs as $tarif) {
	    	if ($adh['tarif'] ==  $tarif) {

		    	$url = 'https://admin.googleapis.com/admin/directory/v1/groups/' . $groupe[$i] . '/members';

		    	$body = '{"email":"' . $mail . '","role":"MEMBER"}';

			    $args = [
			        'headers' => array(
			        	'Content-Type'  => 'application/json',
			        	'Accept'		=> 'application/json',
			        	'Authorization' => 'Bearer ' . $access_token,
			        	'Accept-Encoding' => 'gzip, deflate'
			        ),
			        'method' => 'POST',
			        'blocking' => true,
			        'body' => $body
			    ];

			    $json = wp_remote_request($url, $args);
			    $json = json_decode($json['body']);


			        // Test API
			    switch( current_filter() ) {
			        case 'wp_ajax_ajax_helladh_test_api_google':
		        		echo("<strong>Réponse google pour l'adresse " . esc_attr($mail) . " (groupe " . esc_attr($groupe[$i]) . ") :</strong> " . wp_kses_post(print_r($json, true)));
			        break;
			    }

		        helladh_write_log("Réponse google pour l'adresse " . $mail . " (groupe " . $groupe[$i] . ") : " . print_r($json, true));
	    	} 
	    	$i++;
	    }
	}
}


function helladh_test_api_google() {
    echo "Pour tester les fonctionnalités google groups, veuillez sauvegarder les paramètres puis appuyer sur le bouton suivant : ";
    echo '<button class="button_test_api_google" type="button" role="button">Test API Google</button>';

    echo '<script>
        jQuery(".button_test_api_google").click(function(){
            jQuery(".result_area_google").css({"background-color": "black", "height": "15em", "resize": "vertical"});
            jQuery.get(ajaxurl,{"action": "ajax_helladh_test_api_google"},
            function (msg) { jQuery(".result_area_google").html(msg);});
        });
        </script>';
    echo '<pre class="result_area result_area_google">Chargement en cours...</pre>';
}

function test_helladh_google_api_with_data() {
	$info_adherent = helladh_helloasso_api();
	helladh_google_api($info_adherent);
}

add_action('wp_ajax_ajax_helladh_test_api_google', 'test_helladh_google_api_with_data');


?>