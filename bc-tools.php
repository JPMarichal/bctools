<?php
/**
 * Plugin Name: BC Tools
 * Plugin URI:  https://biblicomentarios.com
 * Description: Un plugin para reemplazar referencias a las Escrituras con enlaces.
 * Version:     1.0
 * Author:      Juan Pablo Marichal Catalan
 * Author URI:  https://biblicomentarios.com
 */

// Prevenir acceso directo.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Incluir la clase.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-lds-replacer.php';

// Inicializar la clase.
function lds_tools_run() {
    $lds_replacer = new LDS_Replacer();
}
add_action( 'plugins_loaded', 'lds_tools_run' );

function lds_replacer_enqueue_styles() {
    // Usa plugins_url() para obtener la URL correcta del archivo CSS
    wp_enqueue_style('lds-replacer-styles', plugins_url('css/lds-replacer-styles.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'lds_replacer_enqueue_styles');
