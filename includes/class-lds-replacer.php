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
        $pattern = '/\b(1 Nefi|2 Nefi|Jacob|Enós|Jarom|Omni|Mosíah|Alma|Helamán|3 Nefi|4 Nefi|Mormón|Éter|Moroni|Génesis|Éxodo|Levítico|Números|Deuteronomio|Josué|Jueces|Rut|1 Samuel|2 Samuel|1 Reyes|2 Reyes|1 Crónicas|2 Crónicas|Esdras|Nehemías|Ester|Job|Salmos|Proverbios|Eclesiastés|Cantares|Isaías|Jeremías|Lamentaciones|Ezequiel|Daniel|Oseas|Joel|Amós|Abdías|Jonás|Miqueas|Nahúm|Habacuc|Sofonías|Hageo|Zacarías|Malaquías|Mateo|Marcos|Lucas|Juan|Hechos|Romanos|1 Corintios|2 Corintios|Gálatas|Efesios|Filipenses|Colosenses|1 Tesalonicenses|2 Tesalonicenses|1 Timoteo|2 Timoteo|Tito|Filemón|Hebreos|Santiago|1 Pedro|2 Pedro|1 Juan|2 Juan|3 Juan|Judas|Apocalipsis|Moisés|Abraham|José Smith—Mateo|José Smith—Historia|Artículos de Fe|Doctrina y Convenios|Declaración Oficial)\s\d+(?::\d+(?:–\d+)?(?:,\d+(?:–\d+)?)*)?/iu';
    
        return preg_replace_callback($pattern, function($matches) {
            // Normalizar el nombre del libro para eliminar acentos y ajustar para "Éxodo" y "Éter"
            $normalizedBookName = $this->normalize_book_name($matches[1]);
            $book = $normalizedBookName; // El nombre del libro normalizado
            $chapter = isset($matches[2]) ? $matches[2] : ''; // Asegurar que el capítulo esté definido
    
            // Corrección clave: Asegurarse de incluir el número de capítulo en la URL
            $bookAndChapter = ($book . ' ' . $chapter);
    
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
  