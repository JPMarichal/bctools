<?php

class BC_Tools_Uninstaller {
    public static function uninstall() {
        // Ejemplo de eliminación de una opción, ajusta según tus necesidades
        // delete_option('bc_tools_option_name');

        // Eliminar eventos cron programados relacionados con el plugin
        self::clear_scheduled_cron_events();

        // Ejemplo de eliminación de taxonomía, WordPress maneja automáticamente los términos
        // No es necesario un proceso adicional para eliminar taxonomías registradas por el plugin

        // Ejemplo de eliminación de tabla personalizada, ajusta el nombre de la tabla según tus necesidades
        // global $wpdb;
        // $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mi_tabla_personalizada");
    }

    protected static function clear_scheduled_cron_events() {
        $timestamp = wp_next_scheduled('bc_tools_process_posts_in_batches');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'bc_tools_process_posts_in_batches');
        }
    }
}

BC_Tools_Uninstaller::uninstall();
