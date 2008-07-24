<?php
/**
 * @package auxo
 * @subpackage view
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
 */


// load google API
require_once(t3lib_extMgm::extPath('auxo') . 'vendors/google/GoogleMapAPI.class.php');

/**
 * GeomapHelper
 * 
 * Class that offers methods to present object data as google maps. 
 * This helper use typo3 extensions 'Google map' and 'lumogooglemaps' 
 * of Thomas Off, LumoNet oHG <t.off@lumonet.de>
 * 
 * @package auxo
 * @subpackage view
 * @author Andreas Horn <Andreas.Horn@extronaut.de>
 * @access public
 */
class tx_auxo_aui_geomap extends tx_auxo_aui_HTMLcomponent {
	
	private $map;
	private $apiKey;
    private $height;
    private $width;
    private $mapType;
    private $controls;
    private $markerImage;
    private $shadowImage;
    

	public function setDimensions($height, $width) {
		$this->map->setHeight( $height );
		$this->map->setWidth( $width );
	}
	
	public function setMapType($mapType) {
		$this->mapType = $mapTyp;	
	}
	
	public function enableControls() {
		$this->controls = true;
	}
	
	public function disableControls() {
		$this->controls = false;		
	}
	
	public function setNavigation($navigation) {
		$this->navigation = $navigation;
	}
	
	public function setMarkerImages($marker, $shadow='') {
		
	}
	
	public function setAPIKey($apiKey) {
		$this->map->setAPIKey(trim($apiKey));
	}
	
	/**
	 * initialize
	 * 
	 * Initializes a map and get configuration values.
	 */
	static protected function initialize($view) {
		// Width
		$this->iWidth = $this->controller->configurations->get('width');
		$this->iWidth = $iWidth ? $iWidth : 500;

		// Height
		$this->iHeight = $this->controller->configurations->get('height');
		$this->iHeight = $iHeight ? $iHeight : 500;

		// Type of map
		$this->sType = $this->controller->configurations->get('type');
		$this->sType = $sType ? $sType : 'map';

		// Show type controls
		$this->sTypeControls = $this->controller->configurations->get('type_controls');
		switch ($sTypeControls) {
			case 'hide':
				$this->iTypeControls = 0;
				break;
			case 'show':
			default:
				$this->iTypeControls = 1;
				break;
		}

		// Show navigation controls?
		$this->sNavControls = $this->controller->configurations->get('nav_controls');
		switch ($this->sNavControls) {
			case 'none':
				$this->iNavControls = 0;
				break;
			case 'small':
				$this->iNavControls = 1;
				break;
			case 'large':
			default:
				$this->iNavControls = 2;
				break;
		}
			
		// Marker icon
		$this->sMarkerIcon = $this->controller->configurations->get('MakerIcon');

		// Marker shadow
		$this->sMarkerShadow = $this->controller->configurations->get('MarkerShadow');
	
		// x, y  coord in image for spot
		$this->iMarkerSpotX = $this->controller->configurations->get('MarkerSpotX');
		$this->iMarkerSpotX = $this->iMarkerSpotX ? $this->iMarkerSpotX : 0;
     	$this->iMarkerSpotY = $this->get->controller->configurations->get('MarkerSpotY');
		$this->iMarkerSpotY = $this->iMarkerSpotY ? $this->iMarkerSpotY : 0;

		// x, y coord in image for info box
		$this->iMarkerInfoX = $this->controller->configurations->get('MarkerInfoX');
		$this->iMarkerInfoX = $this->iMarkerInfoX ? $this->iMarkerInfoX : 10;
		$this->iMarkerInfoY = $this->controller->configurations->get('MarkerInfoY');
		$this->iMarkerInfoY = $this->iMarkerInfoY ? $this->iMarkerInfoY : 10;
		
		// Google API key
		$this->sGoogleApiKey = trim($this->controller->configurations->get('google_api_key'));
		$this->sGoogleApiKey = $this->sGoogleApiKey ? $this->sGoogleApiKey : '';
		return $config;
	}
	
