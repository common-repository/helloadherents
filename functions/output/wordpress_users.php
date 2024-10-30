<?php 

function helladh_wordpress_users($info_adherent) {
   // Préparation des données

    $helloadhrents_options = get_option( 'ha_option_name' ); // Array of All Options
    
    if (isset ($helloadhrents_options['wordpress_users_custom_role'])) { 
        $role = $helloadhrents_options['wordpress_users_custom_role'];
    } else {
        $role = $helloadhrents_options['wordpress_users_def_role'];
    }

    // Ajout des utilisateurs

    foreach ($info_adherent as $adh) {

        $userdata = array(
            'user_pass' => helladh_randomPassword(),
            'user_login' => $adh[$helloadhrents_options['wordpress_users_login']],
            'user_url' => $adh[$helloadhrents_options['wordpress_users_url']],
            'user_email' => $adh['mail'],
            'first_name' => $adh['prenom'],
            'last_name' => $adh['nom'],
            'rich_editing' => true,
            'syntax_highlighting' => true,
            'comment_shortcuts' => false,
            'show_admin_bar_front' => true,
            'role' => $role,
            'description' => $adh[$helloadhrents_options['wordpress_users_description']]
        );

        $user_id = wp_insert_user($userdata);


        if (! is_wp_error($user_id)) { $log = wp_kses_post("<strong>Utilisateur créé pour l'adresse " . $adh['mail'] . " :</strong> id n°" . $user_id . '</br>');} 
        else { $log = wp_kses_post("Echec de la création du compte pour l'adresse" . $adh['mail'] . '</br>'); }

        // Test Wordpress User
        switch( current_filter() ) {
            case 'wp_ajax_ajax_helladh_test_wordpress_users':
            echo $log;
            break;
        }
        helladh_write_log($log);

        // Envoi email hors mode test
        switch( current_filter() ) {
            case 'hello_adh_hook':
                if (! is_wp_error($user_id)) {
                    $result_email = helladh_wordpress_users_email_auto($adh['mail'], $helloadhrents_options['wordpress_users_resetmail_title'], $helloadhrents_options['wordpress_users_resetmail_content'], $adh);
                    if ($result_email == false) { $log_mail = "Echec de l'envoi du mail à " . $adh['mail'] . '</br>';}
                    else { $log_mail = "Mail suivant envoyé avec succès à " . $adh['mail'] . " : " . $result_email . '</br>';}
                
                    helladh_write_log($log_mail);
                }
            break;
        } 
    }
}

function helladh_test_wordpress_users() {
    print('<p><i>NB :</br>
            - Veillez à choisir soigneusement le Rôle attribué à vos utilisateurs. N\'hésitez pas à aller voir la <a href="https://wordpress.org/support/article/roles-and-capabilities/" rel="noopener noreferer" target="blank">documentation sur les rôles par défaut de Wordpress</a>, à modifier les autorisations liées aux rôles par défaut, voire créer votre propre rôle personnalisé. Pour cela, codez selon vos besoins ou utilisez un plugin dédié comme l\'excellent <a href="https://wordpress.org/plugins/capability-manager-enhanced/" rel="noopener noreferer" target="blank">PublishPress Capabilities</a>.</br>
            - Les comptes sont créés avec un mot de passe généré aléatoirement, auquel vous aurez accès en tant qu\'administrateur. Pour des raisons de sécurité, il n\'est pas recommandé d\'envoyer le mot de passe en clair à vos utilisateurs. C\'est pourquoi vous devrez soit leur demander de venir eux-même sur votre site pour faire une procédure "Mot de passe oublié", soit paramétrer un envoi automatique d\'email qui leur donnera un lien pour cette procédure.</i></p>');
    echo "Pour tester l'ajout d'utilisateurs Wordpress, veuillez sauvegarder les paramètres puis appuyer sur le bouton suivant : ";
    echo '<button class="button_test_wordpress_users" type="button" role="button">Test Utilisateurs Wordpress</button>';

    echo '<script>
        jQuery(".button_test_wordpress_users").click(function(){
            jQuery(".result_area_wordpress_users").css({"background-color": "black", "height": "15em", "resize": "vertical"});
            jQuery.get(ajaxurl,{"action": "ajax_helladh_test_wordpress_users"},
            function (msg) { jQuery(".result_area_wordpress_users").html(msg);});
        });
        </script>';
    echo '<pre class="result_area result_area_wordpress_users">Chargement en cours...</pre>';
}

function helladh_test_wordpress_users_with_data() {
    $info_adherent = helladh_helloasso_api();
    helladh_wordpress_users($info_adherent);
    wp_die();
}

add_action('wp_ajax_ajax_helladh_test_wordpress_users', 'helladh_test_wordpress_users_with_data');


function helladh_wordpress_users_email_auto($to, $title, $content, $adh) {

    preg_match_all("/%(.*?)%/", $content, $matches);
    foreach ($matches[1] as $match) {
        $replace[] = $adh[$match];
    }
    $content = stripslashes(html_entity_decode(preg_replace_callback('/%(.*?)%/', function($matches) use (&$replace) { return array_shift($replace); }, $content)));

    add_filter('wp_mail_content_type', 'helladh_set_html_content_type');
    $result_wp_mail = wp_mail($to, $title, $content);
    remove_filter('wp_mail_content_type', 'helladh_set_html_content_type');
    if ($result_wp_mail == false) {return $result_wp_mail;} else { return $content; }
}

function helladh_set_html_content_type() {return 'text/html';}

?>