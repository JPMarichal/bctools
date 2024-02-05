<?php
// Incluye wp-load.php para acceder a las funciones de WordPress.
require_once('../../../../wp-load.php');

// Incluye plugin.php para utilizar is_plugin_active().
require_once(ABSPATH . 'wp-admin/includes/plugin.php');

function bc_post_update_activar() {
    if (!is_plugin_active('bc_tools/bc-tools.php')) {
        die("El plugin BC Tools debe estar instalado y activo antes de ejecutar BC Post Update.");
    }

    $posts_por_lote = 50;

    $posts = get_posts([
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    ]);

    $total_posts = count($posts);
    $processed_posts = 0;

    foreach (array_chunk($posts, $posts_por_lote) as $lote) {
        foreach ($lote as $post) {
            $content = $post->post_content;
            $capitulos = find_scripture_references($content);

            wp_update_post([
                'ID'           => $post->ID,
                'post_content' => $content,
            ]);

            $tags_deleted = 0;
            $tags = wp_get_post_tags($post->ID);

            foreach ($tags as $tag) {
                if (in_array($tag->name, $capitulos)) {
                    wp_remove_object_terms($post->ID, $tag->term_id, 'post_tag');
                    $tags_deleted++;
                }
            }

            $processed_posts++;
        }
    }

    echo "Procesamiento terminado. Total de posts procesados: {$processed_posts}.\n";
}

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
        $bookAndChapter = $normalizedBookName . ' ' . $chapter;

        if (!in_array($bookAndChapter, $foundChapters)) {
            $foundChapters[] = $bookAndChapter;
        }
    }

    return $foundChapters;
}

function normalize_book_name($name) {
    $unwanted_array = [
        'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'Ñ' => 'N', 'ñ' => 'n',
    ];
    return strtr($name, $unwanted_array);
}

// Debido a que este script se ejecuta directamente, no es necesario un gancho de activación.
bc_post_update_activar();
