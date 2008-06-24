<?php

/**
 * Project:	GoogleMapAPI: a PHP library inteface to the Google Map API
 * File:	GoogleMapAPI.class.php
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * For questions, help, comments, discussion, etc., please join the
 * Smarty mailing list. Send a blank e-mail to
 * smarty-general-subscribe@lists.php.net
 *
 * @link http://www.phpinsider.com/php/code/GoogleMapAPI/
 * @copyright 2005 New Digital Group, Inc.
 * @author Monte Ohrt <monte at ohrt dot com>
 * @package GoogleMapAPI
 * @version 1.7
 */

/*

For best results with GoogleMaps, use XHTML compliant web pages with this header:

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">

*/

class GoogleMapAPI {

	/**
	 * YOUR GoogleMap API KEY for your site.
	 * (http://maps.google.com/apis/maps/signup.html)
	 *
	 * @var string
	 */
	var $api_key = '';

	/**
	 * API version of GoogleMap.
	 *
	 * @var integer
	 */
	var $api_version = 2;

	/**
	 * current map id, set when you instantiate
	 * the GoogleMapAPI object.
	 *
	 * @var string
	 */
	var $map_id = null;

	/**
	 * GoogleMapAPI uses the Yahoo geocode lookup API.
	 * This is the application ID for YOUR application.
	 * This is set upon instantiating the GoogleMapAPI object.
	 * (http://developer.yahoo.net/faq/index.html#appid)
	 *
	 * @var string
	 */
	var $app_id = null;

	/**
	 * map center latitude (horizontal)
	 * calculated automatically as markers
	 * are added to the map.
	 *
	 * @var float
	 */
	var $center_lat = null;

	/**
	 * map center longitude (vertical)
	 * calculated automatically as markers
	 * are added to the map.
	 *
	 * @var float
	 */
	var $center_lon = null;

	/**
	 * enables map controls (zoom/move/center)
	 *
	 * @var boolean
	 */
	var $map_controls = true;

	/**
	 * determines the map control type
	 * small -> show move/center controls
	 * large -> show move/center/zoom controls
	 *
	 * @var string
	 */
	var $control_size = 'large';

	/**
	 * enables map type controls (map/satellite/hybrid)
	 *
	 * @var boolean
	 */
	var $type_controls = true;

	/**
	 * default map type (G_MAP_TYPE/G_SATELLITE_TYPE/G_HYBRID_TYPE)
	 *
	 * @var boolean
	 */
	var $map_type = 'G_MAP_TYPE';

	/**
	 * determines the default zoom level
	 *
	 * @var integer
	 */
	var $zoom = 0;

	/**
	 * determines the map width
	 *
	 * @var integer
	 */
	var $width = '500px';

	/**
	 * determines the map height
	 *
	 * @var integer
	 */
	var $height = '500px';

	/**
	 * message that pops up when the browser is incompatible with Google Maps.
	 * set to empty string to disable.
	 *
	 * @var integer
	 */
	var $browser_alert = 'Sorry, the Google Maps API is not compatible with this browser.';

	/**
	 * message that appears when javascript is disabled.
	 * set to empty string to disable.
	 *
	 * @var string
	 */
	var $js_alert = '<b>Javascript must be enabled in order to use Google Maps.</b>';

	/**
	 * determines if to/from directions are included inside info window
	 *
	 * @var boolean
	 */
	var $directions = true;

	/**
	 * determines if map markers bring up an info window
	 *
	 * @var boolean
	 */
	var $info_window = true;

	/**
	 * determines if info window appears with a click or mouseover
	 *
	 * @var string click/mouseover
	 */
	var $window_trigger = 'click';

	/**
	 * what server geocode lookups come from
	 *
	 * available:	YAHOO	Yahoo! API. US geocode lookups only.
	 *				GOOGLE	Google Maps. This can do international lookups,
	 *						but not an official API service so use at your own risk.
	 *						No guarantees on the results :)
	 *
	 * @var string service name
	 */
	var $lookup_service = 'YAHOO';

	/**
	 * Text snippets for driving directions
	 * 
	 * 
	 * @var array
	 */
	var $driving_dir_text = array(
									'dir_text' => 'Directions: ',
									'dir_tohere' => 'To here',
									'dir_fromhere' => 'From here',
									'dir_to' => 'Start address: (include addr, city st/region)',
									'button_to' => 'Get Directions',
									'dir_from' => 'End address: (include addr, city st/region)',
									'button_from' => 'Get Directions',
									);

	/**
	 * version number
	 *
	 * @var string
	 */
	var $_version = '1.7';

	/**
	 * list of added markers
	 *
	 * @var array
	 */
	var $_markers = array();

	/**
	 * array of sidebar entries
	 *
	 * @var array
	 */
	var $_sidebar = array();

