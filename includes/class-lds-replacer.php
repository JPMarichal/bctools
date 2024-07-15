<?php
/**
 * Clase para identificar y reemplazar referencias a capítulos de las Escrituras con enlaces,
 * incluyendo un ancla al primer versículo del rango si se especifica, y ajustando para mantener el texto del rango.
 */

class LDS_Replacer {

    /**
     * Constructor de la clase.
     */
    public function __construct() {
        add_filter('the_content', array($this, 'replace_scripture_references'));
    }

    /**
     * Busca y reemplaza las referencias de las Escrituras en el contenido.
     *
     * @param string $content Contenido en el que buscar las referencias.
     * @return string Contenido con las referencias reemplazadas por enlaces.
     */
    public function replace_scripture_references($content) {
        // Cargar los nombres de los libros desde el archivo
        $books = file(plugin_dir_path(__FILE__) . '../data/scripture_books.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($books === false) {
            // Si no se puede cargar el archivo, retorna el contenido sin modificaciones
            return $content;
        }
        
        // Escapar los nombres de los libros para su uso en la expresión regular
        $escapedBooks = array_map(function($book) {
            return preg_quote($book, '/');
        }, $books);
    
        // Construir el patrón usando los nombres de los libros cargados
        $pattern = '/\b(' . implode('|', $escapedBooks) . ')\s\d+(?::\d+(?:–\d+)?(?:,\d+(?:–\d+)?)*)?/iu';
    
        return preg_replace_callback($pattern, function($matches) {
            // Normalizar el nombre del libro para eliminar acentos y ajustar para "Éxodo" y "Éter"
            $normalizedBookName = $this->normalize_book_name($matches[1]);
            $book = $normalizedBookName; // El nombre del libro normalizado
            $chapter = isset($matches[2]) ? $matches[2] : ''; // Asegurar que el capítulo esté definido
    
            // Corrección clave: Incluir el número de capítulo en la URL
            $bookAndChapter = $book . ' ' . $chapter;
    
            // Capturar correctamente el primer versículo y rangos, si están presentes
            $verseMatch = '';
            if (preg_match('/\d+:\d+(?:–\d+)?/', $matches[0], $verseMatches)) {
                $verseMatch = str_replace(':', '#', $verseMatches[0]);
            }
    
            $displayText = $matches[0]; // Mantener el texto completo de la referencia para el display
    
            // Construir la URL completa, incluyendo el capítulo y el versículo
            $url = "https://biblicomentarios.com/capitulo-escrituras/?capitulo=" . urlencode($bookAndChapter) . $verseMatch;
    
            return "<a href='$url' class='chapterLink' target='_chapter'>$displayText</a>";
        }, $content);
    }   
    
    private function normalize_book_name($name) {
        $name = str_replace(['Éxodo', 'Éter'], ['Exodo', 'Eter'], $name);
    
        $unwanted_array = [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Ñ' => 'N', 'ñ' => 'n',
            // Considera agregar cualquier otro carácter especial que necesites manejar
        ];
        return strtr($name, $unwanted_array);
    }
    
    
}
  