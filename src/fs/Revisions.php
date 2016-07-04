<?php
/**
 * Manages revisions (backups) of a file.
 * Revision file names have form "<filename>-backup-<date>.<ext>~"
 */
class Loco_fs_Revisions implements Countable/*, IteratorAggregate*/ {
    
    /**
     * @var loco_fs_File
     */
    private $master;
    
    /**
     * Sortable list of backed up file paths (not including master)
     * @var array
     */
    private $paths;
    
    /**
     * Cached count of backups + 1
     * @var int
     */
    private $length;

    /**
     * Paths to delete when object removed from memory
     * @var array
     */
    private $trash = array();
    

    /**
     * Construct from master file (current version)
     */
    public function __construct( Loco_fs_File $file ){
        $this->master = $file;
    }
    
    
    
    public function __destruct(){
        foreach( $this->trash as $path ){
            self::_unlink_if_exists($path);
        }
    }
    


    /**
     * Check that file permissions allow a new backup to be created
     * @return bool
     */
    public function writable(){
        return $this->master->getParent()->writable();
    }


    /**
     * Create a new backup of current version
     * @return Loco_fs_File
     */
    public function create(){
        $vers = 0;
        $date = date('YmdHis');
        $ext = $this->master->extension();
        $base = $this->master->dirname().'/'.$this->master->filename();
        do {
            $path = sprintf( '%s-backup-%s%u.%s~', $base, $date, $vers++, $ext);
        }
        while ( 
            file_exists($path)
        );

        $copy = $this->master->copy( $path );
        
        // invalidate cache so next access reads disk
        $this->paths = null;
        $this->length = null;
        
        return $copy;
    }



    /**
     * Delete oldest backups until we have maximuim of $num_backups remaining
     * @return Loco_fs_Revisions
     */
    public function prune( $num_backups ){
        $paths = $this->getPaths();
        if( isset($paths[$num_backups]) ){
            foreach( array_slice( $paths, $num_backups ) as $path ){
                $this->unlinkLater($path);
            }
            $this->paths = array_slice( $paths, 0, $num_backups );
            $this->length = null;
        }
        return $this;
    }






    /**
     * @return array
     */
    public function getPaths(){
        if( is_null($this->paths) ){
            // build regex for matching backed up revisions of master
            $regex = preg_quote( $this->master->filename(), '/' ).'-backup-(\\d{14,})?';
            if( $ext = $this->master->extension() ){
                $regex .= preg_quote('.'.$ext,'/');
            }
            $regex = '/'.$regex.'~/';
            //
            $this->paths = array();
            $finder = new Loco_fs_FileFinder( $this->master->dirname() );
            /** @var $file Loco_fs_File */
            foreach( $finder as $file ){
                if( preg_match( $regex, $file->basename(), $r ) ){
                    $this->paths[] = $file->getPath();
                }
            }
            // time sort order descending
            rsort( $this->paths );
        }
        return $this->paths;
    }



    /**
     * Get number of backups plus master
     * @return int
     */    
    public function count(){
        if( ! $this->length ){
            $this->length = 1 + count( $this->getPaths() );
        }
        return $this->length;
    }



    /**
     * @internal
     */
    public static function _unlink_if_exists( $path ){
        $file = new Loco_fs_File($path);
        if( $file->exists() ){
            $file->unlink();
        }
    }
    

    /**
     * Delete file when object removed from memory.
     * Previously unlinked on shutdown, but doesn't work with WordPress file system abstraction
     * @return void
     */
    public function unlinkLater($path){
        // register_shutdown_function( array(__CLASS__,'_unlink_if_exists'), $path );
        $this->trash[] = $path;
    }
      
}