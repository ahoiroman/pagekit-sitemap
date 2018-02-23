<?php

use Doctrine\DBAL\Schema\Comparator;

return [
    
    /*
     * Installation hook
     *
     */
    'install'   => function ($app) {
        // Workaround: As extension got a new name, we need to migrate the config in the install routine
        if (!empty($app['config']->get('sitemap')->toArray())
            && empty($app['config']->get('spqr/sitemap')->toArray())
        ) {
            $app['config']->set('spqr/sitemap',
                $app->config('sitemap')->toArray());
        }
    },
    
    /*
     * Enable hook
     *
     */
    'enable'    => function ($app) {
    },
    
    /*
     * Uninstall hook
     *
     */
    'uninstall' => function ($app) {
        
        // remove the config
        $app['config']->remove('spqr/sitemap');
    },
    
    /*
     * Runs all updates that are newer than the current version.
     *
     */
    'updates'   => [],

];