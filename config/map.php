<?php

return [
    /*
    | Centro padrão do mapa admin (ex.: mapa de alunos).
    | Araras/SP: 22°21'25"S 47°23'03"W
    */
    'default_latitude' => (float) env('MAP_DEFAULT_LATITUDE', -22.356944),
    'default_longitude' => (float) env('MAP_DEFAULT_LONGITUDE', -47.384167),
    'default_city' => env('MAP_DEFAULT_CITY', 'Araras'),
    'default_uf' => env('MAP_DEFAULT_UF', 'SP'),
    'default_zoom' => (int) env('MAP_DEFAULT_ZOOM', 13),
];
