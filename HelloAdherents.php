<?php
/**
 * Plugin Name: HelloAdhérents
 * Description: Retrieve data from HelloAsso and use it to automatically update mailing lists, and more.
 * Version: 1.2.4
 * Author: DrCode
 * Author URI: https://medg.fr
 * License: GPL2
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/functions/settings-page.php';
require_once __DIR__ . '/functions/output/helloasso_api.php';
require_once __DIR__ . '/functions/output/google_api.php';
require_once __DIR__ . '/functions/output/mailchimp_api.php';
require_once __DIR__ . '/functions/output/wordpress_users.php';
require_once __DIR__ . '/functions/output/custom_function.php';

add_action('admin_init', 'helladh_css');
function helladh_css(){
  wp_register_style('helladh_css', plugins_url('style.css',__FILE__));
  wp_enqueue_style ('helladh_css');
}

use Firebase\JWT\JWT; 


/* FONCTION PRINCIPALE */

function helladh_run() {

    $helloadhrents_options = get_option( 'ha_option_name' ); // Array of All Options

    // PARTIE HELLOASSO
    $info_adherent = helladh_helloasso_api();


    // PARTIE GOOGLE GROUPS
    if ($helloadhrents_options['google_group_option'] == true) {
        helladh_google_api($info_adherent);
    }

    // PARTIE MAILCHIMP
    if ($helloadhrents_options['mailchimp_group_option'] == true) {
        helladh_mailchimp_api($info_adherent);
    }

    // PARTIE WORDPRESS USERS
        
    if ($helloadhrents_options['wordpress_users_group_option'] == true) {
        helladh_wordpress_users($info_adherent);
    }

    // PARTIE CUSTOM FUNCTION
        
    if ($helloadhrents_options['custom_function_group_option'] == true) {
        $function = $helloadhrents_options['custom_function'];
        $function($info_adherent);
    }
}


/* ENREGISTREMENT DU PROCESSUS DANS LE FICHIER LOG.txt */

function helladh_write_log($message) { 
    if(is_array($message)) { 
        $message = json_encode($message); 
    } 
    $path = plugin_dir_path(__FILE__) . 'log_' . current_time('Y-m') . '.txt';
    $file = fopen($path,"a"); 
    fwrite($file, "\n" . current_time('Y-m-d H:i:s') . " :: " . $message . "\n"); 
    fclose($file); 

    // Suppression des fichiers log datant de plus d'un mois
    $txt_files = glob(plugin_dir_path(__FILE__) . '*.txt');
    $actualmonth = current_time('Y-m');
    $lastmonth = date("Y-m", mktime(0, 0, 0, date("m")-1, date("d"), date("Y")));

    foreach ($txt_files as $path) {
        preg_match('/log_(.*).txt/', $path, $output_arr);
        if ($output_arr[1] !== $actualmonth && $output_arr[1] !== $lastmonth) {
            wp_delete_file($path);        
        }
    }
}



/* CRON JOB */

add_action('init', function() {

    // Récupère l'intervalle défini par l'utilisateur
    
    add_filter( 'cron_schedules', function ( $schedules ) {
        $helloadhrents_options = get_option( 'ha_option_name' ); // Array of All Options
        if ($helloadhrents_options['cj_custom_intervals'] == 0) {
            $user_cron_freq = $helloadhrents_options['cj_wp_intervals'];
        } else {
            $user_cron_freq = $helloadhrents_options['cj_custom_intervals'];
        }

       $schedules['user_defined'] = array(
           'interval' => $user_cron_freq,
           'display' => __( 'Défini par l\'utilisateur' )
       );
       return $schedules;
    } );

    // Création de l'action hook
    add_action( 'hello_adh_hook', 'helladh_run' );

    // Planification du hook si non-existant
    if (! wp_next_scheduled ( 'hello_adh_hook' )) {
        wp_schedule_event( time(), 'user_defined', 'hello_adh_hook' );
    }
});

// Si l'intervalle a été modifié : supprime l'ancien hook et planifie le nouveau avec intervalle mis à jour

function helladh_update_cron_freq ($new_value, $old_value) {
    if ( $new_value['cj_wp_intervals'] !== $old_value['cj_wp_intervals'] || $new_value['cj_custom_intervals'] !== $old_value['cj_custom_intervals'] ) {
        wp_clear_scheduled_hook( 'hello_adh_hook' );
        wp_schedule_event( time(), 'user_defined', 'hello_adh_hook' );
    }
    return $new_value;
}
add_filter( 'pre_update_option_ha_option_name', 'helladh_update_cron_freq', 10, 2 );



/* TRANSFORMER DES CUSTOM FIELDS EN NOM DE VARIABLE PROPRE */

function helladh_clean_string($string) {
   $string = str_replace(' ', '_', $string);
   return $string = preg_replace('/[^A-Za-z0-9\_]/', '', $string);
}


/* CREATION D'UN MDP RANDOM */

function helladh_randomPassword() {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array();
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}


?>