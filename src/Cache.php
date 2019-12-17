<?php

namespace D3turnes\Helpers;

/**
 * Cache
 *
 * http://github.com/d3turnes/cache
 *
 * A simple PHP class for caching data in the filesystem.
 *
 * License
 *   This software is released under the MIT License, see LICENSE.txt.
 *
 * Use see README.md
 *
 * @package Cache
 * @author  d3turnes <d3turnes@gmail.com>
 * @version 1.0.0
 */

class Cache {
	
	/**
     * The root cache directory.
     * @var string
     */
	private $path = 'cache';
	
	/**
     * The time in seconds that an object is stored in a caching. Default 3600 seconds
     * @var number
     */
	private $ttl = 3600;

	/**
     * text prefix define a unique word for the cahe
	 * @var string
	 */
	private $prefix = '';
	
	/**
     * Creates a Cache object
     *
     * @param array $options
     */
	public function __construct(array $options = []) {
		
		/*
		$properties = ['path', 'ttl', 'prefix'];
		foreach ($properties as $name) {
			if ( isset($options[$name]) ) {
				$this->{$name} = $options[$name];
			}
		}*/
		
		foreach ($options as $key => $value)
			$this->{$key} = $value;

		if ( empty($this->prefix) )
			throw new \InvalidArgumentException("the attribute \"prefix\" cannot be empty");
	}
	
	/**
	 * determine if an item exists in the cache. 
	 * return bool false if the value is not exists or true otherwise
	 */
	public function has($key) {
		$file = $this->getFileName($key);
		
		return file_exists($file) ? true : false;
		
		/*
		if (!is_file($file) || !is_readable($file)) {
            return false;
        }
		
		return true;
		*/
	}
	
	/**
     * Fetches an entry from the cache or store value into cache if no exists.
     *
     * @param string $key
	 * @param string|null|clousure() $default
	 * return false if not file exists or data was expired|data|closure()
     */
	 
	public function get($key, $default = null) {

		if ( ($data = $this->read($key)) == false ) {

			if (is_null($default)) return false;
			
			// store new value
			$value = is_callable($default) ? $default() : $default; 
			$this->put($key, $value, $this->ttl);
			return $value;
		}
		
		if ( time() > $data[0] && $data[0] !== -1) {
			
			if (is_null($default)) return false;
			
			// store the new value for expired time
			$value = is_callable($default) ? $default() : $default;
			$this->put($key, $value, $this->ttl);
			return $value;
				
		}
		
		// return from cache
		return $data[1];	
		
	}
	
