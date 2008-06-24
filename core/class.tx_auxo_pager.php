<?php
/**
 * @package auxo
 * @subpackage util
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


/** Pager 
 *
 *  This pager class might be used to paginate an object list so that it
 *  can be displayed easily. It offers a lot of convience methods which 
 *  allows to find out anything about the current pager status. 
 *  Moreover, this pager class decorates tx_lib_object that means all 
 *  methods offers by this class could be used without any restriction.
 *  
 *  Usage:
 *  <code>
 *      // create a new page for this model class 
 *      $pager = tx_auxo_pager('tx_auxo_models_people', 5, 100);
 *
 *      // create page navigation
 *      if ($pager->isPaginated()) {
 *       	if ($link = $pager->getFirstPage()) {
 *             printf('<a href="abc.php?page=<?=$link?>"> << </a>', $link);
 *          }
 *
 *       	if ($link = $pager->getPreviousPage()) {
 *             printf('<a href="abc.php?page=<?=$link?>"> < </a>', $link);
 *          }
 *
 *       	if ($links = $pager->getPreviousLinks()) {
 *              foreach($links as $link) {
 *                 printf('<a href="abc.php?page=<?=$link?>"> < </a>', $link);
 *              }
 *          }
 * 
 *          echo $pager->getCurrentPage();
 *
 *       	if ($links = $pager->getNextLinks()) {
 *              foreach($links as $link) {
 *                 printf('<a href="abc.php?page=<?=$link?>"> < </a>', $link);
 *              }
 *          }
 *
 *       	if ($link = $pager->getNextPage()) {
 *             printf('<a href="abc.php?page=<?=$link?>"> > </a>', $link);
 *          }
 *
 *       	if ($link = $pager->getLastPage()) {
 *             printf('<a href="abc.php?page=<?=$link?>"> >> </a>', $link);
 *          }
 *      }
 *
 *      // iterate the object result list of the current page
 *      while($pager->isValid()) {
 *          $current = $pager->current();
 *          ...
 *          echo $current->get('firstname');
 *          ...
 *          $pager->next();
 *      }
 *  </code>
 *
 *  Following groups of methods are implemeted:
 *  - Page validators:
 *  isPaginated, hasPreviousPage, hasNextPage, isFirstPage, isLastPage, isLastPageComplete
 *  - Page getters: 
 *  getFirstPage, getLastPage, getCurrentPage, getNextPage, getPreviousPage
 *  - page setters: 
 *  setCurrentPage, gotoFirstPage, gotoLastPage, gotoPreviousPage, gotoNextPage
 *  - Configuration getters:
 *  getMaxEntriesPerPage, getMaxPages, getMaxLinks
 *  - Confguration setters:
 *  setMaxEntriesPerPage, setMaxPages, setMaxLinks
 *  - Result getters:
 *  getTotalCount, getTotalPages
 *  - Links getters:
 *  getPreviousLinks, getNextLinks
 *
 * @package auxo
 * @subpackage util
 * @author Andreas Horn <Andreas.Horn@extronaut.de>
 */
class tx_auxo_pager extends tx_lib_object {

	private $peerClass = '';
	private $selectMethod = 'select';
	private $countMethod = 'selectCount';
	private $criteria = NULL;
	private $options = array();
	
	private $maxLinks = 5;
	private $currentPage = 0;
	private $position = 0;
	private $maxPages = 0;
	private $maxEntriesPerPage = 0;
	private $maxEntries = 0;
	private $countPages = 0;
	private $countEntries = 0;
	
  /**
   * tx_auxo_pager::__construct()
   *
   * @param mixed 	$peerClass
   * @param mixed 	$maxEntriesPerPage
   * @param mixed 	$maxPages
   * @param integer $gotoPage
   * @param mixed 	$options
   * @return void
   */
    public function __construct($peerClass, $maxEntriesPerPage, $maxPages, $gotoPage=0, $options=array()) {
    	parent::__construct();
    	// intialize
		$this->peerClass = $peerClass;
		$this->setMaxPages($maxPages);
		$this->setMaxEntriesPerPage($maxEntriesPerPage);
		$this->maxEntries = $maxPages * $maxEntriesPerPage;
		// extract and set additional parameters	
		foreach(array('criteria', 'selectMethod', 'countMethod') as $option) {
			if (isset($options[$option])) {
				$this->{$option} = $options[$option];
				unset($options[$option]);
			}
		}
		$this->options = $options;
		// count all entries
		$this->countEntries = $this->countEntries();
		// calculate count pages
		if ($this->countEntries > 0) {
			$this->countPages = floor($this->countEntries / $this->getMaxEntriesPerPage());
			if ($gotoPage <= 0 || $gotoPage > $this->getMaxPages()) {
				$gotoPage = 1;
			}
			$this->setCurrentPage($gotoPage);					
		}
    }
    
