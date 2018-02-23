<?php

use Pagekit\Application;


return [
    'name' => 'spqr/sitemap',
    'type' => 'extension',
    'main' => function (Application $app) {
    },
    
    'autoload' => [
        'Spqr\\Sitemap\\' => 'src',
    ],
    
    'nodes' => [],
    
    'routes' => [
        '/sitemap' => [
            'name'       => '@sitemap',
            'controller' => [
                'Spqr\\Sitemap\\Controller\\SitemapController',
            ],
        ],
    ],
    
    'menu' => [
        'sitemap'           => [
            'label'  => 'Sitemap',
            'url'    => '@sitemap',
            'active' => '@sitemap(/*)?',
            'icon'   => 'spqr/sitemap:icon.svg',
            'access' => 'sitemap: manage settings',
        ],
        'sitemap: settings' => [
            'parent' => 'sitemap',
            'label'  => 'Settings',
            'url'    => '@sitemap/settings',
            'access' => 'sitemap: manage settings',
        ],
    ],
    
    'permissions' => [
        'sitemap: manage settings' => [
            'title' => 'Manage settings',
        ],
    ],
    
    'settings' => '@sitemap/settings',
    
    'resources' => [
        'spqr/sitemap:' => '',
    ],
    
    'config' => [
        'frequency'      => 'weekly',
        'filename'       => 'sitemap.xml',
        'verifyssl'      => true,
        'allowredirects' => true,
        'debug'          => false,
        'excluded'       => [],
    ],
    
    'events' => [],
];