	/**
     * Puts data into the cache.
     *
     * @param string $key
     * @param mixed  $value
     * @param int|null $lifetime if is null take ttl value otherwise take lifetime
     *
     * @return bool
     */
    public function put($key, $value, $lifetime = null) {

		$dir = $this->getDirectory($key);
		if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                return false;
            }
        }
		
		$lifetime = ( $lifetime == null ? $this->ttl : $lifetime );
		if ($lifetime > 0) $lifetime+=time();
		$data = serialize(array($lifetime, $value));

		$file = $this->getFileName($key);
		$result     = file_put_contents($file, $data, LOCK_EX);
        return ($result === false ? false : true);
    }
	
	/**
     * Calls $callback if $key does not exists or is expired.
     * Also returns latest data associated with $key.
     * This is basically a shortcut, turns this:
     * <code>
     * if($cache->isExpired($key)) {
     *     $cache->put($key, $newdata, 10);
     * }
     *
     * $data = $cache->retrieve(key);
     * </code>
     *
     * to this:
     *
     * <code>
     * $data = $cache->remember($key, function () {
     *    return $newdata;
     * }, 10);
     * </code>
     *
     * @param $key
     * @param $callback Callback called when data needs to be refreshed. Should return data to be cached.
     * @param int $lifetime Cache time. Defaults to 3600
     * @return mixed|null Data currently stored under key
     * @throws \Exception if the file cannot be saved
     */
	public function remember($key, $lifetime, $callback) {
		$data = $this->read($key);
		if (!$data || time() > $data[0] && $data[0] !== -1) {
			$newdata = $callback();
			$this->put($key, $newdata, $lifetime);
			return $newdata; 						// return $newdata
		}
		return $data[1];							// return cache
	}
	
	/**
	 *	The forever method may be used to store an item in the cache permanently. Since these items will not expire, they must be manually removed from the cache using the forget method
	 *	@param string $key
	 *	@param string|object|clousure
	 *	return $value
	 */
	public function forever($key, $value) {
		
		$value = ( is_callable($value) ? $value() : $value );
		
		return ( $this->put($key, $value, -1) ? $value : false );
		
		
	}
	
	/**
	 *	@param $key
	 *	@return bool True if bad file or time expired, False otherwise
	 */
	public function isExpired($key) {
		
		$data = $this->read($key);	// $data[0] = time $data[1] = data
		
		return ( !$data ? true : ( time() > $data[0] && $data[0] !== -1 ? true : false ) );
	}
	
	/**
	 *	Retrieve & Delete
	 *	return false If $key does not exists or retrieve an item from the cache 
	 *	and then delete the item
	 *	@param string $key
	 *	return bool|mixed	
	 */
	public function pull($key) {
		
		if (!$this->has($key))
			return false;
		
		if ( ($data = $this->get($key)) == false ) {
			return false;
		}
		
		$this->forget($key);
		
		return $data[1];
	}
	
	/**
	 *	remove items from the cache
	 * 	@param string $key
	 */
	public function forget($key) {
		
		$file = $this->getFileName($key);
		if (file_exists($file)) unlink($file);
	}
	
	/**
	 * delete all files cache
	 */
	public function flush() {
		
		$this->delete_directory( $this->getCacheDirectory() );
		
	}
	
	/**
	 *  print default options
	 */
	public function print() {
		return sprintf("path: %s, ttl: %s, prefix: %s", $this->path, $this->ttl, $this->prefix);
	}
	
	
	//------------------------------------------------
    // PRIVATE METHODS
    //------------------------------------------------
	
	private function read($key) {
		
		$key = $this->setPrefix($key);

		$file = $this->getFileName($key);
		
		if (!is_file($file) || !is_readable($file)) {
            return false;
        }
		
		$data = @unserialize(file_get_contents($file));
		if (!$data) {
			return false;
		}
		
		return $data;
	}
	
    /**
     * Fetches a directory to store the cache data
     *
     * @param string $id
     *
     * @return string
     */
    protected function getDirectory($key) {
		$key = $this->setPrefix($key);
		$hash = sha1($key, false);
		$dirs = array(
            $this->getCacheDirectory(),
            substr($hash, 0, 2),
            substr($hash, 2, 2)
        );
        return str_replace('\\', '/', join(DIRECTORY_SEPARATOR, $dirs));
    }
    
	/**
     * Fetches a base directory to store the cache data
     *
     * @return string
     */
    protected function getCacheDirectory() { return str_replace('\\', '/',$this->path); }
	
	 /**
     * Fetches a file path of the cache data
     *
     * @param string $id
     *
     * @return string
     */
    protected function getFileName($key) {

		$key = $this->setPrefix($key);

		$directory = $this->getDirectory($key);
		$hash      = sha1($key, false);
        $file      = str_replace('\\', '/', $directory . DIRECTORY_SEPARATOR . $hash . '.cache');
        return $file;
	}
	
	/**
	 * 	set prefix key
	 *  @param string $key
	 */
	private function setPrefix($key) {
		return (
			substr($key, 0, strlen($this->prefix)) !== $this->prefix ?
				$this->prefix . $key : 
				$key );
	}

	/**
	 *	recursively delete a directory
	 */
	private function delete_directory( $dir ) {
		
		if( is_dir( $dir ) ) {
			
			$files = glob( $dir . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
			
		
			foreach( $files as $file ) {
				$this->delete_directory( $file );      
			}
	 
			rmdir( $dir );
		} 
		elseif( is_file( $dir ) ) {
		  unlink( $dir );
		}
	}
	
}