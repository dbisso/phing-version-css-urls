<?php

require_once 'phing/filters/BaseFilterReader.php';
include_once 'phing/filters/ChainableReader.php';
include_once 'phing/types/RegularExpression.php';

/**
 * Finds urls in CSS files and appends them with a query var representing the last modification time.
 *
 * @author    Dan Bissonnet <dan@danisadesigner.com>
 */
class VersionCSSURLs extends BaseFilterReader implements ChainableReader {
	private $filepath;

    function read($len = null) {
        $buffer = $this->in->read($len);
        $this->originaldir = dirname($this->in->getResource());
        if($buffer === -1) {
            return -1;
        }
		try {
			$this->log('Versioning urls in ' . $this->in->getResource());
        	$token = 'ver=';
			$buffer = preg_replace_callback('|url\(([^\)]+\?' . preg_quote($token) . '[^\)]+)\)|', array($this, 'add_version'), $buffer);
        } catch (Exception $e) {
            // perhaps mismatch in params (e.g. no replace or pattern specified)
            $this->log("Error performing regexp replace: " . $e->getMessage(), Project::MSG_WARN);
        }
        
        return $buffer;
    }

    /**
     * Callback for preg_replace_callback(). Appends the version string.
     * 
     * Retrieves the filename and checks whether it exists and then appends
     * the ctime as a query var.
     * 
     * @param Array $match
     */
    private function add_version($match) {
		
		// Strip off any hash or query vars.
		// TODO: reappend the hash and query vars.
		$originalPath = preg_replace('|([\?#].*)$|', '', trim($match[1],"' "));
		$path = $this->originaldir . '/' . $originalPath;

		if(file_exists($path)){
			$attribute = preg_replace("|url\(([\'\"]*)([^\)\'\"]+)([\'\"]*)\)|", 'url($1' . $originalPath . '?ver=' . filectime($path) . '$3)' , $match[0]);
			$this->log($attribute);

			return $attribute;
		} else {
			throw new BuildException("Resource not found: " . $path, 1);
		}

    	return $match[0];
    }

    /**
     * Creates a new VersionCSSURLs filter using the passed in
     * Reader for instantiation.
     * 
     * @param Reader $reader A Reader object providing the underlying stream.
     *               Must not be <code>null</code>.
     * 
     * @return VersionCSSURLs A new filter based on this configuration, but filtering
     *         the specified reader
     */
    function chain(Reader $reader) {
        $newFilter = new VersionCSSURLs($reader);
        $newFilter->setProject($this->getProject());
        return $newFilter;
    }
   

}