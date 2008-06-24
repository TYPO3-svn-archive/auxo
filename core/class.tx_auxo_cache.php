<?php
/**
 * @package auxo
 * @subpackage core
 * @author Andreas Horn <Andreas.Horn@extronaut.de>
 * @copyright 2007
 * @version $WCREV$
 *
 * LICENSE:
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 **/

/**
 * Cache
 *
 * This class represents a cache and offers several static methods 
 * for caching any objects.
 *
 * @package 	auxo
 * @subpackage	core
 * @author 		Andreas Horn
 * @access 		public
 */

class tx_auxo_cache {
    /** default namespace used if namespace is specific calling cache methods **/
    const DEFAULT_NAMESPACE = 'AUXO';
    /** prefix that is used creating cache files **/
    const FILE_PREFIX       = 'CACHE';

    /** scopes specific where to search cached items **/
    const SCOPE_MEMORY 		= 1;
    const SCOPE_FILE   		= 2;
    const SCOPE_ALL    		= 3;

    static private $lifespan       = 86400;
    static private $memorySize     = 50;
    static private $memoryRequired = 10;
    static private $cache          = array();

	/** counter for benchmarking and optimization **/
	static private $countMemoryAccess = 0;
	static private $countFileAccess   = 0;

  /**
   * Defines a default lifetime for cached objects.
   *
   * @param integer $option
   * @return void
   */
	public function setLifespan($option) {
		self::$lifespan = $option;
	}
	

  /**
   * Defines a number of objects that are cached in memory. if this limit is 
   * reached 'memoryRequired' cached objects will be written to filesystem.
   *
   * @param integer $option
   * @return void
   */
	public function setMemorySize($option) {
		self::$memorySize = $option;
	}
		

  /**
   * Defines how many places have to be available im memory after old objects 
   * has been written to filesystem.
   *
   * @param integer $option
   * @return void
   */
	public function setMemoryRequired($option) {
		self::$memoryRequired = $option;
	}
	
				
  /**
   * Caches an object and optionally specified its lifetime and beloning namespace
   *
   * @param string $id unique id to access cached objects
   * @param string $namespace namespace to distiguish objects
   * @return void
   */
    public function add($id, $object, $lifetime = 0, $namespace = self::DEFAULT_NAMESPACE) {
        $lifetime = time() + ( $lifetime ? $lifetime : self::$lifespan);
        if (count(self::$cache) >= self::$memorySize) {
			if (!self::cleanMemoryCache($namespace, 'expired')) {
				self::saveToFile('required');
			}
		}
        self::$cache[self::generateAccessKey($id, $namespace)] = array(
		    'lifetime' => $lifetime,
            'data' => serialize($object)
		);
    }
    

  /**
   * Returns an cached object
   *
   * @param string $id unique id to access cached objects
   * @param string $namespace namespace to distiguish objects
   * @return mixed either data or NULL if not found
   */
    public function get($id, $namespace = self::DEFAULT_NAMESPACE) {
        if (self::has($id, $namespace, self::SCOPE_MEMORY)) {
        	self::$countMemoryAccess++;
	        return unserialize(self::$cache[self::generateAccessKey($id, $namespace)]['data']);
        }
        if (self::has($id, $namespace, self::SCOPE_FILE)) {
        	self::$countFileAccess++;
            return unserialize(self::read(self::generateAccessKey($id, $namespace)));
        }
        return NULL;
    }


  /**
   * Verifies if an object has been cached
   *
   * @param string $id unique id to access cached objects
   * @param string $namespace namespace to distiguish objects
   * @param integer $scope defines where to search for cached objects either memory or filesystem
   * @return boolean $cached return true if an object has been cached
   */
    public function has($id, $namespace = self::DEFAULT_NAMESPACE, $scope = self::SCOPE_ALL) {
    	if (($scope)&self::SCOPE_MEMORY && self::existsInMemory($id, $namespace)) {
			return true;
    	}
    	if (($scope)&self::SCOPE_FILE && self::existsAsFile($id, $namespace)) {
	        return true;			
		}
        return false;
    }


