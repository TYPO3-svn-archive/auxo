<?php

declare(ENCODING = 'UTF-8');

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *	
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */	

/**
 * @package auxo
 * @subpackage core
 * @version $Id$
 */

/**
 * Cache
 *
 * This class represents a cache and offers several methods for caching any kind of object. 
 * Moreover, this class could be configured widely in terms of capacity and lifespan 
 * of cached objects. Instances of this cache class might exists simutanously and might
 * be separated by different namespaces.
 *
 * @package auxo
 * @subpackage core	
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author AHN
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

class tx_auxo_Cache {
    /**
	 * default namespace used if namespace is specific calling cache methods 
	 */
    const	DEFAULT_NAMESPACE = 'AUXO';
    
    /**
	 * prefix that is used creating cache files 
	 */
    const	FILE_PREFIX       = 'CACHE';

    /**
     * default lifespan of an cached object
     */
    const	DEFAULT_LIFESPAN  = 86400;
    
    /** scopes specific where to search cached items **/
    const	SCOPE_MEMORY 		= 1;
    const	SCOPE_FILE   		= 2;
    const	SCOPE_ALL    		= 3;

    /**
     * Lifespan of an cached object
     *
     * @var int $lifespan
     */
    private $lifespan;
    
    /**
     * Maximum number of objects cached in memory
     *
     * @var int $memorySize
     */
    private $memorySize = 50;
    
    /**
     * Miminum number of space available in memory before
     * objects are transfered into a file.
     *
     * @var int $memoryRequired
     */
    private $memoryRequired = 10;
    
    /**
     * Cached objects as array
     *
     * @var array $cache
     */
    private $cache = array();

    /**
     * Benchmark counter for access to memory cached objects
     *
     * @var int $countMemoryAccess
     */
	private $countMemoryAccess = 0;
	
	/**
	 * Benchmark counter for access to file cached objects
	 *
	 * @var int $countFileAccess
	 */
	private $countFileAccess = 0;

	/**
	 * Create a new named cache
	 *
	 * @param string $namespace
	 * @param int $lifespan
	 */
	public function __construct($namespace=self::DEFAULT_NAMESPACE, $lifespan=self::DEFAULT_LIFESPAN) {
		$this->setNamespace($namespace);
		$this->setLifespan($lifespan);
	}
	
	public function setNamespace($namespace) {
		$this->namespace = $namespace;
	}
	
  /**
   * Defines a default lifetime for cached objects.
   *
   * @param integer $option
   * @return void
   */
	public function setLifespan($option) {
		$this->lifespan = $option;
	}
	
  /**
   * Defines a number of objects that are cached in memory. if this limit is 
   * reached 'memoryRequired' cached objects will be written to filesystem.
   *
   * @param integer $option
   * @return void
   */
	public function setMemorySize($option) {
		$this->$memorySize = $option;
	}	

  /**
   * Defines how many places have to be available im memory after old objects 
   * has been written to filesystem.
   *
   * @param integer $option
   * @return void
   */
	public function setMemoryRequired($option) {
		$this->$memoryRequired = $option;
	}
				
  /**
   * Caches an object and optionally specified its lifetime and beloning namespace
   *
   * @param string $id unique id to access cached objects
   * @param mixed $object objects needs to be cached
   * @param int $liftime lifetime in seconds for this cached object  
   * @return void
   */
    public function add($id, $object, $lifetime = 0) {
        $lifetime = time() + ( $lifetime ? $lifetime : $this->$lifespan);
        if (count($this->cache) >= $this->$memorySize) {
			if (! $this->cleanMemoryCache($this->namespace, 'expired')) {
				$this->saveToFile('required');
			}
		}
        self::$cache[$this->generateAccessKey($id)] = array(
		    'lifetime' => $lifetime,
            'data' => serialize($object)
		);
    } 

  /**
   * Returns an cached object
   *
   * @param string $id unique id to access cached objects
   * @return mixed either data or NULL if not found
   */
    public function get($id) {
        if ($this->has($id, $this->namespace, self::SCOPE_MEMORY)) {
        	$this->countMemoryAccess++;
	        return unserialize($this->cache[$this->generateAccessKey($id)]['data']);
        }
        if ($this->has($id, $namespace, $this->SCOPE_FILE)) {
        	$this->$countFileAccess++;
            return unserialize($this->read($this->generateAccessKey($id)));
        }
        return NULL;
    }

  /**
   * Verifies if an object has been cached
   *
   * @param string $id unique id to access cached objects
   * @param integer $scope defines where to search for cached objects either memory or filesystem
   * @return boolean $cached return true if an object has been cached
   */
    public function has($id, $scope = self::SCOPE_ALL) {
    	if (($scope)&self::SCOPE_MEMORY && $this->existsInMemory($id, $this->namespace)) {
			return true;
    	}
    	if (($scope)&self::SCOPE_FILE && $this->existsAsFile($id, $this->namespace)) {
	        return true;			
		}
        return false;
    }

  /**
   * Removes an object from cache
   *
   * @param string $id unique id to access cached objects
   * @return void
   */
    public function remove($id) {
        if ($this->has($id, $this->namespace, self::SCOPE_MEMORY)) {
            unset($this->$cache[$this->generateAccessKey($id)]);
        }
        elseif ($this->has($id, $namespace, self::SCOPE_FILE)) {
        	$filepath = $this->getFilename($this->generateAccessKey($id));
        	@unlink($filepath);
        }
    }

  /**
   * Callback method that compares the lifetime of two cached objects
   *
   * @param mixed $a cached object
   * @param mixed $b cached object
   * @return integer returns either 0 = equal, 1 = greater, -1 less than
   */
	public function sortCacheByLifetime($a, $b) {
		if ($a['lifetime'] > $b['lifetime']) {
			return -1;
		}
		elseif ($a['lifetime'] < $b['lifetime']) {
			return 1;
		}
		return 0;
	}

  /**
   * Cleans cached objects either from memory or filesystem
   *
   * @param string $namespace namespace to distiguish objects
   * @param string $mode delete either 'all' or only 'expired' objects
   * @return void
   */
	public function clean($mode = 'all') {
		$this->cleanMemoryCache($mode);
		$this->cleanFileCache($mode);
	}
	
  /**
   * Saves all non-expired objects in memory cache to disk in order to
   * shutdown 
   *
   * @return void
   */
	public function	shutdown() {
		$this->cleanMemoryCache('expired');
		$this->saveToFile('all');
	}	
	
  /**
   * Verifies if an object has been cached in memory
   *
   * @param string $id unique id to access cached objects
   * @param string $namespace namespace to distiguish objects
   * @return
   */
    protected function existsInMemory($id) {
        if (isset($this->$cache[$this->generateAccessKey($id)])) {
		    if ($this->$cache[$this->generateAccessKey($id)]['lifetime'] > time()) {
		    	/**
				 * @todo random cleanup has to be added here
				 */
                return true;
            }
        }
        
        return false;
    }

  /**
   * Verifies if an object has been cached as file
   *
   * @param string $id unique id to access cached objects
   * @param string $namespace namespace to distiguish objects
   * @return
   */
    protected function existsAsFile($id) {
        $filepath = $this->getFilename($this->generateAccessKey($id));
        if ((file_exists($filepath)) && ((@filemtime($filepath) + $this->$lifespan) > time())) {
            return true;
        }
        return false;
    }

  /**
   * Saves memory cache items 
   *
   * @param string $mode either "required" or "all" files will be saved
   * @return boolean $saved
   */
    protected function saveToFile($mode) {
    	if ($mode == 'required') {
			uasort($this->$cache, array('tx_auxo_Cache', 'sortCacheByLifetime'));
			$chunks = array_chunk($this->$cache, $this->$memoryRequired, true);
			foreach($chunks[0] as $key => $item) {
				if (!isset($item['saved'])) {
					$this->write($key);
				}
			}
			$this->$cache = $chunks[1];
		}
		else {
			foreach($this->$cache as $key => $item) {
				if (!isset($item['saved'])) {
					$this->write($key);
				}
			}			
		}
		return true;
	}
	
  /**
   * Cleans cached items from memory
   *
   * @param string $mode delete either 'all' or only 'expired' objects
   * @return boolean $deleted true if any object has been deleted
   */
    protected function cleanMemoryCache($mode) {
    	$deleted = false;
		 
    	switch ($mode) {
	        case 'all':
				$this->$cache = array();		
				$this->$countMemoryAccess = 0;
				$deleted = true;
				break;
			case 'expired':
				$lifetime = time();
				foreach ($this->$cache as $key => $item) {
					if (strncmp($key, $this->namespace, strlen($namespace)) == 0) {
						if ($item['lifetime'] < $lifetime) {
							unset($this->$cache[$key]);
							$deleted = true; 
						}
					}
				}
				break;
		}
		
		return $deleted;
    }

  /**
   * Generates a key for accessing items either in memory or filesystem
   *
   * @param string $id unique id to access cached objects
   * @return string $accessKey access key
   */
    protected function generateAccessKey($id) {
        return $this->namespace . '_' . $id;
    }

  /**
   * Reads an saved cache item from filesystem
   *
   * @param string $key access key 
   * @return mixed $data data of an cached object
   */
    protected function read($key) {
        $filepath = $this->getFilename($key);
        
	    if (!($handle = @fopen($filepath, "rb"))) {
			throw new tx_auxo_exception(sprintf('Cache file: %s can not be opened', $filepath));
		}
	
	    clearstatcache(); 
	    $length = @filesize($filepath);
	    $quotes = get_magic_quotes_runtime();
	    set_magic_quotes_runtime(0);
	    $data = ($length) ? @fread($handle, $length) : '';
	    set_magic_quotes_runtime($quotes);
	    @fclose($handle);

        if (count($this->$cache) < ($this->$memorySize - $this->$memoryRequired)) {
        	$this->$cache[$key] = array(
			   'data' => $data,
			   'saved' => true,
			   'lifetime' => filemtime($filepath) + $this->$lifespan
			);
		}
		return $data;
    }

  /**
   * Writes a cached object to filesystem
   *
   * @param $key key of cached object in memory
   * @throws tx_auxo_exceptions if write cache file failed
   * @return boolean $success
   */
    protected function write($key) {
	    $quotes = get_magic_quotes_runtime();
	    set_magic_quotes_runtime(0);
    	$error = t3lib_div::writeFileToTypo3tempDir($this->getFilename($key), $this->$cache[$key]['data']);
	    set_magic_quotes_runtime($quotes);
		if($error) {
			throw new tx_auxo_exception(sprintf('Cache file %s: %s', $this->getFilename($key), $error));	
		}
        return true;
    }

  /**
   * Cleans cached objects from filesystem
   *
   * @param $mode 
   * @throws tx_auxo_exception unable to open cache directory
   * @return boolean $cleaned
   */
    protected function cleanFileCache($mode) {
    	$cacheDir = $this->getCacheDir();
		if (!($handle = @opendir($cacheDir))) {
            throw new tx_auxo_exception(sprintf('Unable to open %s cache directory', $cacheDir));
        }

        $cleaned = true;
        $lifetime = time() + $this->$lifespan;
        $prefix = self::FILE_PREFIX . '_' . $this->namespace;
        
		while ($file = readdir($handle)) {
            if (($file != '.') && ($file != '..') && strncmp($file, $prefix, strlen($prefix)) == 0 ) {
                $filepath = $cacheDir . '/' . $file;
                if (is_file($filepath)) {
                	switch ($mode) {
                		case 'expired':
		                    if (filemtime($filepath) < $lifetime) {
		                        $cleaned = @unlink($filepath) and $cleaned;
		                    }
		                    break;
		                case 'all':
		                    $cleaned = @unlink($filepath) and $cleaned;
					        $this->$countFileAccess = 0;
		                	break;
		            }
                }
            }
        }
        
        return $cleaned;
    }
    
  /**
   * Generates and returns a filename for a given cache object
   *
   * @param mixed $key
   * @return string $filepath
   */
    protected function getFilename($key) {
		return $this->getCacheDir(). '/' . self::FILE_PREFIX . '_' . $key;
	}
	
	/**
	 * Validates a cached object if outdated by a given key
	 *
	 * @return boolean $valid returns true if object is valid or false if outdated
	 */
	protected function validate($key) {
		/*
		 * @todo here an automated validation of cached objects have to be implemented. 
		 * This might be used for caching of scanned or parsed files in order to detected 
		 * if a files has been changed.
		 */
		return true;
	}

  /**
   * Returns path to cache directory
   *
   * @return string $path
   */
    protected function getCacheDir() {
		return PATH_site . 'typo3temp' . '/' . 'auxo';
	}
}
?>