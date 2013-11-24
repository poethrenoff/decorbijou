<?php
    /**
     * Пользовательские правила маршрутизации
     */
    $routes = array(
        // Путь к каталогу
        '/catalogue/@name' => array(
            'controller' => 'catalogue',
            'name' => '\w+',
        ),
        
        // Путь к товару
        '/catalogue/@name/@id' => array(
            'controller' => 'catalogue',
            'name' => '\w+',
            'action' => 'item',
        ),
        
        // Путь к каталогу по тегу
        '/catalogue/tag/@tag' => array(
            'controller' => 'catalogue',
            'tag' => '.+',
        ),
   );