	/**
	 * maximum longitude of all markers
	 *
	 * @var float
	 */
	var $_max_lon = -1000000;

	/**
	 * minimum longitude of all markers
	 *
	 * @var float
	 */
	var $_min_lon = 1000000;

	/**
	 * max latitude
	 *
	 * @var float
	 */
	var $_max_lat = -1000000;

	/**
	 * min latitude
	 *
	 * @var float
	 */
	var $_min_lat = 1000000;

	/**
	 * determines if we should zoom to minimum level (above this->zoom value) that will encompass all markers
	 *
	 * @var boolean
	 */
	var $zoom_encompass = true;

	/**
	 * list of added polylines
	 *
	 * @var array
	 */
	var $_polylines = array();

	/**
	 * icon info array
	 *
	 * @var array
	 */
	var $_icons = array();

	/**
	 * class constructor
	 *
	 * @param string $map_id the id for this map
	 * @param string $app_id YOUR Yahoo App ID
	 */
	function GoogleMapAPI($map_id = 'map', $app_id = 'MyMapApp') {
		$this->map_id = 'map_' . $map_id;
		$this->app_id = $app_id;
	}

	/**
	 * sets YOUR Google Map API key
	 *
	 * @param string $key
	 */
	function setAPIKey($key) {
		$this->api_key = $key;
	}

	/**
	 * sets API version of GoogleMap
	 *
	 * @param integer $version
	 */
	function setAPIVersion($version) {
		$this->api_version = $version;
	}

	/**
	 * sets the width of the map
	 *
	 * @param string $width
	 */
	function setWidth($width) {
		if (!preg_match('!^(\d+)(.*)$!', $width, $_match)) {
			return false;
		}

		$_width = $_match[1];
		$_type = $_match[2];
		if ($_type == '%') {
			$this->width = $_width . '%';
		}
		else {
			$this->width = $_width . 'px';
		}

		return true;
	}

	/**
	 * sets the height of the map
	 *
	 * @param string $height
	 */
	function setHeight($height) {
		if (!preg_match('!^(\d+)(.*)$!', $height,$_match)) {
			return false;
		}

		$_height = $_match[1];
		$_type = $_match[2];
		if ($_type == '%') {
			$this->height = $_height . '%';
		}
		else {
			$this->height = $_height . 'px';
		}

		return true;
	}

	/**
	 * sets the default map zoom level
	 *
	 * @param string $level
	 */
	function setZoomLevel($level) {
		$this->zoom = (int) $level;
	}

	/**
	 * enables the map controls (zoom/move)
	 *
	 */
	function enableMapControls() {
		$this->map_controls = true;
	}

	/**
	 * disables the map controls (zoom/move)
	 *
	 */
	function disableMapControls() {
		$this->map_controls = false;
	}

	/**
	 * sets the map control size (large/small)
	 *
	 * @param string $size
	 */
	function setControlSize($size) {
		if (in_array($size, array('large', 'small'))) {
			$this->control_size = $size;
		}
	}

	/**
	 * enables the type controls (map/satellite/hybrid)
	 *
	 */
	function enableTypeControls() {
		$this->type_controls = true;
	}

	/**
	 * disables the type controls (map/satellite/hybrid)
	 *
	 */
	function disableTypeControls() {
		$this->type_controls = false;
	}

	/**
	 * set default map type (map/satellite/hybrid)
	 *
	 */
	function setMapType($type) {
		switch ($type) {
			case 'hybrid':
				$this->map_type = 'G_HYBRID_TYPE';
				break;
			case 'satellite':
				$this->map_type = 'G_SATELLITE_TYPE';
				break;
			case 'map':
			default:
				$this->map_type = 'G_MAP_TYPE';
				break;
		}
	}

	/**
	 * enables map directions inside info window
	 *
	 */
	function enableDirections() {
		$this->directions = true;
	}

	/**
	 * disables map directions inside info window
	 *
	 */
	function disableDirections() {
		$this->directions = false;
	}
	
	/**
	 * gets labels for driving directions
	 * 
	 */
	function getDirectionsText() {
		return $this->driving_dir_text;
	}

	/**
	 * sets labels for driving directions
	 * 
	 */
	function setDirectionsText($labels) {
		$this->driving_dir_text = $labels;
	}

	/**
	 * set browser alert message for incompatible browsers
	 *
	 * @params $message string
	 */
	function setBrowserAlert($message) {
		$this->browser_alert = $message;
	}

	/**
	 * set <noscript> message when javascript is disabled
	 *
	 * @params $message string
	 */
	function setJSAlert($message) {
		$this->js_alert = $message;
	}

	/**
	 * enable map marker info windows
	 */
	function enableInfoWindow() {
		$this->info_window = true;
	}

