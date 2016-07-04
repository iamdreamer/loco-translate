<?php
/**
 * The "WordPress Core" translations bundle
 */
class Loco_package_Core extends Loco_package_Bundle {

    /**
     * {@inheritdoc}
     */
    public function getSystemTargets(){
        return array (
            rtrim( loco_constant('WP_LANG_DIR'), '/' ),
            rtrim( loco_constant('LOCO_LANG_DIR'), '/' ).'/core',
        );
    }


    /**
     * {@inheritdoc}
     */
    public function getHeaderInfo(){
        return new Loco_package_Header( array (
            'TextDomain' => 'default',
        ) );
    }


    /**
     * {@inheritdoc}
     */
    public function getMetaTranslatable(){
        return array();
    }


    /**
     * {@inheritdoc}
     */
    public function getType(){
        return 'Core';
    }


    /**
     * {@inheritdoc}
     * Core bundle doesn't need a handle, there is only one.
     */
    public function getId(){
        return 'core';
    }


    /**
     * {@inheritdoc}
     * Core bundle is always configured
     */
    public function isConfigured(){
        $saved = parent::isConfigured() or $saved = 'internal';
        return $saved;
    }
    

    
    /**
     * Manually define the core WordPress translations as a single bundle
     * Projects are those included in standard WordPress downloads: [default], "admin", "admin-network" and "continents-cities"
     * @return Loco_package_Core
     */
    public static function create(){
        
        $rootDir = loco_constant('ABSPATH');
        $langDir = loco_constant('WP_LANG_DIR');
        
        $bundle = new Loco_package_Core('core', 'WordPress Core');
        $bundle->setDirectoryPath( $rootDir );
        
        // Core config may be saved in DB, but not supporting bundled XML
        if( $bundle->configureDb() ){
            return $bundle;
        }
        
        // front end, admin and network admin packages are all part of the "default" domain
        $domain = new Loco_package_TextDomain('default');
        $domain->setCanonical( true );
        // front end subset, has empty name in WP
        $project = $domain->createProject('Development');
        $project->setSlug('')
                ->setPot( new Loco_fs_File($langDir.'/wordpress.pot') )
                ->addSourceDirectory( $rootDir)
                ->excludeSourcePath( $rootDir.'/wp-admin')
                ->excludeSourcePath( $rootDir.'/wp-content')
                ->excludeSourcePath( $rootDir.'/wp-includes/class-pop3.php')
        ;
        // "Administration" project (admin subset)
        $project = $domain->createProject('Administration');
        $project->setSlug('admin')
                ->setPot( new Loco_fs_File($langDir.'/admin.pot') )
                ->addSourceDirectory( $rootDir.'/wp-admin' )
                ->excludeSourcePath( $rootDir.'/wp-admin/js')
                ->excludeSourcePath( $rootDir.'/wp-admin/css')
                ->excludeSourcePath( $rootDir.'/wp-admin/network')
                ->excludeSourcePath( $rootDir.'/wp-admin/network.php')
                ->excludeSourcePath( $rootDir.'/wp-admin/includes/continents-cities.php')
        ;
        // "Network Admin" package (admin-network subset)
        $project = $domain->createProject('Network Admin');
        $project->setSlug('admin-network')
                ->setPot( new Loco_fs_File($langDir.'/admin-network.pot') )
                ->addSourceDirectory( $rootDir.'/wp-admin/network' )
                ->addSourceFile( $rootDir.'/wp-admin/network.php' )
        ;
        
        // end of "default" domain projects
        $bundle->addDomain( $domain );


        // Continents & Cities is its own text domain)
        $domain = new Loco_package_TextDomain('continents-cities');
        $project = $domain->createProject('Continents & Cities');
        $project->setPot( new Loco_fs_File( $langDir.'/continents-cities.pot') )
                ->addSourceFile( $rootDir.'/wp-admin/includes/continents-cities.php' )
        ;
        $bundle->addDomain( $domain );
        
        return $bundle;
    }     
    
    
    
    
}