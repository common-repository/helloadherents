<?php

class HelloAdhrents {
    private $helloadhrents_options;

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'helloadhrents_add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'helloadhrents_page_init' ) );
    }

    public function helloadhrents_add_plugin_page() {
        add_menu_page(
            'HelloAdhérents', // page_title
            'HelloAdhérents', // menu_title
            'manage_options', // capability
            'helloadhrents', // menu_slug
            array( $this, 'helloadhrents_create_admin_page' ), // function
            'dashicons-groups', // icon_url
            65 // position
        );
    }

    public function helloadhrents_create_admin_page() {
        $this->helloadhrents_options = get_option( 'ha_option_name' ); ?>

        <div class="wrap">
            <h2>HelloAdhérents - Paramétrage</h2>
            <?php settings_errors(); ?>

            <form method="post" action="options.php">
                <?php
                    settings_fields( 'ha_option_group' );
                    do_settings_sections_custom( 'helloadhrents-admin' );
                    submit_button();
                ?>
            </form>
        </div>
    <?php }

    public function helloadhrents_page_init() {


        register_setting(
            'ha_option_group', // option_group
            'ha_option_name', // option_name
            array( $this, 'helladh_sanitize' ) // sanitize_callback
        );

        add_settings_section(
            'cron_job', // id
            'Exécution automatique - Cron job', // title
            array( $this, 'helladh_cron_job_info' ), // callback
            'helloadhrents-admin' // page
        );

        add_settings_section(
            'helloasso', // id
            'Récupération des données - API HelloAsso', // title
            array( $this, 'helladh_helloasso_info' ), // callback
            'helloadhrents-admin' // page
        );

        add_settings_section(
            'output', // id
            'Envoi des données pour les services concernant vos adhérents', // title
            array( $this, 'helladh_output_info' ), // callback
            'helloadhrents-admin' // page
        );

        add_settings_field(
            'cj_wp_intervals', // id
            'Intervalles par défaut <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Ce paramètre ne sera pas pris en compte si vous renseignez un intervalle personnalisé (différent de 0).</span></span>', // title
            array( $this, 'cj_wp_intervals_callback' ), // callback
            'helloadhrents-admin', // page
            'cron_job' // section
        );

        add_settings_field(
            'cj_custom_intervals', // id
            'Intervalle personnalisé en secondes <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Intervalle d\'exécution du cron job en secondes. <i>Exemple : pour exécuter le programme tous les 2 jours, renseignez 2j * 24h * 3600s soit "172800"</i></span></span>', // title
            array( $this, 'cj_custom_intervals_callback' ), // callback
            'helloadhrents-admin', // page
            'cron_job' // section
        );

        add_settings_field(
            'ha_client_id', // id
            'Client ID HelloAsso <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Pour générer les identifiants de connexion à l\'API HelloAsso (Client ID et Client Secret), connectez-vous à HelloAsso et rendez-vous dans la rubrique Mon Compte -> Intégration et API.</span></span>', // title
            array( $this, 'ha_client_id_callback' ), // callback
            'helloadhrents-admin', // page
            'helloasso' // section
        );

        add_settings_field(
            'ha_client_secret', // id
            'Client Secret HelloAsso', // title
            array( $this, 'ha_client_secret_callback' ), // callback
            'helloadhrents-admin', // page
            'helloasso' // section
        );

        add_settings_field(
            'ha_org_id', // id
            'ID de l\'organisation HelloAsso <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">L\'ID de votre organisation est la partie qui suit immédiatement "https://admin.helloasso.com/" (ou "https://admin.helloasso.com/association/") dans l\'URL lorsque vous naviguez sur HelloAsso. <i>Exemple: "mon-association-loi-1901"</i></span></span>', // title
            array( $this, 'ha_org_id_callback' ), // callback
            'helloadhrents-admin', // page
            'helloasso' // section
        );

        add_settings_field(
            'ha_camp_id', // id
            'ID de la campagne HelloAsso <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">L\'ID de votre campagne est la partie qui suit immédiatement "https://www.helloasso.com/associations/*organisation_id*/adhesions/" dans l\'URL lorsque vous vous rendez dans l\'administration de votre campagne - Rubrique Mes adhésions -> Administrer. <i>Exemple: adhesion-2021-2022</i></span></span>', // title
            array( $this, 'ha_camp_id_callback' ), // callback
            'helloadhrents-admin', // page
            'helloasso' // section
        );

        add_settings_field(
            'ha_delai_jour', // id
            'Délai de récupération des données en jours <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Il est recommandé de mettre le même délai que pour l\'éxécution du cron job, + 1 jour. <i>Exemple : si vous choisissez d\'exécuter le cron job toutes les semaines, renseignez "8" pour obtenir les données des personnes ayant adhéré durant les 8 derniers jours.</i></span></span>', // title
            array( $this, 'ha_delai_jour_callback' ), // callback
            'helloadhrents-admin', // page
            'helloasso' // section
        );

        add_settings_field(
            'helloasso_nom_tarif', // id
            'Nom du (des) tarif(s) pour la campagne HelloAsso <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Si vous avez créé différents tarifs pour votre campagne, vous pourrez faire un usage différent des données des adhérents pour chaque tarif. Le nom exact est trouvable dans la rubrique Mes adhésions -> Administrer -> Edition -> Tarifs (ou ci-dessous en réalisant le test API HelloAsso, champ "name"). S\'il existe plusieurs tarifs à distinguer, saisissez leur nom séparé par un point virgule. Sinon, laissez ce champ vide. <i>Exemple : "Adhésion Tarif 1;Adhésion tarif 2"</i></span></span>', // title
            array( $this, 'helloasso_nom_tarif_callback' ), // callback
            'helloadhrents-admin', // page
            'helloasso' // section
        );

        add_settings_field(
            'helloasso_custom_fields', // id
            'Champs personnalisés <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Sélectionnez les données dont vous avez besoin, issues de champs personnalisés renseignés par vos adhérents lors de l\'inscription. En réalisant le test API HelloAsso ci-dessous, vous trouverez l\'intitulé de ces champs dans la partie customFields -> name pour chaque utilisateur. <i>Exemple : "Date de naissance;Numéro de téléphone"</i></span></span>', // title
            array( $this, 'helloasso_custom_fields_callback' ), // callback
            'helloadhrents-admin', // page
            'helloasso', // section
            array('class' => 'test_ha_after')
        );

        add_settings_field(
            'google_group_option', // id
            'Google groups', // title
            array( $this, 'google_group_callback' ), // callback
            'helloadhrents-admin', // page
            'output', // section
            array('class' => 'options_group')
        );

        add_settings_field(
            'cle_api_google', // id
            'Clé privée du compte de service Google <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Correspond au champ "private_key". <i>Exemple : "-----BEGIN PRIVATE KEY-----\[...]\n-----END PRIVATE KEY-----\n"</i></span></span>', // title
            array( $this, 'cle_api_google_callback' ), // callback
            'helloadhrents-admin', // page
            'output' // section
        );

        add_settings_field(
            'google_iss', // id
            'Email client du compte de service Google <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Correspond au "client_email", adresse se terminant par gserviceaccount.com. <i>Exemple : "[SERVICE-ACCOUNT-NAME]@[PROJECT_ID].iam.gserviceaccount.com"</i></span></span>', // title
            array( $this, 'google_iss_callback' ), // callback
            'helloadhrents-admin', // page
            'output' // section
        );

        add_settings_field(
            'google_sub', // id
            'Adresse email du compte administrateur du groupe Google <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Adresse email de l\'administrateur du groupe ayant les droits de modification. <i>Exemple : "webmaster@mon-asso.org"</i></span></span>', // title
            array( $this, 'google_sub_callback' ), // callback
            'helloadhrents-admin', // page
            'output' // section
        );

        add_settings_field(
            'google_groupkey', // id
            'Adresse email du (des) groupe(s) Google <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Si vous avez plusieurs groupes Google correspondant à plusieurs tarifs lors de l\'adhésion sur HelloAsso, renseignez ces adresses séparées par un point virgule, dans le même ordre que les tarifs de la campagne HelloAsso renseignés ci-dessus. <i>Exemple : "groupe1@mon-asso.org;groupe2@mon-asso.org" si vous aviez renseigné "Adhésion Tarif 1;Adhésion tarif 2"</i></span></span>', // title
            array( $this, 'google_groupkey_callback' ), // callback
            'helloadhrents-admin', // page
            'output', // section
            array('class' => 'test_google_after')
        );

        add_settings_field(
            'mailchimp_group_option', // id
            'Mailchimp', // title
            array( $this, 'mailchimp_group_callback' ), // callback
            'helloadhrents-admin', // page
            'output', // section
            array('class' => 'options_group')
        );

        add_settings_field(
            'mailchimp_id_audience', // id
            'Identifiant de l\'audience MailChimp <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Vous trouverez cette information sur Mailchimp dans la section Audience -> All contacts -> Settings -> Audience name and defaults. <i>Exemple : "1a2345bc6d"</i></span></span>', // title
            array( $this, 'mailchimp_id_audience_callback' ), // callback
            'helloadhrents-admin', // page
            'output' // section
        );

        add_settings_field(
            'mailchimp_server', // id
            'Serveur où est hébergé le compte MailChimp <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Lorsque vous êtes connectés à Mailchimp, cette information se trouve au début de l\'URL. <i>Exemple : "us2" si le début de l\'URL est "https://us2.admin.mailchimp[...]"</i></span></span>', // title
            array( $this, 'mailchimp_server_callback' ), // callback
            'helloadhrents-admin', // page
            'output' // section
        );

        add_settings_field(
            'mailchimp_cle_api', // id
            'Clé API MailChimp <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Vous trouverez cette information sur Mailchimp dans la section Integration -> API key. <i>Exemple : "123ab4c567de6781a6b-us2"</i></span></span>', // title
            array( $this, 'mailchimp_cle_api_callback' ), // callback
            'helloadhrents-admin', // page
            'output' // section
        );

        add_settings_field(
            'key_merge_fields_mailchimp', // id
            'Clé des données que vous souhaitez convertir en Merge Fields <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Si vous souhaitez renseigner des informations comme Merge Fields sur Mailchimp, rentrez ici le nom affecté à ces champs (lorsque vous réalisez le Test API HelloAsso ci-dessus, correspond à l\'une des clés de l\'array finale), séparés par un point-virgule. <i>Exemple : "prenom;nom;numro_de_tlphone"</i></span></span>', // title
            array( $this, 'key_merge_fields_mailchimp_callback' ), // callback
            'helloadhrents-admin', // page
            'output' // section
        );      

        add_settings_field(
            'merge_fields_mailchimp', // id
            'Nom des Merge Fields Mailchimp <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Insertion des Merge Fields selon les valeurs renseignées dans le champ précédent. Renseignez les noms dans le même ordre que le champ précédent, et séparés par un point-virgule. <i>Exemple : "FNAME;LNAME;PHONE"</i></span></span>', // title
            array( $this, 'merge_fields_mailchimp_callback' ), // callback
            'helloadhrents-admin', // page
            'output' // section
        );   

        add_settings_field(
            'key_tags_mailchimp', // id
            'Clé des données que vous souhaitez convertir en étiquettes (tags) <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Si vous souhaitez renseigner des informations comme étiquette (tag) sur Mailchimp, rentrez ici le nom affecté à ces champs (lorsque vous réalisez le Test API HelloAsso ci-dessus, correspond à l\'une des clés de l\'array finale), séparés par un point-virgule. <i>Exemple : "tarif;annee_bac"</i></span></span>', // title
            array( $this, 'key_tags_mailchimp_callback' ), // callback
            'helloadhrents-admin', // page
            'output' // section
        );      

        add_settings_field(
            'tags_mailchimp', // id
            'Etiquettes (tags) Mailchimp <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Création des tags selon les valeurs renseignées dans le champ précédent. Renseignez les tags dans le même ordre que le champ précédent, et séparés par un point-virgule. Vous pouvez intégrer la valeur correspondant à la clé grâce au code "$valeur". <i>Exemple : "$valeur;Bac $valeur" créera 2 étiquettes  "Adhésion tarif 1" et "Bac 2012".</i></span></span>', // title
            array( $this, 'tags_mailchimp_callback' ), // callback
            'helloadhrents-admin', // page
            'output', // section
            array('class' => 'test_mailchimp_after')
        );      

        add_settings_field(
            'wordpress_users_group_option', // id
            'Utilisateurs Wordpress ', // title
            array( $this, 'wordpress_users_group_callback' ), // callback
            'helloadhrents-admin', // page
            'output', // section
            array('class' => 'options_group')
        );

        add_settings_field(
            'wordpress_users_login', // id
            'Identifiant du compte wordpress <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Par défaut, correspond à l\'email renseigné dans HelloAsso. Si vous souhaitez utiliser une autre valeur, renseignez ici le nom affecté à ce champ (lorsque vous réalisez le Test API HelloAsso ci-dessus, correspond à l\'une des clés de l\'array finale). <i>Exemple : "surnom"</i></span></span>', // title
            array( $this, 'wordpress_users_login_callback' ), // callback
            'helloadhrents-admin', // page
            'output' // section
        );

        add_settings_field(
            'wordpress_users_url', // id
            'URL <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Lien vers un site web renseigné par votre adhérent HelloAsso. Renseignez ici le nom affecté à ce champ (lorsque vous réalisez le Test API HelloAsso ci-dessus, correspond à l\'une des clés de l\'array finale)<i>Exemple : "site_web"</i></span></span>', // title
            array( $this, 'wordpress_users_url_callback' ), // callback
            'helloadhrents-admin', // page
            'output' // section
        );

        add_settings_field(
            'wordpress_users_email', // id
            'Email <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Email rattaché au compte Wordpress. Celle-ci pourra servir d\'identifiant même si vous choisissez autre chose pour le champ "Identifiant".</i></span></span>', // title
            array( $this, 'wordpress_users_email_callback' ), // callback
            'helloadhrents-admin', // page
            'output' // section
        );

        add_settings_field(
            'wordpress_users_admin_bar', // id
            'Montrer la barre d\'administration <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Correspond à la barre en haut de page lorsque l\'utilisateur est connecté</i></span></span>', // title
            array( $this, 'wordpress_users_admin_bar_callback' ), // callback
            'helloadhrents-admin', // page
            'output' // section
        );

        add_settings_field(
            'wordpress_users_def_role', // id
            'Rôle à attribuer <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Les rôles Wordpress permettent de définir des groupes d\'utilisateurs avec différents droits et limitations sur votre site. Les rôles par défaut sont listés ici, ce paramètre ne sera pas pris en compte si vous renseignez un rôle personnalisé ci-dessous.</i></span></span>', // title
            array( $this, 'wordpress_users_def_role_callback' ), // callback
            'helloadhrents-admin', // page
            'output' // section
        );

        add_settings_field(
            'wordpress_users_custom_role', // id
            'Rôle personnalisé à attribuer <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Renseignez le slug de votre rôle personnalisé. Ex: "mon_role_personnalisé"</i></span></span>', // title
            array( $this, 'wordpress_users_custom_role_callback' ), // callback
            'helloadhrents-admin', // page
            'output' // section
        );

        add_settings_field(
            'wordpress_users_description', // id
            'Clé des données que vous souhaitez convertir en "Renseignements biographiques" <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Si vous souhaitez renseigner des informations comme Renseignements biographiques ("description"), rentrez ici le nom affecté à ces champs (lorsque vous réalisez le Test API HelloAsso ci-dessus, correspond à l\'une des clés de l\'array finale). <i>Exemple : "biographie"</i></span></span>', // title
            array( $this, 'wordpress_users_description_callback' ), // callback
            'helloadhrents-admin', // page
            'output' // section
        );

        add_settings_field(
            'wordpress_users_send_resetmail', // id
            'Envoyer automatiquement un mail à la création du compte <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Cet email permettra d\'informer vos adhérents de la création de leur compte, et de leur envoyer un lien pour définir leur mot de passe. Attention : si vous utilisez le bouton "Test" ci-dessous, le compte sera créé mais le mail ne sera pas envoyé. Il est recommandé de tester la bonne création de compte(s) avec le bouton de test, puis de supprimer ce(s) compte(s) et de relancer la manoeuvre complète grâce au Cron Job pour s\'assurer que tous les utilisateurs soient bien prévenus de la création de leur compte.</span></span>', // title
            array( $this, 'wordpress_users_send_resetmail_callback' ), // callback
            'helloadhrents-admin', // page
            'output' // section
        );

        add_settings_field(
            'wordpress_users_resetmail_title', // id
            'Titre du mail envoyé à vos adhérents ', // title
            array( $this, 'wordpress_users_resetmail_title_callback' ), // callback
            'helloadhrents-admin', // page
            'output' // section
        );

        add_settings_field(
            'wordpress_users_resetmail_content', // id
            'Contenu du mail envoyé à vos adhérents <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Modifiez ce message à loisir, et pensez à modifier le lien vers la page de réinitialisation du mot de passe fournie en l\'adaptant l\'URL. Vous pouvez personnaliser le contenu en plaçant le nom affecté à un champ (lorsque vous réalisez le Test API HelloAsso ci-dessus, correspond à l\'une des clés de l\'array finale) entre 2 signes "%". <i>Exemple: "Bonjour %prenom%"</i></span></span>', // title
            array( $this, 'wordpress_users_resetmail_content_callback' ), // callback
            'helloadhrents-admin', // page
            'output', // section
            array('class' => 'test_wordpress_users_after')
        );
        add_settings_field(
            'custom_function_group_option', // id
            'Fonction personnalisée ', // title
            array( $this, 'custom_function_group_callback' ), // callback
            'helloadhrents-admin', // page
            'output', // section
            array('class' => 'options_group')
        );

        add_settings_field(
            'custom_function', // id
            'Nom de la fonction PHP à exécuter <span class="tooltip"><span class="dashicons dashicons-info-outline"></span><span class="tooltiptext">Fonction personnalisée à exécuter, pouvant être définie où vous souhaitez (thème enfant, plugin...). Pour se servir des données issues de HelloAsso, elle devra prendre comme argument "&#36;info_adherent". Pour afficher un résultat grâce au bouton de test ci-dessous, utilisez un "echo &#36;résultat;". <i>Exemple : "ma_fonction_personnalisée".</i></span></span>', // title
            array( $this, 'custom_function_callback' ), // callback
            'helloadhrents-admin', // page
            'output', // section
            array('class' => 'test_custom_function_after')
        );

    }

    public function helladh_sanitize($input) {
        $sanitary_values = array();
        
        if ( isset( $input['cj_wp_intervals'] ) ) {
            $sanitary_values['cj_wp_intervals'] = sanitize_text_field( $input['cj_wp_intervals'] );
        }

        if ( isset( $input['cj_custom_intervals'] ) ) {
            $sanitary_values['cj_custom_intervals'] = absint( $input['cj_custom_intervals'] );
        }

        if ( isset( $input['ha_client_id'] ) ) {
            $sanitary_values['ha_client_id'] = sanitize_text_field( $input['ha_client_id'] );
        }

        if ( isset( $input['ha_client_secret'] ) ) {
            $sanitary_values['ha_client_secret'] = sanitize_text_field( $input['ha_client_secret'] );
        }

        if ( isset( $input['ha_org_id'] ) ) {
            $sanitary_values['ha_org_id'] = sanitize_text_field( $input['ha_org_id'] );
        }

        if ( isset( $input['ha_camp_id'] ) ) {
            $sanitary_values['ha_camp_id'] = sanitize_text_field( $input['ha_camp_id'] );
        }

        if ( isset( $input['ha_delai_jour'] ) ) {
            $sanitary_values['ha_delai_jour'] = sanitize_text_field( $input['ha_delai_jour'] );
        }

        if ( isset( $input['helloasso_nom_tarif'] ) ) {
            $sanitary_values['helloasso_nom_tarif'] = sanitize_text_field( $input['helloasso_nom_tarif'] );
        }
        if ( isset( $input['helloasso_custom_fields'] ) ) {
            $sanitary_values['helloasso_custom_fields'] = sanitize_text_field( $input['helloasso_custom_fields'] );
        }
        if ( isset( $input['google_group_option'] ) ) {
            $sanitary_values['google_group_option'] = sanitize_text_field( $input['google_group_option'] );
        }
        if ( isset( $input['cle_api_google'] ) ) {
            $sanitary_values['cle_api_google'] = sanitize_text_field( $input['cle_api_google'] );
        }
        if ( isset( $input['google_iss'] ) ) {
            $sanitary_values['google_iss'] = sanitize_text_field( $input['google_iss'] );
        }
        if ( isset( $input['google_sub'] ) ) {
            $sanitary_values['google_sub'] = sanitize_text_field( $input['google_sub'] );
        }
        if ( isset( $input['google_groupkey'] ) ) {
            $sanitary_values['google_groupkey'] = sanitize_text_field( $input['google_groupkey'] );
        }
        if ( isset( $input['mailchimp_group_option'] ) ) {
            $sanitary_values['mailchimp_group_option'] = sanitize_text_field( $input['mailchimp_group_option'] );
        }        
        if ( isset( $input['mailchimp_id_audience'] ) ) {
            $sanitary_values['mailchimp_id_audience'] = sanitize_text_field( $input['mailchimp_id_audience'] );
        }
        if ( isset( $input['mailchimp_server'] ) ) {
            $sanitary_values['mailchimp_server'] = sanitize_text_field( $input['mailchimp_server'] );
        }
        if ( isset( $input['mailchimp_cle_api'] ) ) {
            $sanitary_values['mailchimp_cle_api'] = sanitize_text_field( $input['mailchimp_cle_api'] );
        }
        if ( isset( $input['key_merge_fields_mailchimp'] ) ) {
            $sanitary_values['key_merge_fields_mailchimp'] = sanitize_text_field( $input['key_merge_fields_mailchimp'] );
        }
        if ( isset( $input['merge_fields_mailchimp'] ) ) {
            $sanitary_values['merge_fields_mailchimp'] = sanitize_text_field( $input['merge_fields_mailchimp'] );
        }
        if ( isset( $input['key_tags_mailchimp'] ) ) {
            $sanitary_values['key_tags_mailchimp'] = sanitize_text_field( $input['key_tags_mailchimp'] );
        }
        if ( isset( $input['tags_mailchimp'] ) ) {
            $sanitary_values['tags_mailchimp'] = sanitize_text_field( $input['tags_mailchimp'] );
        }
        if ( isset( $input['wordpress_users_group_option'] ) ) {
            $sanitary_values['wordpress_users_group_option'] = sanitize_text_field( $input['wordpress_users_group_option'] );
        }
        if ( isset( $input['wordpress_users_login'] ) ) {
            $sanitary_values['wordpress_users_login'] = sanitize_text_field( $input['wordpress_users_login'] );
        }
        if ( isset( $input['wordpress_users_url'] ) ) {
            $sanitary_values['wordpress_users_url'] = sanitize_text_field( $input['wordpress_users_url'] );
        }
        if ( isset( $input['wordpress_users_email'] ) ) {
            $sanitary_values['wordpress_users_email'] = sanitize_text_field( $input['wordpress_users_email'] );
        }
        if ( isset( $input['wordpress_users_admin_bar'] ) ) {
            $sanitary_values['wordpress_users_admin_bar'] = sanitize_text_field( $input['wordpress_users_admin_bar'] );
        }
        if ( isset( $input['wordpress_users_def_role'] ) ) {
            $sanitary_values['wordpress_users_def_role'] = sanitize_text_field( $input['wordpress_users_def_role'] );
        }
        if ( isset( $input['wordpress_users_custom_role'] ) ) {
            $sanitary_values['wordpress_users_custom_role'] = sanitize_text_field( $input['wordpress_users_custom_role'] );
        }
        if ( isset( $input['wordpress_users_description'] ) ) {
            $sanitary_values['wordpress_users_description'] = sanitize_text_field( $input['wordpress_users_description'] );
        }
        if ( isset( $input['wordpress_users_send_resetmail'] ) ) {
            $sanitary_values['wordpress_users_send_resetmail'] = sanitize_text_field( $input['wordpress_users_send_resetmail'] );
        }
        if ( isset( $input['wordpress_users_resetmail_title'] ) ) {
            $sanitary_values['wordpress_users_resetmail_title'] = sanitize_text_field( $input['wordpress_users_resetmail_title'] );
        }
        if ( isset( $_POST['wordpress_users_resetmail_content'] ) ) {
            $sanitary_values['wordpress_users_resetmail_content'] = htmlentities(wpautop( $_POST['wordpress_users_resetmail_content'] ));
        }

        if ( isset( $input['custom_function_group_option'] ) ) {
            $sanitary_values['custom_function_group_option'] = sanitize_text_field( $input['custom_function_group_option'] );
        }
        if ( isset( $input['custom_function'] ) ) {
            $sanitary_values['custom_function'] = sanitize_text_field( $input['custom_function'] );
        }

        return $sanitary_values;
    }

    public function helladh_cron_job_info() {
        print("<span class='option_group_info'>Le Cron Job permet d'exécuter automatiquement une tâche à intervalles réguliers. Déterminez ici à quelle fréquence vous souhaitez réaliser les opérations concernant vos adhérents.</span>");
    }

    public function helladh_helloasso_info() {
        print("<span class='option_group_info'>Informations nécessaires pour récupérer les données de vos adhérents via l'API HelloAsso.</span>");
    }

    public function helladh_output_info() {
        print("<span class='option_group_info'>Paramétrage des différents services nécessitant des informations sur vos adhérents (mailing list, accès réservés...). Cette liste pourrait être allongée en fonction des besoins.</span>");
    }

    public function cj_wp_intervals_callback() {
        $values = array(3600, 86400, 604800);
        $names = array('1 fois par heure', '1 fois par jour', '1 fois par semaine');
        $i = 0;

        print('<select class="regular-text" name="ha_option_name[cj_wp_intervals]" id="cj_wp_intervals">');
        foreach ($values as $value) {
            $selected = ($this->helloadhrents_options['cj_wp_intervals'] == $value) ? "selected" : "";
            print ('<option value="' . $value . '" ' . $selected . '> ' . $names[$i] . '</option>');
            $i++;
        }
        print('</select>');
    }

    public function cj_custom_intervals_callback() {
        printf(
            '<input class="regular-text" type="number" name="ha_option_name[cj_custom_intervals]" id="cj_custom_intervals" value="%s">',
            isset( $this->helloadhrents_options['cj_custom_intervals'] ) ? esc_attr( $this->helloadhrents_options['cj_custom_intervals']) : ''
        );
    }

    public function ha_client_id_callback() {
        printf(
            '<input class="regular-text" type="text" name="ha_option_name[ha_client_id]" id="ha_client_id" value="%s">',
            isset( $this->helloadhrents_options['ha_client_id'] ) ? esc_attr( $this->helloadhrents_options['ha_client_id']) : ''
        );
    }

    public function ha_client_secret_callback() {
        printf(
            '<input class="regular-text" type="password" name="ha_option_name[ha_client_secret]" id="ha_client_secret" value="%s">',
            isset( $this->helloadhrents_options['ha_client_secret'] ) ? esc_attr( $this->helloadhrents_options['ha_client_secret']) : ''
        );
    }

    public function ha_org_id_callback() {
        printf(
            '<input class="regular-text" type="text" name="ha_option_name[ha_org_id]" id="ha_org_id" value="%s">',
            isset( $this->helloadhrents_options['ha_org_id'] ) ? esc_attr( $this->helloadhrents_options['ha_org_id']) : ''
        );
    }

    public function ha_camp_id_callback() {
        printf(
            '<input class="regular-text" type="text" name="ha_option_name[ha_camp_id]" id="ha_camp_id" value="%s">',
            isset( $this->helloadhrents_options['ha_camp_id'] ) ? esc_attr( $this->helloadhrents_options['ha_camp_id']) : ''
        );
    }

    public function ha_delai_jour_callback() {
        printf(
            '<input class="regular-text" type="number" name="ha_option_name[ha_delai_jour]" id="ha_delai_jour" value="%s">',
            isset( $this->helloadhrents_options['ha_delai_jour'] ) ? esc_attr( $this->helloadhrents_options['ha_delai_jour']) : ''
        );
    }

    public function helloasso_nom_tarif_callback() {
        printf(
            '<input class="regular-text" type="text" name="ha_option_name[helloasso_nom_tarif]" id="helloasso_nom_tarif" value="%s">',
            isset( $this->helloadhrents_options['helloasso_nom_tarif'] ) ? esc_attr( $this->helloadhrents_options['helloasso_nom_tarif']) : ''
        );
    }

    public function helloasso_custom_fields_callback() {
        printf(
            '<input class="regular-text" type="text" name="ha_option_name[helloasso_custom_fields]" id="helloasso_custom_fields" value="%s">',
            isset( $this->helloadhrents_options['helloasso_custom_fields'] ) ? esc_attr( $this->helloadhrents_options['helloasso_custom_fields']) : ''
        );
    }

    public function google_group_callback() {
        printf(
            ' <input type="checkbox" class="invisible_checkbox options_group" name="ha_option_name[google_group_option]" id="google_group_option" %s value="true"><label for="google_group_option" class="toggle" id="toggle_google_group_option"></label>
            <br/>
            La gestion de groupes google nécessite de créer un "<a href="https://cloud.google.com/iam/docs/creating-managing-service-accounts#creating_a_service_account" rel="noopener noreferer" target="blank">Compte de service Google</a>" et le paramétrer pour qu\'il ait les autorisations suffisantes.',
            isset( $this->helloadhrents_options['google_group_option'] ) ? 'checked' : ''
        );
    }

    public function cle_api_google_callback() {
        printf(
            '<input class="regular-text" type="password" name="ha_option_name[cle_api_google]" id="cle_api_google" value="%s">',
            isset( $this->helloadhrents_options['cle_api_google'] ) ? esc_attr( $this->helloadhrents_options['cle_api_google']) : ''
        );
    }

    public function google_iss_callback() {
        printf(
            '<input class="regular-text" type="text" name="ha_option_name[google_iss]" id="google_iss" value="%s">',
            isset( $this->helloadhrents_options['google_iss'] ) ? esc_attr( $this->helloadhrents_options['google_iss']) : ''
        );
    }

    public function google_sub_callback() {
        printf(
            '<input class="regular-text" type="text" name="ha_option_name[google_sub]" id="google_sub" value="%s">',
            isset( $this->helloadhrents_options['google_sub'] ) ? esc_attr( $this->helloadhrents_options['google_sub']) : ''
        );
    }

    public function google_groupkey_callback() {
        printf(
            '<input class="regular-text" type="text" name="ha_option_name[google_groupkey]" id="google_groupkey" value="%s">',
            isset( $this->helloadhrents_options['google_groupkey'] ) ? esc_attr( $this->helloadhrents_options['google_groupkey']) : ''
        );
    }

    public function mailchimp_group_callback() {
        printf(
            ' <input type="checkbox" class="invisible_checkbox options_group" name="ha_option_name[mailchimp_group_option]" id="mailchimp_group_option" %s value="true"><label for="mailchimp_group_option" class="toggle" id="toggle_mailchimp_group_option"></label>',
            isset( $this->helloadhrents_options['mailchimp_group_option'] ) ? 'checked' : ''
        );
    }

    public function mailchimp_id_audience_callback() {
        printf(
            '<input class="regular-text" type="text" name="ha_option_name[mailchimp_id_audience]" id="mailchimp_id_audience" value="%s">',
            isset( $this->helloadhrents_options['mailchimp_id_audience'] ) ? esc_attr( $this->helloadhrents_options['mailchimp_id_audience']) : ''
        );
    }

    public function mailchimp_server_callback() {
        printf(
            '<input class="regular-text" type="text" name="ha_option_name[mailchimp_server]" id="mailchimp_server" value="%s">',
            isset( $this->helloadhrents_options['mailchimp_server'] ) ? esc_attr( $this->helloadhrents_options['mailchimp_server']) : ''
        );
    }

    public function mailchimp_cle_api_callback() {
        printf(
            '<input class="regular-text" type="password" name="ha_option_name[mailchimp_cle_api]" id="mailchimp_cle_api" value="%s">',
            isset( $this->helloadhrents_options['mailchimp_cle_api'] ) ? esc_attr( $this->helloadhrents_options['mailchimp_cle_api']) : ''
        );
    }

    public function key_merge_fields_mailchimp_callback() {
        printf(
            '<input class="regular-text" type="text" name="ha_option_name[key_merge_fields_mailchimp]" id="key_merge_fields_mailchimp" value="%s">',
            isset( $this->helloadhrents_options['key_merge_fields_mailchimp'] ) ? esc_attr( $this->helloadhrents_options['key_merge_fields_mailchimp']) : ''
        );
    }

    public function merge_fields_mailchimp_callback() {
        printf(
            '<input class="regular-text" type="text" name="ha_option_name[merge_fields_mailchimp]" id="merge_fields_mailchimp" value="%s">',
            isset( $this->helloadhrents_options['merge_fields_mailchimp'] ) ? esc_attr( $this->helloadhrents_options['merge_fields_mailchimp']) : ''
        );
    }

    public function key_tags_mailchimp_callback() {
        printf(
            '<input class="regular-text" type="text" name="ha_option_name[key_tags_mailchimp]" id="key_tags_mailchimp" value="%s">',
            isset( $this->helloadhrents_options['key_tags_mailchimp'] ) ? esc_attr( $this->helloadhrents_options['key_tags_mailchimp']) : ''
        );
    }

    public function tags_mailchimp_callback() {
        printf(
            '<input class="regular-text" type="text" name="ha_option_name[tags_mailchimp]" id="tags_mailchimp" value="%s">',
            isset( $this->helloadhrents_options['tags_mailchimp'] ) ? esc_attr( $this->helloadhrents_options['tags_mailchimp']) : ''
        );
    }

    public function wordpress_users_group_callback() {
        printf(
            '<input type="checkbox" class="invisible_checkbox options_group" name="ha_option_name[wordpress_users_group_option]" id="wordpress_users_group_option" %s value="true"><label for="wordpress_users_group_option" class="toggle" id="toggle_wordpress_users_group_option"></label>
            <br/>
            Création de comptes utilisateurs sur votre site Wordpress pour vos adhérents. Cette section utilise la fonction "<a href="https://developer.wordpress.org/reference/functions/wp_insert_user/" rel="noopener noreferer" target="blank">wp_insert_user</a>".',
            isset( $this->helloadhrents_options['wordpress_users_group_option'] ) ? 'checked' : ''
        );
    }

    public function wordpress_users_login_callback() {
        printf(
            '<input class="regular-text" type="text" name="ha_option_name[wordpress_users_login]" id="wordpress_users_login" value="%s">',
            isset( $this->helloadhrents_options['wordpress_users_login'] ) ? esc_attr( $this->helloadhrents_options['wordpress_users_login']) : 'mail'
        );        
    }

    public function wordpress_users_url_callback() {
        printf(
            '<input class="regular-text" type="text" name="ha_option_name[wordpress_users_url]" id="wordpress_users_url" value="%s">',
            isset( $this->helloadhrents_options['wordpress_users_url'] ) ? esc_attr( $this->helloadhrents_options['wordpress_users_url']) : ''
        );        
    }

    public function wordpress_users_email_callback() {
        printf(
            '<input class="regular-text" type="text" name="ha_option_name[wordpress_users_email]" id="wordpress_users_email" value="%s">',
            isset( $this->helloadhrents_options['wordpress_users_email'] ) ? esc_attr( $this->helloadhrents_options['wordpress_users_email']) : 'mail'
        );        
    }

    public function wordpress_users_admin_bar_callback() {
        printf(
            '<input type="checkbox" name="ha_option_name[wordpress_users_admin_bar]" id="wordpress_users_admin_bar" %s>',
            isset( $this->helloadhrents_options['wordpress_users_admin_bar'] ) ? 'checked' : ''
        );        
    }

    public function wordpress_users_def_role_callback() {
        print(
            '<select class="regular-text" name="ha_option_name[wordpress_users_def_role]" id="wordpress_users_def_role">
                <option value="subscriber" '); if($this->helloadhrents_options['wordpress_users_def_role'] == "subscriber") { print("selected");} print('>Abonné</option>
                <option value="contributor" '); if($this->helloadhrents_options['wordpress_users_def_role'] == "contributor") { print("selected");} print('>Contributeur</option> 
                <option value="author" '); if($this->helloadhrents_options['wordpress_users_def_role'] == "author") { print("selected");} print('>Auteur</option> 
                <option value="editor" '); if($this->helloadhrents_options['wordpress_users_def_role'] == "editor") { print("selected");} print('>Editeur</option> 
                <option value="administrator" '); if($this->helloadhrents_options['wordpress_users_def_role'] == "editor") { print("selected");} print('>Editeur</option>
            </select>'
        );        
    }

    public function wordpress_users_custom_role_callback() {
        printf(
            '<input class="regular-text" type="text" name="ha_option_name[wordpress_users_custom_role]" id="wordpress_users_custom_role" value="%s">',
            isset( $this->helloadhrents_options['wordpress_users_custom_role'] ) ? esc_attr( $this->helloadhrents_options['wordpress_users_custom_role']) : ''
        );        
    }

    public function wordpress_users_description_callback() {
        printf(
            '<input class="regular-text" type="text" name="ha_option_name[wordpress_users_description]" id="wordpress_users_description" value="%s">',
            isset( $this->helloadhrents_options['wordpress_users_description'] ) ? esc_attr( $this->helloadhrents_options['wordpress_users_description']) : ''
        );        
    }

    public function wordpress_users_send_resetmail_callback() {
        printf(
            '<input type="checkbox" name="ha_option_name[wordpress_users_send_resetmail]" id="wordpress_users_send_resetmail" %s>',
            isset( $this->helloadhrents_options['wordpress_users_send_resetmail'] ) ? 'checked' : ''
        );        
    }

    public function wordpress_users_resetmail_title_callback() {
        printf(
            '<input class="regular-text" type="text" name="ha_option_name[wordpress_users_resetmail_title]" id="wordpress_users_resetmail_title" value="%s">',
            isset( $this->helloadhrents_options['wordpress_users_resetmail_title'] ) && ($this->helloadhrents_options['wordpress_users_resetmail_title'] != '') ? esc_attr( $this->helloadhrents_options['wordpress_users_resetmail_title']) : 'Création de votre compte'
        );        
    }

    public function wordpress_users_resetmail_content_callback() {

        if (isset($this->helloadhrents_options['wordpress_users_resetmail_content']) && $this->helloadhrents_options['wordpress_users_resetmail_content'] != '') {
            $content = stripslashes(html_entity_decode( $this->helloadhrents_options['wordpress_users_resetmail_content']));
        } else {
            $content = stripslashes(html_entity_decode("<p><strong>Cher %prenom%</strong>,</p><p>Merci de votre adhésion à notre association. Votre compte utilisateur vient d'être créé sur notre site internet.</p><p>Votre identifiant est l'adresse %mail% que vous avez renseigné lors de votre inscription sur HelloAsso. Pour utiliser votre compte, il ne vous reste qu'à définir votre mot de passe en suivant <a href='https://www.mon-site.fr/wp-login.php?action=lostpassword'>ce lien [https://www.mon-site.fr/wp-login.php?action=lostpassword]</a>.</p><p>Cordialement,<br />
                Votre Webmaster</p>"));
        }

        wp_editor($content, 'wordpress_users_resetmail_content', array('textarea_name' => 'wordpress_users_resetmail_content', 'media_buttons' => false, 'textarea_rows' => 5));

    }

    public function custom_function_group_callback() {
        printf(
            '<input type="checkbox" class="invisible_checkbox options_group" name="ha_option_name[custom_function_group_option]" id="custom_function_group_option" %s value="true"><label for="custom_function_group_option" class="toggle" id="toggle_custom_function_group_option"></label>',
            isset( $this->helloadhrents_options['custom_function_group_option'] ) ? 'checked' : ''
        );
    }

    public function custom_function_callback() {
        printf(
            '<input class="regular-text" type="text" name="ha_option_name[custom_function]" id="custom_function" value="%s">',
            isset( $this->helloadhrents_options['custom_function'] ) ? esc_attr( $this->helloadhrents_options['custom_function']) : ''
        );        
    }

}
if ( is_admin() )
    $helloadhrents = new HelloAdhrents();

/* 
 * Retrieve this value with:
 */
$helloadhrents_options = get_option( 'ha_option_name' ); // Array of All Options





function do_settings_sections_custom( $page ) {
    global $wp_settings_sections, $wp_settings_fields;
 
    if ( ! isset( $wp_settings_sections[ $page ] ) ) {
        return;
    }
 
    foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
        if ( $section['title'] ) {
            echo "<h2>" . esc_attr($section['title']) . "</h2>\n";
        }
 
        if ( $section['callback'] ) {
            call_user_func( $section['callback'], $section );
        }
 
        if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
            continue;
        }


        global $wp_settings_fields;
     
        if ( ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
            return;
        }
     
        foreach ( (array) $wp_settings_fields[ $page ][ $section['id'] ] as $field ) {

            if ($field['args']['class'] == 'options_group') {
                echo ('<div class="options_group">' . esc_html($field['title']));
                call_user_func( $field['callback'], $field['args'] );
                echo "<div id='content_options_group'>";
            } else {
                echo '<div style="width:100%;"><div class="ha_option_label">' . wp_kses_post($field['title']) . '</div><div class="ha_option_input">';
                call_user_func( $field['callback'], $field['args'] );
                echo '</div></div>';
            }

            if ($field['args']['class'] == 'test_ha_after') {echo "<br>"; helladh_test_api_ha();}
            if ($field['args']['class'] == 'test_google_after') {echo "<br>"; helladh_test_api_google(); echo "</div></div>";}            
            if ($field['args']['class'] == 'test_mailchimp_after') {echo "<br>"; helladh_test_api_mailchimp(); echo "</div></div>";}            
            if ($field['args']['class'] == 'test_wordpress_users_after') {echo "<br>"; helladh_test_wordpress_users(); echo "</div></div>";}            
            if ($field['args']['class'] == 'test_custom_function_after') {echo "<br>"; helladh_test_custom_function(); echo "</div></div>";}            

        }

    }
}


?>