	/**
	 * buildMap
	 * 
	 * @return $map map
	 *
	 */
	function buildMap() {
		// Generate unique ids for containing div and Yahoo
		$sMapId = t3lib_div::shortMD5('map' . time());
		$sYahooId = 'yahoo_' . t3lib_div::shortMD5('yahoo' . time());

		// Generate map object
		$map = new GoogleMapAPI($sMapId, $sYahooId);
		// Set Google API key
		$map->setAPIKey($this->sGoogleApiKey);
		// Width
		$map->setWidth($this->width);
		// Height
		$map->setHeight($this->height);
		// Type of map
		$map->setMapType($this->mapType);
		// Show type controls?
		if ($this->iTypeControls) {
			$map->enableTypeControls();
		}
		else {
			$map->disableTypeControls();
		}
		// Show navigation controls?
		switch ($this->iNavControls) {
			case 0:
				$map->disableMapControls();
				break;
			case 1:
				$map->enableMapControls();
				$map->setControlSize('small');
				break;
			case 2:
			default:
				$map->enableMapControls();
				$map->setControlSize('large');
				break;
		}
		
		// Marker icon
		if ($this->markerIcon != '' && $this->markerShadow != '') {
			$map->setMarkerIcon( $this->MarkerIcon, $this->MarkerShadow, 
			                     $this->MarkerSpotX, $this->iMarkerSporY,
			                     $this->MarkerInfoX, $this->MarkerInfoY );
		}

		// Set language labels for map
		$lDirLabels = $map->getDirectionsText();
		$lDirLabels['dir_text'] = $this->pi_getLL('map.directions.dir_text');
		$lDirLabels['dir_tohere'] = $this->pi_getLL('map.directions.dir_tohere');
		$lDirLabels['dir_fromhere'] = $this->pi_getLL('map.directions.dir_fromhere');
		$lDirLabels['dir_to'] = $this->pi_getLL('map.directions.dir_to');
		$lDirLabels['button_to'] = $this->pi_getLL('map.directions.button_to');
		$lDirLabels['dir_from'] = $this->pi_getLL('map.directions.dir_from');
		$lDirLabels['button_from'] = $this->pi_getLL('map.directions.button_from');
		$map->setDirectionsText($lDirLabels);
		$map->setBrowserAlert($this->pi_getLL('map.browseralert'));
		$map->setJsAlert($this->pi_getLL('map.jsalert'));
		return $map;
	}

	/**
	 * Add marker from table tt_address to map
	 *
	 * @return	integer	Number of added markers
	 */
	function addMarkersToMap($map, $object) {
	   // traverse data records and transfer their coordinates 
	   for ($object->rewind(); $object->valid(); $object->next()) { 
           $record = $this->current();
			// Get longitude and latitude
			$fLongitude = floatval($record['longitude']);
			$fLatitude = floatval($record['latitude']);
			if ($fLongitude == 0.0 || $fLatitude == 0.0) {
				if ($record['address'] == '' &&
					$record['zip'] == '' &&
					$record['location'] == '') {
					// No address and no coords stored => no marker to set
					continue;
				}

				// Try to get coords from geocoding service
				$sAddress = preg_replace('/\s+/', ' ', $record['address']) . ' ' . $record['zipcode'] . ' ' . $lRow['location'] . ' ' . $record['country'];
				$sAddress = trim(preg_replace('/\s+/', ' ', $sAddress));
				$lCoords  = $map->geoGetCoords($sAddress);
				if (count($lCoords) == 2) {
					$record['longitude'] = $lCoords['lon'];
					$record['latitude'] = $lCoords['lat'];
				}
				else {
					// Coords couldn't be retrieved from geocoding service
					continue;
				}
			}
			
			// Build up content of info box
			$sInfoBox = $this->createInfoBox($record);

			// Set marker in map
			$this->oMap->addMarkerByCoords($record['longitude'], $lRow['latitude'], $record['title'], $sInfoBox);
			$iCount++;
		}

		return $iCount;
	}

	/**
	 * renderSidebar
	 *
	 * print sidebar items
	 *
	 * @return	string	Processed template for sidebar
	 */
	function renderSidebar($view, $map) {
		$sContent = '';
		// Fetch sidebar elements
		$lSidebarItems = $this->oMap->getSidebarItems();

		// Compose list items
		$sListContent = '';
		foreach ($lSidebarItems as $key => $val) {
			$link  = $val[0];
			$title = $val[1];
			
			$sListContent .= $link.$title;
		}
		// Insert list items in template
		if (sListContent != '') {
		   $sListContent.= $sListContent;
		}

		return $sContent;
	}
	
	/**
	 * print info box with content of data record
	 *
	 * @param	array	Array containing an data record 
	 * @return	string	Processed template for sidebar
	 */
	function addInfoBox($view, $map, $info) {
		$sContent = $info;	
		// Field: name
        // @TODO: fill output stream
		// Remove newlines (as infobox HTML is used in JavaScript)
		$sContent = str_replace("\r", '', $sContent);
		$sContent = str_replace("\n", '', $sContent);

		return $sContent;
	}

	    /**
     * method		render
     *
     * @return 		string rendered control
     */
	public function render(tx_auxo_aui_renderer $renderer)	{
		// Create map object and set properties
		$map = $this->buildMap();

		// Add marker from database to map
		$iCount = $this->addMarkersToMap();

		// Error handling: if no marker is set, then center to default place
		if ($iCount == 0) {
			$map->setCenterCoords(11.2005, 47.6765);
		}

		// Fill marker array
		$content = $map->getHeaderJS();
		$content.= $map->getMapJS();
		$content.= $map->getMap();

		// Return rendered content
		return $content;
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_geomapHelper.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_geomapHelper.php']);
}
?>