	/**
	 * disable map marker info windows
	 */
	function disableInfoWindow() {
		$this->info_window = false;
	}

	/**
	 * set the info window trigger action
	 *
	 * @params $message string click/mouseover
	 */
	function setInfoWindowTrigger($type) {
		switch ($type) {
			case 'mouseover':
				$this->window_trigger = 'mouseover';
				break;
			case 'click':
			default:
				$this->window_trigger = 'click';
				break;
		}
	}

	/**
	 * enable zoom to encompass makers
	 */
	function enableZoomEncompass() {
		$this->zoom_encompass = true;
	}

	/**
	 * disable zoom to encompass makers
	 */
	function disableZoomEncompass() {
		$this->zoom_encompass = false;
	}

	/**
	 * set the lookup service to use for geocode lookups
	 * default is YAHOO, you can also use GOOGLE.
	 * NOTE: GOOGLE can to intl lookups, but is not an
	 * official API, so use at your own risk.
	 *
	 */
	function setLookupService($service) {
		switch ($service) {
			case 'GOOGLE':
				$this->lookup_service = 'GOOGLE';
				break;
			case 'YAHOO':
			default:
				$this->lookup_service = 'YAHOO';
				break;
		}
	}

	/**
	 * adds a map marker by address
	 *
	 * @param string $address the map address to mark (street/city/state/zip)
	 * @param string $title the title display in the sidebar
	 * @param string $html the HTML block to display in the info bubble (if empty, title is used)
	 */
	function addMarkerByAddress($address, $title = '', $html = '') {
		if (($_geocode = $this->getGeocode($address)) === false) {
			return false;
		}
		$_marker['lon'] = $_geocode['lon'];
		$_marker['lat'] = $_geocode['lat'];
		$_marker['html'] = strlen($html) > 0 ? $html : $title;
		$_marker['title'] = $title;
		$this->_markers[] = $_marker;
		$this->adjustCenterCoords($_marker['lon'], $_marker['lat']);
		// return index of marker
		return count($this->_markers) - 1;
	}

	/**
	 * adds a map marker by geocode
	 *
	 * @param string $lon the map longitude (horizontal)
	 * @param string $lat the map latitude (vertical)
	 * @param string $title the title display in the sidebar
	 * @param string $html the HTML block to display in the info bubble (if empty, title is used)
	 */
	function addMarkerByCoords($lon, $lat, $title = '', $html = '') {
		$_marker['lon'] = $lon;
		$_marker['lat'] = $lat;
		$_marker['html'] = strlen($html) > 0 ? $html : $title;
		$_marker['title'] = $title;
		$this->_markers[] = $_marker;
		$this->adjustCenterCoords($_marker['lon'], $_marker['lat']);
		// return index of marker
		return count($this->_markers) - 1;
	}

	/**
	 * adds a map polyline by address
	 * if color, weight and opacity are not defined, use the google maps defaults
	 *
	 * @param string $address1 the map address to draw from
	 * @param string $address2 the map address to draw to
	 * @param string $color the color of the line (format: #000000)
	 * @param string $weight the weight of the line in pixels
	 * @param string $opacity the line opacity (percentage)
	 */
	function addPolyLineByAddress($address1, $address2, $color = '', $weight = 0, $opacity = 0) {
		if (($_geocode1 = $this->getGeocode($address1)) === false) {
			return false;
		}
		if (($_geocode2 = $this->getGeocode($address2)) === false) {
			return false;
		}
		$_polyline['lon1'] = $_geocode1['lon'];
		$_polyline['lat1'] = $_geocode1['lat'];
		$_polyline['lon2'] = $_geocode2['lon'];
		$_polyline['lat2'] = $_geocode2['lat'];
		$_polyline['color'] = $color;
		$_polyline['weight'] = $weight;
		$_polyline['opacity'] = $opacity;
		$this->_polylines[] = $_polyline;
		$this->adjustCenterCoords($_polyline['lon1'], $_polyline['lat1']);
		$this->adjustCenterCoords($_polyline['lon2'], $_polyline['lat2']);
		// return index of polylines
		return count($this->_polylines) - 1;
	}

	/**
	 * adds a map polyline by map coordinates
	 * if color, weight and opacity are not defined, use the google maps defaults
	 *
	 * @param string $lon1 the map longitude to draw from
	 * @param string $lat1 the map latitude to draw from
	 * @param string $lon2 the map longitude to draw to
	 * @param string $lat2 the map latitude to draw to
	 * @param string $color the color of the line (format: #000000)
	 * @param string $weight the weight of the line in pixels
	 * @param string $opacity the line opacity (percentage)
	 */
	function addPolyLineByCoords($lon1, $lat1, $lon2, $lat2, $color = '', $weight = 0, $opacity = 0) {
		$_polyline['lon1'] = $lon1;
		$_polyline['lat1'] = $lat1;
		$_polyline['lon2'] = $lon2;
		$_polyline['lat2'] = $lat2;
		$_polyline['color'] = $color;
		$_polyline['weight'] = $weight;
		$_polyline['opacity'] = $opacity;
		$this->_polylines[] = $_polyline;
		$this->adjustCenterCoords($_polyline['lon1'], $_polyline['lat1']);
		$this->adjustCenterCoords($_polyline['lon2'], $_polyline['lat2']);
		// return index of polylines
		return count($this->_polylines) - 1;
	}