  /**
   * getPreviousLinks
   *
   * Returns an array of links (limited by parameter range) left from 
   * the current position 
   *
   * @param  integer $range number of links that are required
   * @return array 	 $links links left from the current position
   */
    public function getPreviousLinks($range=0) {
		if (!$this->hasPreviousPages()) {
			return NULL;
		}

		if ($range==0) $range = $this->getMaxLinks();
		
		$end = $this->getCurrentPage() - 1;
		$start = min(2, $end - $range);
		
		for ($i=$start; $i <= $end; $i++) {
			$links[] = $i;
		}

		return $links;
	}
	
	
  /**
   * getNextLinks
   *
   * Returns an array of links (limited by parameter range) right from the current
   * position.
   *
   * @param  integer $range number of links required
   * @return array   $links links right from the current position
   */
	public function getNextLinks($range=0) {
		if (!$this->hasNextPages()) {
			return NULL;
		}
		
		if ($range==0) $range = $this->getMaxLinks();
		
		$start = $this->getCurrentPage() + 1;
		$end = min($start + $range, $this->getTotalPage()-1);
		
		for ($i=$start; $i <= $end; $i++) {
			$links[] = $i;
		}
		
		return $links;		
	}

	/** page validation methods **/
	
  /**
   * tx_auxo_pager::isPaginated()
   *
   * @return
   */
    public function isPaginated() {
		return $this->getTotalCount() > $this->getMaxEntriesPerPage();
	}
	
  /**
   * tx_auxo_pager::isLastPageComplete()
   *
   * @return
   */
	public function isLastPageComplete() {
		return !$this->getTotalCount() % $this->getMaxEntriesPerPage();
	}
	
  /**
   * tx_auxo_pager::isFirstPage()
   *
   * @return
   */
	public function isFirstPage() {
		return $this->getCurrentPage() == 1;
	}
	
  /**
   * tx_auxo_pager::isLastPage()
   *
   * @return
   */
	public function isLastPage() {
		return $this->getCurrentPage() == $this->getTotalPages();
	}

  /**
   * tx_auxo_pager::hasPreviousPage()
   *
   * @return
   */
	public function hasPreviousPage() {
		return $this->getCurrentPage() > 1 ? true : false;	
	}

  /**
   * tx_auxo_pager::hasNextPage()
   *
   * @return
   */
	public function hasNextPage() {
		return $this->getCurrentPage() + 1 <= $this->getTotalPages() ? true : false;	
	}
	

	/** page getter methods **/
	
  /**
   * tx_auxo_pager::getFirstPage()
   *
   * @return
   */
	public function getFirstPage() {
        return $this->isPaginated() ? 1 : false;
    }

  /**
   * tx_auxo_pager::getLastPage()
   *
   * @return
   */
    public function getLastPage() {
        return $this->isPaginated() ? $this->getTotalPages() : false;
    }
    
  /**
   * tx_auxo_pager::getCurrentPage()
   *
   * @return
   */
    public function getCurrentPage() {
		return $this->currentPage;
	}

  /**
   * tx_auxo_pager::setCurrentPage()
   *
   * @param mixed $pageNumber
   * @return
   */
    public function setCurrentPage($pageNumber) {
        if ($pageNumber > 0 && $pageNumber <= $this->getTotalPages() && $pageNumber <> $this->getCurrentPage()) {
	        $this->currentPage = $pageNumber;
	        $this->position = ($pageNumber - 1) * $this->getMaxEntriesPerPage();
	        $this->retrieveObjects($this->position);
	        return $pageNumber;
        }
		return false;
    }

  /**
   * tx_auxo_pager::getNextPage()
   *
   * @return
   */
    public function getNextPage() {
        return $this->getValidPage($this->getCurrentPage() + 1);
    }

