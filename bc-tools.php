<?php
/**
 * Plugin Name: BC Tools
 * Plugin URI:  https://biblicomentarios.com
 * Description: Un plugin para reemplazar referencias a las Escrituras con enlaces.
 * Version:     1.0.1
 * Author:      Juan Pablo Marichal Catalan
 * Author URI:  https://biblicomentarios.com
 */

// Prevenir acceso directo.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Incluir la clase.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-lds-replacer.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-bc-tools-taxonomy-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-bc-tools-chapter-widget.php';

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

function bc_tools_taxonomy_handler_run() {
    new BC_Tools_Taxonomy_Handler();
}
add_action( 'plugins_loaded', 'bc_tools_taxonomy_handler_run' );

function register_taxonomy_capitulos() {
    $labels = array(
        'name'                       => _x('Capítulos', 'Taxonomy General Name', 'text_domain'),
        'singular_name'              => _x('Capítulo', 'Taxonomy Singular Name', 'text_domain'),
        'menu_name'                  => __('Capítulos', 'text_domain'),
        'all_items'                  => __('Todos los Capítulos', 'text_domain'),
        'parent_item'                => null,  // null para taxonomías no jerárquicas como tags
        'parent_item_colon'          => null,  // null para taxonomías no jerárquicas como tags
        'new_item_name'              => __('Nombre del Nuevo Capítulo', 'text_domain'),
        'add_new_item'               => __('Añadir Nuevo Capítulo', 'text_domain'),
        'edit_item'                  => __('Editar Capítulo', 'text_domain'),
        'update_item'                => __('Actualizar Capítulo', 'text_domain'),
        'view_item'                  => __('Ver Capítulo', 'text_domain'),
        'separate_items_with_commas' => __('Separe los capítulos con comas', 'text_domain'),
        'add_or_remove_items'        => __('Añadir o quitar capítulos', 'text_domain'),
        'choose_from_most_used'      => __('Elegir entre los más usados', 'text_domain'),
        'popular_items'              => __('Capítulos Populares', 'text_domain'),
        'search_items'               => __('Buscar Capítulos', 'text_domain'),
        'not_found'                  => __('No Encontrado', 'text_domain'),
        'no_terms'                   => __('Sin Capítulos', 'text_domain'),
        'items_list'                 => __('Lista de Capítulos', 'text_domain'),
        'items_list_navigation'      => __('Navegación de Lista de Capítulos', 'text_domain'),
    );
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => false, // False para taxonomías como tags
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
    );
    register_taxonomy('capitulos', array('post'), $args);
}
add_action('init', 'register_taxonomy_capitulos', 0);

register_activation_hook(__FILE__, 'bc_tools_schedule_initial_update');

function bc_tools_schedule_initial_update() {
    if (!wp_next_scheduled('bc_tools_process_posts_in_batches')) {
        wp_schedule_single_event(time(), 'bc_tools_process_posts_in_batches');
    }
}