	/**
	 * adjust map center coordinates by the given lat/lon point
	 *
	 * @param string $lon the map latitude (horizontal)
	 * @param string $lat the map latitude (vertical)
	 */
	function adjustCenterCoords($lon, $lat) {
		if (strlen((string)$lon) == 0 || strlen((string)$lat) == 0) {
			return false;
		}
		$this->_max_lon = max($lon, $this->_max_lon);
		$this->_min_lon = min($lon, $this->_min_lon);
		$this->_max_lat = max($lat, $this->_max_lat);
		$this->_min_lat = min($lat, $this->_min_lat);

		// increase bounds by fudge factor to keep
		// markers away from the edges
		$_len_lon = $this->_max_lon - $this->_min_lon;
		$_len_lat = $this->_max_lat - $this->_min_lat;
		$this->_min_lon -= $_len_lon * 0.02;
		$this->_max_lon += $_len_lon * 0.02;
		$this->_min_lat -= $_len_lat * 0.02;
		$this->_max_lat += $_len_lat * 0.02;

		$this->center_lon = ($this->_min_lon + $this->_max_lon) / 2;
		$this->center_lat = ($this->_min_lat + $this->_max_lat) / 2;
		return true;
	}

	/**
	 * set map center coordinates to lat/lon point
	 *
	 * @param string $lon the map latitude (horizontal)
	 * @param string $lat the map latitude (vertical)
	 */
	function setCenterCoords($lon, $lat) {
		$this->center_lat = (float) $lat;
		$this->center_lon = (float) $lon;
	}

	/**
	 * generate an array of params for a new marker icon image
	 * iconShadowImage is optional
	 * If anchor coords are not supplied, we use the center point of the image by default.
	 * Can be called statically. For private use by addMarkerIcon() and setMarkerIcon()
	 *
	 * @param string $iconImage URL to icon image
	 * @param string $iconShadowImage URL to shadow image
	 * @param string $iconAnchorX X coordinate for icon anchor point
	 * @param string $iconAnchorY Y coordinate for icon anchor point
	 * @param string $infoWindowAnchorX X coordinate for info window anchor point
	 * @param string $infoWindowAnchorY Y coordinate for info window anchor point
	 */
	function createMarkerIcon($iconImage, $iconShadowImage = '', $iconAnchorX = 'x', $iconAnchorY = 'x', $infoWindowAnchorX = 'x', $infoWindowAnchorY = 'x') {
		$_icon_image_path = strpos($iconImage,'http') === 0 ? $iconImage : $_SERVER['DOCUMENT_ROOT'] . $iconImage;
		if (!($_image_info = @getimagesize($_icon_image_path))) {
			die('GoogleMapAPI:createMarkerIcon: Error reading image: ' . $iconImage);
		}
		if ($iconShadowImage) {
			$_shadow_image_path = strpos($iconShadowImage,'http') === 0 ? $iconShadowImage : $_SERVER['DOCUMENT_ROOT'] . $iconShadowImage;
			if (!($_shadow_info = @getimagesize($_shadow_image_path))) {
				die('GoogleMapAPI:createMarkerIcon: Error reading image: ' . $iconShadowImage);
			}
		}

		if ($iconAnchorX === 'x') {
			$iconAnchorX = (int) ($_image_info[0] / 2);
		}
		if ($iconAnchorY === 'x') {
			$iconAnchorY = (int) ($_image_info[1] / 2);
		}
		if ($infoWindowAnchorX === 'x') {
			$infoWindowAnchorX = (int) ($_image_info[0] / 2);
		}
		if ($infoWindowAnchorY === 'x') {
			$infoWindowAnchorY = (int) ($_image_info[1] / 2);
		}

		$icon_info = array(
							'image' => $iconImage,
							'iconWidth' => $_image_info[0],
							'iconHeight' => $_image_info[1],
							'iconAnchorX' => $iconAnchorX,
							'iconAnchorY' => $iconAnchorY,
							'infoWindowAnchorX' => $infoWindowAnchorX,
							'infoWindowAnchorY' => $infoWindowAnchorY
							);
		if ($iconShadowImage) {
			$icon_info = array_merge($icon_info, array(
														'shadow' => $iconShadowImage,
														'shadowWidth' => $_shadow_info[0],
														'shadowHeight' => $_shadow_info[1])
														);
		}
		return $icon_info;
	}

