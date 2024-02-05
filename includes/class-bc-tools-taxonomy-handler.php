<?php

class BC_Tools_Taxonomy_Handler {

    public function __construct() {
        add_action('save_post', array($this, 'update_post_taxonomies'), 10, 2);
    }

    public function update_post_taxonomies($post_id, $post) {
        // Verificar si el guardado es una revisión o un auto guardado
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }

        // Asegurarse de que se trabaje solo con posts (o adaptar para incluir CPTs)
        if ($post->post_type !== 'post') {
            return;
        }

        // Eliminar términos existentes de la taxonomía 'capitulos' para esta publicación
        wp_delete_object_term_relationships($post_id, 'capitulos');

        // Encontrar referencias a capítulos en el contenido del post
        $found_chapters = $this->find_scripture_references($post->post_content);

        // Si se encuentran capítulos, agregarlos a la taxonomía
        if (!empty($found_chapters)) {
            foreach ($found_chapters as $chapter) {
                wp_set_post_terms($post_id, $chapter, 'capitulos', true);
            }
        }
    }

    private function find_scripture_references($content) {
        $books = file(plugin_dir_path(__FILE__) . '../data/scripture_books.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($books === false) {
            return [];
        }
        
        $escapedBooks = array_map('preg_quote', $books, array_fill(0, count($books), '/'));
        $pattern = '/\b(' . implode('|', $escapedBooks) . ')\s(\d+)(?::\d+(?:–\d+)?(?:,\d+(?:–\d+)?)*)?/iu';
        
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
    
        $foundChapters = [];
        foreach ($matches as $match) {
            $normalizedBookName = $this->normalize_book_name($match[1]);
            $chapter = $match[2];
            $bookAndChapter = $normalizedBookName . ' ' . $chapter;
    
            if (!in_array($bookAndChapter, $foundChapters)) {
                $foundChapters[] = $bookAndChapter;
            }
        }
    
        return $foundChapters;
    }
    
    private function normalize_book_name($name) {
        $unwanted_array = [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Ñ' => 'N', 'ñ' => 'n',
        ];
        return strtr($name, $unwanted_array);
    }
}

add_action('bc_tools_process_posts_in_batches', 'bc_tools_process_post_update_in_batches');

function bc_tools_process_post_update_in_batches() {
    $batch_size = 20; // Define cuántas publicaciones procesar a la vez.
    $args = array(
        'post_type' => 'post', // Ajusta según los tipos de post que necesites procesar.
        'post_status' => 'publish',
        'numberposts' => $batch_size,
        'meta_query' => array(
            array(
                'key' => '_bc_tools_processed',
                'compare' => 'NOT EXISTS', // Solo selecciona posts que aún no han sido procesados.
            ),
        ),
    );

    $posts = get_posts($args);

    foreach ($posts as $post) {
        // Aquí llamarías a la lógica para extraer capítulos y actualizar la taxonomía.
        // Por ejemplo: $handler->update_post_taxonomies($post->ID, $post);
        // Marca el post como procesado.
        update_post_meta($post->ID, '_bc_tools_processed', 'yes');
    }

    // Si se procesaron menos posts que el tamaño del lote, todos los posts han sido procesados.
    if (count($posts) < $batch_size) {
        return;
    }

    // De lo contrario, programa el siguiente lote.
    wp_schedule_single_event(time() + 60, 'bc_tools_process_posts_in_batches'); // Espera 60 segundos antes del próximo lote.
}
