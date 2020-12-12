<?php

namespace App\Service;

class Slugify
{
    /**
     * @param string|null $imput
     * @return string
     */
    public function generate(string $slug = null): string
    {   
        // Remplace les caractères spéciaux
        $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
        // Supprime les apostrophes et autres ponctuations
        $slug = preg_replace('/\p{P}+/u', '', $slug);
        // Supprime les tirets successifs
        $slug = preg_replace('#[^-\w]+#', '-', $slug);
        // Supprime les espaces en début et fin de chaînes
        $slug = trim($slug, '-');
        // Chaine en minuscule
        $slug = strtolower($slug);

        return $slug;
    }
}