	/**
	 * set the marker icon for ALL markers on the map
	 */
	function setMarkerIcon($iconImage, $iconShadowImage = '', $iconAnchorX = 'x', $iconAnchorY = 'x', $infoWindowAnchorX = 'x', $infoWindowAnchorY = 'x') {
		$this->_icons = array($this->createMarkerIcon($iconImage, $iconShadowImage, $iconAnchorX, $iconAnchorY, $infoWindowAnchorX, $infoWindowAnchorY));
	}

	/**
	 * add an icon to go with the correspondingly added marker
	 */
	function addMarkerIcon($iconImage, $iconShadowImage = '', $iconAnchorX = 'x', $iconAnchorY = 'x', $infoWindowAnchorX = 'x', $infoWindowAnchorY = 'x') {
		$this->_icons[] = $this->createMarkerIcon($iconImage, $iconShadowImage, $iconAnchorX, $iconAnchorY, $infoWindowAnchorX, $infoWindowAnchorY);
		return count($this->_icons) - 1;
	}

	/**
	 * return Google API header javascript (goes between <head></head>)
	 *
	 */
	function getGoogleApiJS() {
		return sprintf('<script src="http://maps.google.com/maps?file=api&v=%d&key=%s" type="text/javascript" charset="utf-8"></script>', $this->api_version, $this->api_key);
	}

