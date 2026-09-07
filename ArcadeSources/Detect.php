<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

if (!defined('SMF'))
   die('Hacking attempt...');

/*!
 * Developed by Kevin Warren, https://twitter.com/KevinTWarren
 *
 * Released under the MIT license
 * http://opensource.org/licenses/MIT
 *
 * Detect Device 1.0.5
 *
 * Last Modification Date: 26/01/2019
 */
class Detect {
    const VERSION = '1.0.5';

	private static $ipAddress = null;
	private static $ipUrl = null;
	private static $ipInfo = null;
	private static $ipInfoError = false;
	private static $ipInfoSource = null;
	private static $ipInfoHostname = null;
	private static $ipInfoOrg = null;
	private static $ipInfoCountry = null;
	#private static $ipInfoLatitude = null;
	#private static $ipInfoLongitude = null;
	#private static $ipInfoAddress = null;
	private static $detect = null;

	public static function init() {
		self::$detect = new Mobile_Detect();
		self::$detect->setDetectionType(Mobile_Detect::DETECTION_TYPE_EXTENDED);
		self::getIp();
	}

    public static function getScriptVersion() {
        return self::VERSION;
    }

	private static function getIp() {
		if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])) { self::$ipAddress = $_SERVER['HTTP_CLIENT_IP']; }
		elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { self::$ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR']; }
		else { self::$ipAddress = $_SERVER['REMOTE_ADDR']; }
		if (in_array(self::$ipAddress, array('::1', '127.0.0.1', 'localhost'))) {
			self::$ipAddress = 'localhost';
			self::$ipUrl = '';
		} else {
			self::$ipUrl = '/' . self::$ipAddress;
		}
	}

	public static function isMobile() {
		return self::$detect->isMobile();
	}

	public static function isTablet() {
		return self::$detect->isTablet();
	}

	public static function isPhone() {
		return (self::$detect->isMobile() ? (self::$detect->isTablet() ? false : true) : false);
	}

	public static function isComputer() {
		return (self::$detect->isMobile() ? false : true);
	}

	public static function deviceType() {
		return (self::$detect->isMobile() ? (self::$detect->isTablet() ? 'Tablet' : 'Phone') : 'Computer');
	}

	public static function version($var) {
		return self::$detect->version($var);
	}

	public static function isEdge() {
		$agent = $_SERVER['HTTP_USER_AGENT'];
		if (preg_match('/Edge\/\d+/', $agent)) {
			return true;
		} else {
			return false;
		}
	}

	public static function __callStatic($name, $arguments) {
		if (substr($name, 0, 2) != 'is') {
			$trace = current(debug_backtrace());
            Debug::error('No such method exists: ' . $name, $trace);
			return null;
		} else {
			return self::$detect->{$name}();
		}
	}

	public static function brand() {
		#$agent = $_SERVER['HTTP_USER_AGENT'];
		$brand = 'Unknown Brand';
		switch (self::deviceType()) {
		case 'Phone':
			foreach(self::$detect->getPhoneDevices() as $name => $regex) {
				$check = self::$detect->{'is'.$name}();
				if ($check !== false) { $brand = $name; }
			}
			return $brand;
		case 'Tablet':
			foreach(self::$detect->getTabletDevices() as $name => $regex) {
				$check = self::$detect->{'is'.$name}();
				if ($check !== false) { $brand = str_replace('Tablet', '', $name); }
			}
			return $brand;
			break;
		case 'Computer':
			return $brand;
			break;
		}
	}

	public static function os() {
		#self::$getOperatingSystems();
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$version = '';
		$codeName = '';
		$os = 'Unknown OS';
		foreach(self::$detect->getOperatingSystems() as $name => $regex) {
			$check = self::$detect->version($name);
			if ($check !== false) { $os = $name . ' ' . $check; }
			break;
		}
		if (self::$detect->isAndroidOS()) {
			if (self::$detect->version('Android') !== false) {
				$version = ' ' . self::$detect->version('Android');
				switch (true) {
          case self::$detect->version('Android') >= 9.0: $codeName = ' (Pie)'; break;
          case self::$detect->version('Android') >= 8.0: $codeName = ' (Oreo)'; break;
					case self::$detect->version('Android') >= 7.0: $codeName = ' (Nougat)'; break;
          case self::$detect->version('Android') >= 6.0: $codeName = ' (Marshmallow)'; break;
          case self::$detect->version('Android') >= 5.0: $codeName = ' (Lollipop)'; break;
					case self::$detect->version('Android') >= 4.4: $codeName = ' (KitKat)'; break;
					case self::$detect->version('Android') >= 4.1: $codeName = ' (Jelly Bean)'; break;
					case self::$detect->version('Android') >= 4.0: $codeName = ' (Ice Cream Sandwich)'; break;
					case self::$detect->version('Android') >= 3.0: $codeName = ' (Honeycomb)'; break;
					case self::$detect->version('Android') >= 2.3: $codeName = ' (Gingerbread)'; break;
					case self::$detect->version('Android') >= 2.2: $codeName = ' (Froyo)'; break;
					case self::$detect->version('Android') >= 2.0: $codeName = ' (Eclair)'; break;
					case self::$detect->version('Android') >= 1.6: $codeName = ' (Donut)'; break;
					case self::$detect->version('Android') >= 1.5: $codeName = ' (Cupcake)'; break;
					default: $codeName = ''; break;
				}
			}
			$os = 'Android' . $version . $codeName;
		} elseif (preg_match('/Linux/', $agent)) {
			$os = 'Linux';
		} elseif (preg_match('/Mac OS X/', $agent)) {
      if (preg_match('/Mac OS X 10_14/', $agent) || preg_match('/Mac OS X 10.14/', $agent)) {
				$os = 'OS X (Mojave)';
			} elseif (preg_match('/Mac OS X 10_13/', $agent) || preg_match('/Mac OS X 10.13/', $agent)) {
				$os = 'OS X (High Sierra)';
			} elseif (preg_match('/Mac OS X 10_12/', $agent) || preg_match('/Mac OS X 10.12/', $agent)) {
				$os = 'OS X (Sierra)';
			} elseif (preg_match('/Mac OS X 10_11/', $agent) || preg_match('/Mac OS X 10.11/', $agent)) {
				$os = 'OS X (El Capitan)';
			} elseif (preg_match('/Mac OS X 10_10/', $agent) || preg_match('/Mac OS X 10.10/', $agent)) {
				$os = 'OS X (Yosemite)';
			} elseif (preg_match('/Mac OS X 10_9/', $agent) || preg_match('/Mac OS X 10.9/', $agent)) {
				$os = 'OS X (Mavericks)';
			} elseif (preg_match('/Mac OS X 10_8/', $agent) || preg_match('/Mac OS X 10.8/', $agent)) {
				$os = 'OS X (Mountain Lion)';
			} elseif (preg_match('/Mac OS X 10_7/', $agent) || preg_match('/Mac OS X 10.7/', $agent)) {
				$os = 'Mac OS X (Lion)';
			} elseif (preg_match('/Mac OS X 10_6/', $agent) || preg_match('/Mac OS X 10.6/', $agent)) {
				$os = 'Mac OS X (Snow Leopard)';
			} elseif (preg_match('/Mac OS X 10_5/', $agent) || preg_match('/Mac OS X 10.5/', $agent)) {
				$os = 'Mac OS X (Leopard)';
			} elseif (preg_match('/Mac OS X 10_4/', $agent) || preg_match('/Mac OS X 10.4/', $agent)) {
				$os = 'Mac OS X (Tiger)';
			} elseif (preg_match('/Mac OS X 10_3/', $agent) || preg_match('/Mac OS X 10.3/', $agent)) {
				$os = 'Mac OS X (Panther)';
			} elseif (preg_match('/Mac OS X 10_2/', $agent) || preg_match('/Mac OS X 10.2/', $agent)) {
				$os = 'Mac OS X (Jaguar)';
			} elseif (preg_match('/Mac OS X 10_1/', $agent) || preg_match('/Mac OS X 10.1/', $agent)) {
				$os = 'Mac OS X (Puma)';
			} elseif (preg_match('/Mac OS X 10/', $agent)) {
				$os = 'Mac OS X (Cheetah)';
			}
		} elseif (self::$detect->isWindowsPhoneOS()) {
			//$icon = 'windowsphone8';
			if (self::$detect->version('WindowsPhone') !== false) {
				$version = ' ' . self::$detect->version('WindowsPhoneOS');
				/*switch (true) {
					case $version >= 8: $icon = 'windowsphone8'; break;
					case $version >= 7: $icon = 'windowsphone7'; break;
					default: $icon = 'windowsphone8'; break;
				}*/
			}
			$os = 'Windows Phone' . $version;
		} elseif (self::$detect->version('Windows NT') !== false) {
			switch (self::$detect->version('Windows NT')) {
				case 10.0: $codeName = ' 10'; break;
				case 6.3: $codeName = ' 8.1'; break;
				case 6.2: $codeName = ' 8'; break;
				case 6.1: $codeName = ' 7'; break;
				case 6.0: $codeName = ' Vista'; break;
				case 5.2: $codeName = ' Server 2003; Windows XP x64 Edition'; break;
				case 5.1: $codeName = ' XP'; break;
				case 5.01: $codeName = ' 2000, Service Pack 1 (SP1)'; break;
				case 5.0: $codeName = ' 2000'; break;
				case 4.0: $codeName = ' NT 4.0'; break;
				default: $codeName = ' NT v' . self::$detect->version('Windows NT'); break;
			}
			$os = 'Windows' . $codeName;
		} elseif (self::$detect->isiOS()) {
            if (self::$detect->isTablet()) {
                $version = ' ' . self::$detect->version('iPad');
            } else {
                $version = ' ' . self::$detect->version('iPhone');
            }
            $os = 'iOS' . $version;
        }
		return $os;

	}

	public static function browser() {
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$browser = 'Unknown Browser';
		if (preg_match('/Edge\/\d+/', $agent)) {
			#$browser = 'Microsoft Edge ' . (floatval(self::$detect->version('Edge')) + 8);
			$browser = 'Microsoft Edge ' . str_replace('12', '20', self::$detect->version('Edge'));
		} elseif (self::$detect->version('Trident') !== false && preg_match('/rv:11.0/', $agent)) {
			$browser = 'Internet Explorer 11';
		} else {
			$found = false;
			foreach(self::$detect->getBrowsers() as $name => $regex) {
				$check = self::$detect->version($name);
				if ($check !== false && !$found) {
					$browser = $name . ' ' . $check;
					$found = true;
				}
			}
		}
		return $browser;
	}

	public static function ieOld($prependHTML = '', $appendHTML = '') {
		$ieCountdownHTML = '';
		if (self::$detect->version('IE') !== false && self::$detect->version('IE') <= 9) {
			$ieCountdownHTML = $prependHTML . '<strong>YOU ARE USING AN OUTDATED BROWSER</strong><br>It is limiting your experience.<br>Please upgrade your browser.' . $appendHTML;
		}
		return $ieCountdownHTML;
	}

	public static function ip() {
		if (self::$ipAddress == 'localhost' && is_null(self::$ipInfo) && !self::$ipInfoError) { self::getIpInfo(); }
		return self::$ipAddress;
	}

	private static function getIpInfo() {
		try {
			self::$ipInfo = json_decode(file_get_contents('http://ipinfo.io' . self::$ipUrl . '/json'));
			self::$ipAddress = self::$ipInfo->ip;
            if (isset(self::$ipInfo->hostname)) {
                self::$ipInfoHostname = self::$ipInfo->hostname;
            }
            if (isset(self::$ipInfo->org)) {
                self::$ipInfoOrg = self::$ipInfo->org;
            }
			self::$ipInfoCountry = self::$ipInfo->country;
			#list(self::$ipInfoLatitude, self::$ipInfoLongitude) = explode(',', self::$ipInfo->loc);
			/*try {
				$googleLocation = json_decode(file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?latlng=' . self::$ipInfoLatitude . ',' . self::$ipInfoLongitude . '&sensor=false'));
				self::$ipInfoAddress = $googleLocation->results[2]->formatted_address;
			} catch (Exception  $e) {
				$googleLocation = null;
			}*/
			self::$ipInfoSource = 'ipinfo.io';
			self::$ipInfoError = false;
			return true;
		} catch (Exception  $e) {
			try {
				self::$ipInfo = json_decode(file_get_contents('http://freegeoip.net/json' . self::$ipUrl));
				self::$ipAddress = self::$ipInfo->ip;
				self::$ipInfoCountry = self::$ipInfo->country_code;
				/*self::$ipInfoLatitude = self::$ipInfo->latitude;
				self::$ipInfoLongitude = self::$ipInfo->longitude;*/
				/*try {
					$googleLocation = json_decode(file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?latlng=' . self::$ipInfoLatitude . ',' . self::$ipInfoLongitude . '&sensor=false'));
					self::$ipInfoAddress = $googleLocation->results[2]->formatted_address;
				} catch (Exception  $e) {
					$googleLocation = null;
				}*/
				self::$ipInfoSource = 'freegeoip.net';
				self::$ipInfoError = false;
				return true;
			} catch (Exception  $e) {
				self::$ipInfo = null;
				self::$ipInfoSource = null;
				self::$ipInfoError = true;
				return false;
			}
		}
	}

	public static function ipInfoSrc() {
		if (is_null(self::$ipInfo) && !self::$ipInfoError) { self::getIpInfo(); }
		return self::$ipInfoSource;
	}

	public static function ipHostname() {
		if (is_null(self::$ipInfo) && !self::$ipInfoError) { self::getIpInfo(); }
		return self::$ipInfoHostname;
	}

	public static function ipOrg() {
		if (is_null(self::$ipInfo) && !self::$ipInfoError) { self::getIpInfo(); }
		return self::$ipInfoOrg;
	}

	public static function ipCountry() {
		if (is_null(self::$ipInfo) && !self::$ipInfoError) { self::getIpInfo(); }
		return self::$ipInfoCountry;
	}

    private static $phoneModels = [
        'Samsung' => [
            'GT-I7500' => 'Samsung Galaxy',
            'GT-I5700' => 'Samsung Galaxy Spica',
            'GT-I9000' => 'Samsung Galaxy S',
            'GT-S5830' => 'Samsung Galaxy Ace',
            'GT-S5830I' => 'Samsung Galaxy Ace',
            'GT-S5670' => 'Samsung Galaxy Fit',
            'GT-I9003' => 'Samsung Galaxy SL',
            'GT-S5660' => 'Samsung Galaxy Gio',
            'GT-S5570' => 'Samsung Galaxy Mini',
            'SPH-M820' => 'Samsung Galaxy Prevail',
            'GT-I9100' => 'Samsung Galaxy S II',
            'SGH-T759' => 'Samsung Exhibit 4G',
            'GT-I9001' => 'Samsung Galaxy S Plus',
            'GT-I9103' => 'Samsung Galaxy R',
            'GT-I8150' => 'Samsung Galaxy W',
            'SGH-T679' => 'Samsung Exhibit II 4G',
            'GT-S5360' => 'Samsung Galaxy Y',
            'GT-S5690' => 'Samsung Galaxy Xcover',
            'GT-I9250' => 'Samsung Galaxy Nexus',
            'GT-B5510' => 'Samsung Galaxy Y Pro Duos',
            'GT-B5512' => 'Samsung Galaxy Y Pro Duos',
            'GT-S7500' => 'Samsung Galaxy Ace Plus',
            'GT-I8160' => 'Samsung Galaxy Ace 2',
            'GT-S7560M' => 'Samsung Galaxy Ace 2 x',
            'GT-S6500' => 'Samsung Galaxy Mini 2',
            'GT-S6102' => 'Samsung Galaxy Y DUOS',
            'GT-I8520' => 'Samsung Galaxy Beam',
            'SGH-i847' => 'Samsung Galaxy Rugby Smart',
            'GT-S5300' => 'Samsung Galaxy Pocket',
            'GT-S5690M' => 'Samsung Galaxy Rugby',
            'GT-I9300' => 'Samsung Galaxy S III',
            'GT-I9305' => 'Samsung Galaxy S III LTE',
            'SGH-I827' => 'Samsung Galaxy Appeal',
            'GT-B5330' => 'Samsung Galaxy Ch@t',
            'SCH-I200' => 'Samsung Galaxy Stellar',
            'GT-S7562' => 'Samsung Galaxy S Duos',
            'GT-S7568' => 'Samsung Galaxy S Duos',
            'TD-SCDMA' => 'Samsung Galaxy S Duos',
            'GT-S7560M' => 'Samsung Galaxy Trend',
            'GT-S7572' => 'Samsung Galaxy Trend II Duos',
            'GT-S5302' => 'Samsung Galaxy Pocket Duos',
            'SPH-L300' => 'Samsung Galaxy Victory 4G LTE',
            'GT-N7100' => 'Samsung Galaxy Note II (3G)',
            'GT-N7102' => 'Samsung Galaxy Note II (Dual-SIM)',
            'GT-N7105' => 'Samsung Galaxy Note II (4G)',
            'SGH-I437' => 'Samsung Galaxy Express',
            'SGH-I547' => 'Samsung Galaxy Rugby Pro',
            'SGH-I547C' => 'Samsung Galaxy Rugby LTE',
            'GT-I8190' => 'Samsung Galaxy S III Mini'
        ]
    ];

	/*public static function ipLatitude() {
		if (is_null(self::$ipInfo) && !self::$ipInfoError) { self::getIpInfo(); }
		return self::$ipInfoLatitude;
	}

	public static function ipLongitude() {
		if (is_null(self::$ipInfo) && !self::$ipInfoError) { self::getIpInfo(); }
		return self::$ipInfoLongitude;
	}

	public static function ipLocation() {
		if (is_null(self::$ipInfo) && !self::$ipInfoError) { self::getIpInfo(); }
		return self::$ipInfoAddress;
	}*/

}

class Debug {
	public static function error($message = null, $trace = array()) {
		if (!empty($trace) && !empty($message))
			echo $message . ', in <strong>' . $trace['file'] . '</strong> on line <strong>' . $trace['line'] . '</strong>';
	}
}

Detect::init();

?>