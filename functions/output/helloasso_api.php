<?php


function helladh_helloasso_api() {

    $helloadhrents_options = get_option( 'ha_option_name' ); // Array of All Options


    // Connexion HelloAsso avec la méthode Client Credentials

    $client_id = $helloadhrents_options['ha_client_id'];
    $client_secret = $helloadhrents_options['ha_client_secret'];

    $url = "https://api.helloasso.com/oauth2/token";
    $body = "grant_type=client_credentials&client_id=" . $client_id . "&client_secret=" . $client_secret;
    $args = [
        'headers' => array(
            'Content-Type'  => 'application/x-www-form-urlencoded',
            'user-agent'    => ''
        ),
        'method'      => 'POST',
        'blocking'     => true,
        'body' => $body,
        'data_format' => 'body'
    ];

    $response = wp_remote_request($url, $args);
    $json = json_decode($response['body']);
    $access_token = $json->access_token;


        // Test API
    switch( current_filter() ) {
        case 'wp_ajax_ajax_helladh_test_api_ha':
            echo ('<strong>Obtention du jeton de connexion :</strong> ' . wp_kses_post(print_r($json, true)));
        break;
    }

    // Récupération des données HelloAsso

    $date_now = str_replace('+00:00', '', date("c", mktime(0, 0, 0, date("m")+1, date("d"), date("Y"))));
    $jours = $helloadhrents_options['ha_delai_jour'];
    $date_debut = str_replace('+00:00', '', date("c", mktime(0, 0, 0, date("m"), date("d")-$jours, date("Y"))));

    $url = 'https://api.helloasso.com/v5/organizations/' . $helloadhrents_options['ha_org_id'] . '/forms/Membership/' . $helloadhrents_options['ha_camp_id'] . '/items?from=' . $date_debut . '&to=' . $date_now . '&tierTypes=Membership&withDetails=true&retrieveAll=true&itemStates=Processed';
    $args = [
        'headers' => array(
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
            'user-agent'=> ''
        ),
        'method' => 'GET',
        'blocking' => true
    ];

    $json = wp_remote_request($url, $args);
    $json = json_decode($json['body'], true);

        // Test API
    switch( current_filter() ) {
    case 'wp_ajax_ajax_helladh_test_api_ha':
        echo ('<strong>Intégralité des données reçues pour la requête :</strong> ' . wp_kses_post(print_r($json, true)));
    break;
    }

    $info_adherent = [];

    $cf_to_retrieve = explode(';', $helloadhrents_options['helloasso_custom_fields']);
    $i = 0;


    foreach ($json['data'] as $adhesion) {

        $info_adherent[$i] = array(
            'mail' => $adhesion['payer']['email'],
            'campagne' => $helloadhrents_options['ha_camp_id'],
            'tarif' => $adhesion['name'],
            'prenom' => $adhesion['user']['firstName'],
            'nom' => $adhesion['user']['lastName'],
            'code_promo' => $adhesion['discount']['code'],
        );

        foreach ($adhesion['customFields'] as $cf) {

            foreach ($cf_to_retrieve as $cftr) {
                if ($cf['name'] == $cftr) {
                    $cftr = helladh_clean_string($cftr);
                    $info_adherent[$i][$cftr] = $cf['answer'];
                }
            }
        }

        $i++;
    }

        // Test API 
    switch( current_filter() ) {
        case 'wp_ajax_ajax_helladh_test_api_ha':
            echo ('<strong>Objet final servant de base aux applications suivantes. A chaque adhérent correspond un numéro, ses données sont synthétisées dans une array sous la forme [clé] => "valeur" :</strong> ' . wp_kses_post(print_r($info_adherent, true)));
        break;
    }

    helladh_write_log("Données récupérées de HelloAsso : " . print_r($info_adherent, true));

    return ($info_adherent);

}


function helladh_test_api_ha() {
    echo "Pour tester la bonne récupération des données, veuillez sauvegarder les paramètres puis appuyer sur le bouton suivant : ";
    echo '<button class="button_test_api_ha" type="button" role="button">Test API HelloAsso</button>';

    echo '<script>
        jQuery(".button_test_api_ha").click(function(){
            jQuery(".result_area_ha").css({"background-color": "black", "height": "15em", "resize": "vertical"});
            jQuery.get(ajaxurl,{"action": "ajax_helladh_test_api_ha"},
            function (msg) { jQuery(".result_area_ha").html(msg);});
        });
        </script>';
    echo '<pre class="result_area result_area_ha">Chargement en cours...</pre>';
}

add_action('wp_ajax_ajax_helladh_test_api_ha', 'helladh_helloasso_api');

?>