	/**
	 * return map header javascript (goes between <head></head>)
	 *
	 */
	function getMapHeaderJS() {
		$_output = '<script type="text/javascript">' . "\n";
		$_output .= '/*<![CDATA[*/' . "\n";
		$_output .= '<!--' . "\n";

		$_output .= '/*************************************************' . "\n";
		$_output .= ' * Created with GoogleMapAPI ' . $this->_version . "\n";
		$_output .= ' * Author: Monte Ohrt (monte AT ohrt DOT com)' . "\n";
		$_output .= ' * Copyright 2005-2006 New Digital Group' . "\n";
		$_output .= ' * http://www.phpinsider.com/php/code/GoogleMapAPI/' . "\n";
		$_output .= ' *************************************************/' . "\n";
		$_output .= "\n";

		$_output .= 'var points = [];' . "\n";
		$_output .= 'var markers = [];' . "\n";
		$_output .= 'var counter = 0;' . "\n";

		// set marker array for sidebar
		$_output .= 'var marker_html = [];' . "\n";

		// set directions
		if ($this->directions) {
			$_output .= 'var to_htmls = [];' . "\n";
			$_output .= 'var from_htmls = [];' . "\n";
		}

		// set icons
		if (!empty($this->_icons)) {
			$_output .= 'var icon = Array();' . "\n";
			for ($i = 0; $this->_icons[$i]; $i++) {
				$info = $this->_icons[$i];

				// let's get fancy: we hash the icon data and see if we've already got this one; if so, save some javascript!
				$icon_key = md5(serialize($info));
				if (!is_numeric($exist_icn[$icon_key])) {
					$exist_icn[$icon_key] = $i;

					$_output .= "icon[$i] = new GIcon();\n";
					$_output .= sprintf('icon[%s].image = "%s";', $i, $info['image']) . "\n";
					if ($info['shadow']) {
						$_output .= sprintf('icon[%s].shadow = "%s";', $i, $info['shadow']) . "\n";
						$_output .= sprintf('icon[%s].shadowSize = new GSize(%s, %s);', $i, $info['shadowWidth'], $info['shadowHeight']) . "\n";
					}
					$_output .= sprintf('icon[%s].iconSize = new GSize(%s, %s);', $i, $info['iconWidth'], $info['iconHeight']) . "\n";
					$_output .= sprintf('icon[%s].iconAnchor = new GPoint(%s, %s);', $i, $info['iconAnchorX'], $info['iconAnchorY']) . "\n";
					$_output .= sprintf('icon[%s].infoWindowAnchor = new GPoint(%s, %s);', $i, $info['infoWindowAnchorX'], $info['infoWindowAnchorY']) . "\n";
				}
				else {
					$_output .= "icon[$i] = icon[$exist_icn[$icon_key]];\n";
				}
			}
		}

		$_output .= 'var map = null;' . "\n";

		$_output .= "\n";

		// function for setting marker points
		$_output .= 'function createMarker(point, title, html, n) {' . "\n";

		// create marker object
		$_output .= 'if (n >= '. sizeof($this->_icons) .') {' . "\n";
		$_output .= 'n = '. (sizeof($this->_icons) - 1) .';' . "\n";
		$_output .= '}' . "\n";
		if (!empty($this->_icons)) {
			$_output .= 'var marker = new GMarker(point, icon[n]);' . "\n";
		}
		else {
			$_output .= 'var marker = new GMarker(point);' . "\n";
		}

		// show directions
		if ($this->directions) {
			$_output .= 'to_htmls[counter] = html;' . "\n";
			$_output .= "to_htmls[counter] += '<form class=\"gmapDir\" id=\"gmapDirTo\" style=\"white-space: nowrap;\" action=\"http://maps.google.com/maps\" method=\"get\" target=\"_blank\">';" . "\n";
			$_output .= sprintf("to_htmls[counter] += '<p class=\"gmapDirHead\" id=\"gmapDirHeadTo\">%s<strong>%s</strong> - <a href=\"javascript:fromhere(' + counter + ')\">%s</a></p>';", $this->driving_dir_text['dir_text'], $this->driving_dir_text['dir_tohere'], $this->driving_dir_text['dir_fromhere']) . "\n";
			$_output .= sprintf("to_htmls[counter] += '<p class=\"gmapDirItem\" id=\"gmapDirItemTo\"><label for=\"gmapDirSaddr\" class=\"gmapDirLabel\" id=\"gmapDirLabelTo\">%s<br /></label><input type=\"text\" size=\"40\" maxlength=\"40\" name=\"saddr\" class=\"gmapTextBox\" id=\"gmapDirSaddr\" value=\"\" onfocus=\"this.style.backgroundColor = \'#e0e0e0\';\" onblur=\"this.style.backgroundColor = \'#ffffff\';\" /></p>';", $this->driving_dir_text['dir_to']) . "\n";
			$_output .= sprintf("to_htmls[counter] += '<p class=\"gmapDirBtns\" id=\"gmapDirBtnsTo\"><input value=\"%s\" type=\"submit\" class=\"gmapDirButton\" id=\"gmapDirButtonTo\" /></p>';", $this->driving_dir_text['button_to']) . "\n";

			$_output .= "to_htmls[counter] += '<input type=\"hidden\" name=\"daddr\" value=\"' + point.y + ', ' + point.x + '(' + title + ')' + '\" /></form>';" . "\n";

			$_output .= "\n";

			$_output .= "from_htmls[counter] = html;" . "\n";
			$_output .= "from_htmls[counter] += '<form class=\"gmapDir\" id=\"gmapDirFrom\" style=\"white-space: nowrap;\" action=\"http://maps.google.com/maps\" method=\"get\" target=\"_blank\">';" . "\n";
			$_output .= sprintf("from_htmls[counter] += '<p class=\"gmapDirHead\" id=\"gmapDirHeadFrom\">%s<a href=\"javascript:tohere(' + counter + ')\">%s</a> - <strong>%s</strong></p>';", $this->driving_dir_text['dir_text'], $this->driving_dir_text['dir_tohere'], $this->driving_dir_text['dir_fromhere']) . "\n";
			$_output .= sprintf("from_htmls[counter] += '<p class=\"gmapDirItem\" id=\"gmapDirItemFrom\"><label for=\"gmapDirSaddr\" class=\"gmapDirLabel\" id=\"gmapDirLabelFrom\">%s<br /></label><input type=\"text\" size=\"40\" maxlength=\"40\" name=\"saddr\" class=\"gmapTextBox\" id=\"gmapDirSaddr\" value=\"\" onfocus=\"this.style.backgroundColor = \'#e0e0e0\';\" onblur=\"this.style.backgroundColor = \'#ffffff\';\" /></p>';", $this->driving_dir_text['dir_from']) . "\n";
			$_output .= sprintf("from_htmls[counter] += '<p class=\"gmapDirBtns\" id=\"gmapDirBtnsFrom\"><input value=\"%s\" type=\"submit\" class=\"gmapDirButton\" id=\"gmapDirButtonFrom\" /></p>';", $this->driving_dir_text['button_from']) . "\n";

			$_output .= "from_htmls[counter] += '<input type=\"hidden\" name=\"daddr\" value=\"' + point.y + ',' + point.x + '(' + title + ')' + '\" /></form>';" . "\n";

			$_output .= "\n";

			$_output .= sprintf("html = html + '<br /><div id=\"gmapDirHead\" class=\"gmapDir\" style=\"white-space: nowrap;\">%s<a href=\"javascript:tohere(' + counter + ')\">%s</a> - <a href=\"javascript:fromhere(' + counter + ')\">%s</a></div>';", $this->driving_dir_text['dir_text'], $this->driving_dir_text['dir_tohere'], $this->driving_dir_text['dir_fromhere']) . "\n";
		}

		// enable info window
		if ($this->info_window) {
			$_output .= sprintf('GEvent.addListener(marker, "%s", function() { marker.openInfoWindowHtml(html); });',$this->window_trigger) . "\n";
		}
		$_output .= 'points[counter] = point;' . "\n";
		$_output .= 'markers[counter] = marker;' . "\n";

		// fill marker array for sidebar
		$_output .= 'marker_html[counter] = html;' . "\n";

		$_output .= 'counter++;' . "\n";
		$_output .= 'return marker;' . "\n";

		$_output .= '}' . "\n";

		$_output .= "\n";

		// write function for sidebar
		$_output .= 'function click_sidebar(idx) {' . "\n";
		$_output .= 'markers[idx].openInfoWindowHtml(marker_html[idx]);' . "\n";
		$_output .= '}' . "\n";

		$_output .= "\n";

		// write function for info window
		$_output .= 'function showInfoWindow(idx,html) {' . "\n";
		$_output .= 'map.centerAtLatLng(points[idx]);' . "\n";
		$_output .= 'markers[idx].openInfoWindowHtml(html);' . "\n";
		$_output .= '}' . "\n";

		$_output .= "\n";

		// write functions for directions
		if ($this->directions) {
			$_output .= 'function tohere(idx) {' . "\n";
			$_output .= 'markers[idx].openInfoWindowHtml(to_htmls[idx]);' . "\n";
			$_output .= '}' . "\n";
			$_output .= "\n";
			$_output .= 'function fromhere(idx) {' . "\n";
			$_output .= 'markers[idx].openInfoWindowHtml(from_htmls[idx]);' . "\n";
			$_output .= '}' . "\n";
			$_output .= "\n";
		}

		$_output .= '//-->' . "\n";
		$_output .= '/*]]>*/' . "\n";
		$_output .= '</script>' . "\n";

		return $_output;
	}

