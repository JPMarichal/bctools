<?php

class BC_Tools_Uninstaller {
    public static function uninstall() {
        // Ejemplo de eliminación de una opción, ajusta según tus necesidades
      //  delete_option('bc_tools_option_name');

        // Ejemplo de eliminación de taxonomía, WordPress maneja automáticamente los términos
        // No es necesario un proceso adicional para eliminar taxonomías registradas por el plugin

        // Ejemplo de eliminación de tabla personalizada, ajusta el nombre de la tabla según tus necesidades
       // global $wpdb;
      //  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mi_tabla_personalizada");
    }
}

BC_Tools_Uninstaller::uninstall();

