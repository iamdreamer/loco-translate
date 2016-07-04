<?php
/**
 * Provides a bridge between the full serializable array model and POSTDATA array.
 * 
 * Key differences between form data and the DOM are:
 * - form fields cannot express attributes
 * - form fields uses line breaks to separate multiple nodes
 */
class Loco_config_FormModel extends Loco_config_ArrayModel {


    /**
     * Export array data that matches the format used in postdata
     * @return Loco_mvc_PostParams
     */
    public function getPost(){
        $dom = $this->getDom();
        $root = $dom->documentElement;
        $post = new Loco_mvc_PostParams( array (
            'name' => $root->getAttribute('name'),
            'exclude' => array (
                'path' => '',
            ),
            'conf' => array(),
        ) );
        /* @var LocoConfigElement $domain */
        foreach( $this->query('domain',$root) as $domain ){
            $domainName = $domain->getAttribute('name');
            /* @var LocoConfigElement $project */
            foreach( $domain as $project ){
                $tree = array (
                    'name' => $project->getAttribute('name'),
                    'slug' => $project->getAttribute('slug'),
                    'domain' => $domainName,
                    'source' => array (
                        'path' => '',
                        'exclude' => array( 'path' => '' ),
                    ),
                    'target' => array (
                        'path' => '',
                        'exclude' => array( 'path' => '' ),
                    ),
                    'template' => array( 'path' => '' ),
                );
                $post['conf'][] = $this->collectPaths( $project, $tree );
            }
        }
        /* @var LocoConfigElement $paths */
        foreach( $this->query('exclude',$root) as $paths ){
            $post['exclude'] = $this->collectPaths( $paths, $post['exclude'] );
        }
        
        return $post;
    }



    private function collectPaths( LocoConfigElement $parent, array $branch ){
        $texts = array();
        foreach( $parent as $child ){
            $name = $child->nodeName;
            // all file types as "path" in form model
            if( 'file' === $name || 'directory' === $name ){
                $name = 'path';
            }
            if( isset($branch[$name]) ){
                // collect text if child is a <path> node
                if( 'path' === $name ){
                    $file = $this->evaluateFileElement($child);
                    $texts[] = $file->getRelativePath( $this->getDirectoryPath() );
                }
                // else must be simple key to next depth
                else {
                    $branch[$name] = $this->collectPaths( $child, $branch[$name] );
                }
            }
            // @codeCoverageIgnoreStart
            else {
                throw new Exception('Unexpected structure: '.$name.' not in '.json_encode($branch) );
            }
            // @codeCoverageIgnoreEnd
        }
        if( $texts ){
            $branch['path'] = implode("\n", $texts );
        }
        return $branch;
    }



    /**
     * Construct model from posted form data.
     * @return void
     */
    public function loadForm( Loco_mvc_PostParams $post ){
        // basic validation unlikely to fail when posted from UI
        $name = $post->name;
        if( ! $name ){
            throw new InvalidArgumentException('Bundle must have a name');
        }
        $confs = $post->conf;
        if( ! $confs || ! is_array($confs) ){
            throw new InvalidArgumentException('Bundle must have at least one definition');
        }
        // transform posted data into internal model:
        // deliberately not configuring bundle object at this point. simply converting data for storage.
        $dom = $this->getDom();
        $root = $dom->appendChild( $dom->createElement('bundle') );
        $root->setAttribute( 'name', $name );
        
        // bundle level excluded paths
        if( $nodes = array_intersect_key( $post->getArrayCopy(), array( 'exclude' => '' ) ) ) {
            $this->loadStruct( $root, $nodes );
        }
        
        // collect all projects grouped by domain
        $domains = array();
        foreach( $confs as $i => $conf ){
            if( ! empty($conf['removed']) ){
                continue;
            }
            if( empty($conf['domain']) ){
                throw new InvalidArgumentException( __('Text Domain cannot be empty','loco') );
            }
            $domains[ $conf['domain'] ][] = $project = $dom->createElement('project');
            // project attributes
            foreach( array('name','slug') as $attr ){
                if( isset($conf[$attr]) ){
                    $project->setAttribute( $attr, $conf[$attr] );
                }
            }
            // project children
            if( $nodes = array_intersect_key( $conf, array( 'source' => '', 'target' => '', 'template' => '' ) ) ) {
                $this->loadStruct( $project, $nodes );
            }
        }
        // add all domains and their projects 
        foreach( $domains as $name => $projects ){
            $parent = $root->appendChild( $dom->createElement('domain') );
            $parent->setAttribute( 'name', $name );
            /* @var $project LocoConfigElement */
            foreach( $projects as $project ){
                $parent->appendChild( $project );
            }
        }
    }


    
    /**
     * Recursively add array structure into model.
     * - Text nodes are split into one parent element per line.
     * - Elements added here cannot have attributes, but are not expected to as they came from form fields
     */
    private function loadStruct( LocoConfigElement $parent, array $nodes ){
        $dom = $this->getDom();
        foreach( $nodes as $name => $data ){
            if( is_string($data) ){
                // form model has multiline "path" nodes which we'll expand from non-empty lines
                // resolving empty paths to "." must be done elsewhere. here empty means ignore.
                foreach( preg_split('/\\R/', trim( $data,"\n\r"), -1, PREG_SPLIT_NO_EMPTY ) as $path ){
                    $ext = pathinfo( $path, PATHINFO_EXTENSION );
                    $child = $parent->appendChild( $dom->createElement( $ext ? 'file' : 'directory' ) );
                    $child->appendChild( $dom->createTextNode($path) );
                }
            }
            else if( ! is_array($data) ){
                throw new InvalidArgumentException('Invalid datatype');
            }
            else {
                $child = $parent->appendChild( $dom->createElement($name) );
                $this->loadStruct( $child, $data );
            }
        }
     }    
    
    
}
