<?php
// Incluye los archivos necesarios de WordPress para acceder a sus funciones.
require_once('../../../../wp-load.php');
require_once(ABSPATH.'wp-admin/includes/plugin.php');

/**
 * Inicia el proceso de actualización de posts y manejo de etiquetas.
 */
function bc_post_update_activar() {
    if (!is_plugin_active('bc_tools/bc-tools.php')) {
        echo "El plugin BC Tools debe estar instalado y activo antes de ejecutar BC Post Update.";
        return;
    }

    $posts_por_lote = 50;
    $offset = 0;
    $total_posts = 0;
    $inicio = microtime(true);

    do {
        $args = [
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => $posts_por_lote,
            'offset'         => $offset,
        ];

        $posts = get_posts($args);
        $numero_posts = count($posts);

        foreach ($posts as $post) {
            $content = $post->post_content;
            $capitulos = find_scripture_references($content);

            $updated = wp_update_post([
                'ID'           => $post->ID,
                'post_content' => $content,
            ], true);

            if (is_wp_error($updated)) {
                echo "Error al actualizar el post: {$post->post_title} - " . $updated->get_error_message() . "\n";
                continue;
            } else {
                echo "Post actualizado: {$post->post_title}\n";
            }

            wp_reset_postdata();

            $tags = wp_get_post_tags($post->ID, ['fields' => 'all']);
            foreach ($tags as $tag) {
                $tagNameNormalized = normalize_book_name($tag->name);
                foreach ($capitulos as $book => $chapters) {
                    if (in_array($tagNameNormalized, $chapters)) {
                        $result = wp_remove_object_terms($post->ID, $tag->term_id, 'post_tag');
                        if (is_wp_error($result)) {
                            echo "Error al eliminar etiqueta: {$tag->name} (ID: {$tag->term_id}) - " . $result->get_error_message() . "\n";
                        } else {
                            echo "Etiqueta eliminada: {$tag->name} (ID: {$tag->term_id})\n";
                        }
                        break; // Sale del bucle interno si la etiqueta se encuentra y se intenta eliminar.
                    }
                }
            }

            $total_posts++;
        }

        $offset += $posts_por_lote;
    } while ($numero_posts == $posts_por_lote);

    $fin = microtime(true);
    $tiempo_ejecucion = ($fin - $inicio)/60;
    echo "Procesamiento terminado. Total de posts procesados: $total_posts. Tiempo de ejecución: $tiempo_ejecucion minutos.\n";
}

/**
 * Encuentra referencias a capítulos de la Biblia en el contenido.
 */
function find_scripture_references($content) {
    $books_path = plugin_dir_path(__FILE__) . 'scripture_books.txt';
    $books = file($books_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($books === false) {
        return [];
    }

    $escapedBooks = array_map('preg_quote', $books, array_fill(0, count($books), '/'));
    $pattern = '/\b(' . implode('|', $escapedBooks) . ')\s(\d+)(?::\d+(?:–\d+)?(?:,\d+(?:–\d+)?)*)?/iu';

    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

    $foundChapters = [];
    foreach ($matches as $match) {
        $normalizedBookName = normalize_book_name($match[1]);
        $chapter = $match[2];
        $bookAndChapter = "{$normalizedBookName} {$chapter}";

        if (!isset($foundChapters[$normalizedBookName])) {
            $foundChapters[$normalizedBookName] = [];
        }

        if (!in_array($bookAndChapter, $foundChapters[$normalizedBookName])) {
            $foundChapters[$normalizedBookName][] = $bookAndChapter;
        }
    }

    return $foundChapters;
}

/**
 * Normaliza el nombre del libro o etiqueta para manejar uniformemente los acentos.
 */
function normalize_book_name($name) {
    $unwanted_array = [
        'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'Ñ' => 'N', 'ñ' => 'n',
    ];
    return strtr($name, $unwanted_array);
}

bc_post_update_activar();