	/**
	 * return map header javascript (goes between <head></head>)
	 *
	 */
	function getHeaderJS() {
		$_output = $this->getGoogleApiJS() . "\n";
		$_output .= $this->getMapHeaderJS();
		return $_output;
	}

	/**
	 * return map javascript
	 *
	 */
	function getMapJS() {
		$_output = '<script type="text/javascript">' . "\n";
		$_output .= '/*<![CDATA[*/' . "\n";
		$_output .= '<!--' . "\n";

		$_output .= '/*************************************************' . "\n";
		$_output .= ' * Created with GoogleMapAPI ' . $this->_version . "\n";
		$_output .= ' * Author: Monte Ohrt (monte AT ohrt DOT com)' . "\n";
		$_output .= ' * Copyright 2005-2006 New Digital Group' . "\n";
		$_output .= ' * http://www.phpinsider.com/php/code/GoogleMapAPI/' . "\n";
		$_output .= ' *************************************************/' . "\n";
		$_output .= "\n";

		// print function header for map drawing function
		$_output .= sprintf('function draw_%s() {', $this->map_id) . "\n";

		if (!empty($this->browser_alert)) {
			$_output .= 'if (GBrowserIsCompatible()) {' . "\n";
		}

		// create new map object
		$_output .= sprintf('var mapObj = document.getElementById("%s");',$this->map_id) . "\n";
		$_output .= 'if (mapObj != "undefined" && mapObj != null) {' . "\n";
		$_output .= sprintf('map = new GMap(document.getElementById("%s"));',$this->map_id) . "\n";

		// center and zoom map
		if (isset($this->center_lat) && isset($this->center_lon)) {
			$_output .= sprintf('map.centerAndZoom(new GPoint(%s, %s), %s);', $this->center_lon, $this->center_lat, $this->zoom) . "\n";
		}

		// zoom out until all markers are in the viewport
		if ($this->zoom_encompass && count($this->_markers) > 0) {
			$_output .= 'var bds = map.getBoundsLatLng();' . "\n";
			$_output .= 'for (var z = ' . $this->zoom . '; bds.minX > ' . $this->_min_lon  . '|| bds.minY > ' . $this->_min_lat . '|| bds.maxX < ' . $this->_max_lon . '|| bds.maxY < ' . $this->_max_lat . '; z++) {' . "\n";
			$_output .= 'map.zoomTo(z);' . "\n";
			$_output .= 'bds = map.getBoundsLatLng();' . "\n";
			$_output .= '}' . "\n";
		}

		// set map controls
		if ($this->map_controls) {
			if ($this->control_size == 'large') {
				$_output .= 'map.addControl(new GLargeMapControl());' . "\n";
			}
			else {
				$_output .= 'map.addControl(new GSmallMapControl());' . "\n";
			}
		}

		// set type controls
		if ($this->type_controls) {
			$_output .= 'map.addControl(new GMapTypeControl());' . "\n";
		}

		// set map type
		$_output .= sprintf('map.setMapType(%s);',$this->map_type) . "\n";

		// create and set marker
		$i = 0;
		foreach($this->_markers as $_marker) {
			$_output .= sprintf('var point = new GPoint(%s, %s);', $_marker['lon'], $_marker['lat']) . "\n";

			$title = str_replace("'", "\'", $_marker['title']);
			$title = str_replace("\"", "&quot;", $title);
			$html = str_replace("'", "\'", $_marker['html']);

			$_output .= sprintf("var marker = createMarker(point, '%s', '%s', %s);\n", $title, "<div id=\"gmapmarker\" style=\"white-space: nowrap;\">" . $html . "</div>", $i);

			$_output .= 'map.addOverlay(marker);' . "\n";

			// add entry to sidebar array
			$this->_sidebar[$i] = array('click_sidebar(' . $i . ');', $title);

			$i++;
		}

		// create and set polylines
		foreach($this->_polylines as $_polyline) {
			$_output .= sprintf('var polyline = new GPolyline([new GPoint(%s, %s), new GPoint(%s, %s)], "%s", %s, %s);', $_polyline['lon1'], $_polyline['lat1'], $_polyline['lon2'], $_polyline['lat2'], $_polyline['color'], $_polyline['weight'], $_polyline['opacity'] / 100.0) . "\n";
			$_output .= 'map.addOverlay(polyline);' . "\n";
		}

		$_output .= '}' . "\n";

		if (!empty($this->browser_alert)) {
			$_output .= '}' . "\n";
			$_output .= 'else {' . "\n";
			$_output .= 'alert("' . $this->browser_alert . '");' . "\n";
			$_output .= '}' . "\n";
		}

		// close map drawing function
		$_output .= '}' . "\n";

		$_output .= '//-->' . "\n";
		$_output .= '/*]]>*/' . "\n";
		$_output .= '</script>' . "\n";

		return $_output;
	}

