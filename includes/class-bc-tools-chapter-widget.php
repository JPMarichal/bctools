<?php

class BC_Tools_Chapters_Widget extends WP_Widget {

    public function __construct() {
        // Constructor del widget.
        parent::__construct(
            'bc_tools_chapters_widget', // Base ID
            'Capítulos de las Escrituras', // Nombre
            array( 'description' => 'Muestra una lista de capítulos de las Escrituras asociados a la publicación.' ) // Args
        );
    }

    public function widget($args, $instance) {
        // Contenido del widget.
        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        // Recuperar y mostrar los capítulos de la taxonomía "Capítulos".
        $terms = get_terms(array(
            'taxonomy' => 'capitulos',
            'hide_empty' => true,
        ));

        if (!empty($terms) && !is_wp_error($terms)) {
            echo '<ul>';
            foreach ($terms as $term) {
                echo '<li>' . esc_html($term->name) . '</li>';
            }
            echo '</ul>';
        }

        echo $args['after_widget'];
    }

    public function form($instance) {
        // Formulario en el área de administración para el título del widget.
        $title = !empty($instance['title']) ? $instance['title'] : esc_html__('Capítulos de las Escrituras', 'text_domain');
        ?>
        <p>
        <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_attr_e('Título:', 'text_domain'); ?></label> 
        <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <?php 
    }

    public function update($new_instance, $old_instance) {
        // Guardar las opciones del widget.
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';

        return $instance;
    }
}

// Registrar y cargar el widget
function bc_tools_load_widget() {
    register_widget('BC_Tools_Chapters_Widget');
}
add_action('widgets_init', 'bc_tools_load_widget');