  /**
   * Removes an object from cache
   *
   * @param string $id unique id to access cached objects
   * @param string $namespace namespace to distiguish objects
   * @return
   */
    public function remove($id, $namespace = self::DEFAULT_NAMESPACE) {
        if (self::has($id, $namespace, self::SCOPE_MEMORY)) {
            unset(self::$cache[self::generateAccessKey($id, $namespace)]);
        }
        elseif (self::has($id, $namespace, self::SCOPE_FILE)) {
        	$filepath = self::getFilename(self::generateAccessKey($id, $namespace));
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
	public function clean($namespace = self::DEFAULT_NAMESPACE, $mode = 'all') {
		self::cleanMemoryCache($namespace, $mode);
		self::cleanFileCache($namespace, $mode);
	}

	
  /**
   * Saves all non-expired objects in memory cache to disk in order to
   * shutdown 
   *
   * @return void
   */
	public function	shutdown() {
		self::cleanMemoryCache(self::DEFAULT_NAMESPACE, 'expired');
		self::saveToFile('all');
	}	
	
  /**
   * Verifies if an object has been cached in memory
   *
   * @param string $id unique id to access cached objects
   * @param string $namespace namespace to distiguish objects
   * @return
   */
    protected function existsInMemory($id, $namespace = self::DEFAULT_NAMESPACE) {
        if (isset(self::$cache[self::generateAccessKey($id, $namespace)])) {
		    if (self::$cache[self::generateAccessKey($id, $namespace)]['lifetime'] > time()) {
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
    protected function existsAsFile($id, $namespace = self::DEAULT_NAMESPACE) {
        $filepath = self::getFilename(self::generateAccessKey($id, $namespace));
        if ((file_exists($filepath)) && ((@filemtime($filepath) + self::$lifespan) > time())) {
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
			uasort(self::$cache, array('tx_auxo_cache', 'sortCacheByLifetime'));
			$chunks = array_chunk(self::$cache, self::$memoryRequired, true);
			foreach($chunks[0] as $key => $item) {
				if (!isset($item['saved'])) {
					self::write($key);
				}
			}
			self::$cache = $chunks[1];
		}
		else {
			foreach(self::$cache as $key => $item) {
				if (!isset($item['saved'])) {
					self::write($key);
				}
			}			
		}
		return true;
	}
	
	
  /**
   * Cleans cached items from memory
   *
   * @param string $namespace namespace to distiguish objects
   * @param string $mode delete either 'all' or only 'expired' objects
   * @return boolean $deleted true if any object has been deleted
   */
    protected function cleanMemoryCache($namespace = self::DEFAULT_NAMESPACE, $mode) {
    	$deleted = false;
		 
    	switch ($mode) {
	        case 'all':
				self::$cache = array();		
				self::$countMemoryAccess = 0;
				$deleted = true;
				break;
			case 'expired':
				$lifetime = time();
				foreach (self::$cache as $key => $item) {
					if (strncmp($key, $namespace, strlen($namespace)) == 0) {
						if ($item['lifetime'] < $lifetime) {
							unset(self::$cache[$key]);
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
   * @param string $namespace namespace to distiguish objects
   * @return string $accessKey access key
   */
    static protected function generateAccessKey($id, $namespace = self::DEFAULT_NAMESPACE) {
        return $namespace . '_' . $id;
    }

  /**
   * Reads an saved cache item from filesystem
   *
   * @param string $key access key 
   * @return mixed $data data of an cached object
   */
    static protected function read($key) {
        $filepath = self::getFilename($key);
        
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

        if (count(self::$cache) < (self::$memorySize - self::$memoryRequired)) {
        	self::$cache[$key] = array(
			   'data' => $data,
			   'saved' => true,
			   'lifetime' => filemtime($filepath) + self::$lifespan
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
    static protected function write($key) {
	    $quotes = get_magic_quotes_runtime();
	    set_magic_quotes_runtime(0);
    	$error = t3lib_div::writeFileToTypo3tempDir(self::getFilename($key), self::$cache[$key]['data']);
	    set_magic_quotes_runtime($quotes);
		if($error) {
			throw new tx_auxo_exception(sprintf('Cache file %s: %s', self::getFilename($key), $error));	
		}
        return true;
    }
    

  /**
   * Cleans cached objects from filesystem
   *
   * @param $namespace
   * @param $mode 
   * @throws tx_auxo_exception unable to open cache directory
   * @return boolean $cleaned
   */
    static protected function cleanFileCache($namespace = self::DEFAULT_NAMESPACE, $mode) {
    	$cacheDir = self::getCacheDir();
		if (!($handle = @opendir($cacheDir))) {
            throw new tx_auxo_exception(sprintf('Unable to open %s cache directory', $cacheDir));
        }

        $cleaned = true;
        $lifetime = time() + self::$lifespan;
        $prefix = self::FILE_PREFIX . '_' . $namespace;
        
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
					        self::$countFileAccess = 0;
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
    static protected function getFilename($key) {
		return self::getCacheDir(). '/' . self::FILE_PREFIX . '_' . $key;
	}


  /**
   * Returns path to cache directory
   *
   * @return string $path
   */
    static protected function getCacheDir() {
		return PATH_site . 'typo3temp' . '/' . 'auxo';
	}
}

?>