  /**
   * tx_auxo_pager::getPreviousPage()
   *
   * @return
   */
    public function getPreviousPage() {
        return $this->getValidPage($this->getCurrentPage() - 1);
    }
    
  /**
   * tx_auxo_pager::getValidPage()
   *
   * @param mixed $pageNumber
   * @return
   */
    public function getValidPage($pageNumber) {
		if ($pageNumber > 0 && $pageNumber <= $this->getTotalPages()) {
			return $pageNumber;
		}
		return false;
	}
	
  /**
   * tx_auxo_pager::gotoFirstPage()
   *
   * @return
   */
	public function gotoFirstPage() {
        return $this->setCurrentPage(1);
    }

  /**
   * tx_auxo_pager::gotoLastPage()
   *
   * @return
   */
    public function gotoLastPage() {
        return $this->setCurrentPage($this->getTotalPages());
    }

  /**
   * tx_auxo_pager::gotoNextPage()
   *
   * @return
   */
    public function gotoNextPage() {
        return $this->setCurrentPage($this->getCurrentPage() + 1);
    }

  /**
   * tx_auxo_pager::gotoPreviousPage()
   *
   * @return
   */
    public function gotoPreviousPage() {
        return $this->setCurrentPage($this->getCurrentPage() - 1);
    }
    
  /**
   * tx_auxo_pager::getTotalPages()
   *
   * @return
   */
    public function getTotalPages() {
		return $this->countPages;
	}
	
  /**
   * tx_auxo_pager::getTotalCount()
   *
   * @return
   */
	public function getTotalCount() {
		return $this->countEntries;
	}
	
  /**
   * tx_auxo_pager::getMaxEntriesPerPage()
   *
   * @return
   */
	public function getMaxEntriesPerPage() {
		return $this->maxEntriesPerPage;
	}
	
  /**
   * tx_auxo_pager::getMaxPages()
   *
   * @return
   */
	public function getMaxPages() {
		return $this->maxPages;
	}
	
  /**
   * tx_auxo_pager::getMaxLinks()
   *
   * @return
   */
	public function getMaxLinks() {
		return $this->maxLinks;
	}
	
	/** setter **/
	
  /**
   * tx_auxo_pager::setMaxEntriesPerPage()
   *
   * @param mixed $maxEntriesPerPage
   * @return
   */
	public function setMaxEntriesPerPage($maxEntriesPerPage) {
		$this->maxEntriesPerPage = $maxEntriesPerPage;
	}
	
  /**
   * tx_auxo_pager::setMaxPages()
   *
   * @param mixed $maxPages
   * @return
   */
	public function setMaxPages($maxPages) {
		if ($maxPages < $this->getMaxPages()) {
			if ($maxPages < $this->getCurrentPage()) {
				$this->setCurrentPage($maxPages);
			}
			$this->countEntries = $maxPages * $this->getMaxEntriesPerPage();
			$this->maxEntries = $this->countEntries;
		}
		$this->maxPages = $maxPages;
	}
	
  /**
   * tx_auxo_pager::setMaxLinks()
   *
   * @param mixed $maxLinks
   * @return
   */
	public function setMaxLinks($maxLinks) {
		$this->maxLinks = $maxLinks;
	}
	
    /** data retrieval methods **/
    
  /**
   * tx_auxo_pager::countEntries()
   *
   * @return
   */
	protected function countEntries() {
		return call_user_func(array($this->peerClass, $this->countMethod), 
							  $this->getQuery($this->maxEntries), 
							  $this->options);			
	}
	
  /**
   * tx_auxo_pager::retrieveObjects()
   *
   * @param mixed $start
   * @return
   */
	protected function retrieveObjects($start) {
		$objects = call_user_func(array($this->peerClass, $this->selectMethod), 
		                          $this->getQuery(array($this->position, $this->getMaxEntriesPerPage())), 
								  $this->options);		
		if (!$objects) {
			return NULL;
		}
		$this->clear();
		for ($objects->rewind(); $objects->valid(); $objects->next()) {
			$this->append($objects->current());
		}
		$this->rewind();
	}
	
	
  /**
   * tx_auxo_pager::getQuery()
   *
   * @param mixed $limit
   * @return
   */
	protected function getQuery($limit) {
		if (is_object($this->criteria)) {
			$query = $this->criteria;
		}
		else {
	        $query = new tx_auxo_query();        
	    }
  		$query->limit($limit);
		return $query;
	}
}
?>