<?php

namespace Config;

use Config\Services;

class AutoRoutes
{
    public static function loadDynamicRoutes()
    {
        $routes = Services::routes();

        $db = \Config\Database::connect();

        $sql = "SELECT c.name AS url, c.name AS controller, d.name AS method

                FROM privilege a
                JOIN `group` b ON a.group_id = b.group_id
                JOIN page c ON a.page_id = c.page_id
                JOIN action d ON a.action_id = d.action_id
                WHERE c.name IS NOT NULL AND d.name IS NOT NULL";

        $query = $db->query($sql);

        if ($query->getNumRows() > 0) 
        {
            
            foreach ($query->getResult() as $row) 
            {
                $url = strtolower(trim($row->url));
                $controller = ucfirst(trim($row->controller));
                $method = trim($row->method);

                $routes->get($url, $controller . '::' . $method, ['filter' => 'permission']);
            }
        }
    }
}