	/**
	 * return map
	 *
	 */
	function getMap() {
		$_output = '<script type="text/javascript">' . "\n";
		$_output .= '/*<![CDATA[*/' . "\n";
		$_output .= '<!--' . "\n";
		$_output .= 'if (GBrowserIsCompatible()) {' . "\n";
		if (strlen($this->width) > 0 && strlen($this->height) > 0) {
			$_output .= sprintf('document.write(\'<div id="%s" style="width: %s; height: %s"></div>\');', $this->map_id, $this->width, $this->height) . "\n";
		}
		else {
			$_output .= sprintf('document.write(\'<div id="%s"></div>\');', $this->map_id) . "\n";
		}
		$_output .= sprintf("setTimeout('draw_%s()', 500);", $this->map_id) . "\n";
		$_output .= '}';

		if (!empty($this->js_alert)) {
			$_output .= "\n";
			$_output .= 'else {' . "\n";
			$_output .= sprintf('document.write(\'%s\');', $this->js_alert) . "\n";
			$_output .= '}' . "\n";
		}

		$_output .= '// -->' . "\n";
		$_output .= '/*]]>*/' . "\n";
		$_output .= '</script>' . "\n";

		if (!empty($this->js_alert)) {
			$_output .= '<noscript>' . $this->js_alert . '</noscript>' . "\n";
		}

		return $_output;
	}

	/**
	 * return sidebar items
	 *
	 */
	function getSidebarItems() {
		return $this->_sidebar;
	}

	/**
	 * get geocode lat/lon points for given address from Yahoo
	 *
	 * @param string $address
	 */
	function geoGetCoords($address) {

		switch ($this->lookup_service) {

			case 'GOOGLE':

				$_url = 'http://maps.google.com/maps?output=js&q=';
				$_url .= sprintf('?output=js&q=%s',rawurlencode($address));

				$_result = false;

				if ($_result = $this->fetchURL($_url)) {

					if (strpos($_result,'errortips') > 1 || strpos($_result,'Did you mean:') !== false) {
						// didn't get a good result
						return false;
					}

					preg_match('!center:\s*{lat:\s*(-?\d+\.\d+),lng:\s*(-?\d+\.\d+)}!U', $_result, $_match);

					$_coords['lon'] = $_match[2];
					$_coords['lat'] = $_match[1];

				}

				break;

			case 'YAHOO':
			default:

				$_url = 'http://api.local.yahoo.com/MapsService/V1/geocode';
				$_url .= sprintf('?appid=%s&location=%s', $this->app_id, rawurlencode($address));

				$_result = false;

				if ($_result = $this->fetchURL($_url)) {

					preg_match('!<Latitude>(.*)</Latitude><Longitude>(.*)</Longitude>!U', $_result, $_match);

					$_coords['lon'] = $_match[2];
					$_coords['lat'] = $_match[1];

				}

				break;
		}

		return $_coords;
	}

	/**
	 * fetch a URL. Override this method to change the way URLs are fetched.
	 *
	 * @param string $url
	 */
	function fetchURL($url) {
		return file_get_contents($url);
	}

}

?>
