<?php

/**
 * Mobile_Detect is a lightweight PHP class for detecting mobile devices (including tablets).
 * It uses the User-Agent string combined with specific HTTP headers to detect the mobile environment.
 * @license Code and contributions have 'MIT License'
 * More details: https://github.com/serbanghita/Mobile-Detect/blob/master/LICENSE.txt
 * 
 */
if(!class_exists( 'AE_MobileDetect' ) ) {
class AE_MobileDetect {
	/**
	 * 
	*/
	protected $accept;
	/**
	 * 
	*/
	protected $userAgent;
	/**
	 * 
	*/
	protected $isMobile = false;
	protected $isAndroid = null;
	protected $isAndroidtablet = null;
	protected $isIphone = null;
	protected $isIpad = null;
	protected $isBlackberry = null;
	protected $isBlackberrytablet = null;
	protected $isOpera = null;
	protected $isPalm = null;
	protected $isWindows = null;
	protected $isWindowsphone = null;
	protected $isGeneric = null;

	/**
	 * list of supported device
	*/
	protected $devices = array(
		"android" => "android.*mobile",
		"androidtablet" => "android(?!.*mobile)",
		"blackberry" => "blackberry",
		"blackberrytablet" => "rim tablet os",
		"iphone" => "(iphone|ipod)",
		"ipad" => "(ipad)",
		"palm" => "(avantgo|blazer|elaine|hiptop|palm|plucker|xiino)",
		"windows" => "windows ce; (iemobile|ppc|smartphone)",
		"windowsphone" => "Windows Phone 8.0|Windows Phone OS|XBLWP7|ZuneWP7",
		"generic" => "(kindle|mobile|mmp|midp|pocket|psp|symbian|smartphone|treo|up.browser|up.link|vodafone|wap|opera mini)"
	);

	public static $instance;

	/**
	 * contruct object function
	*/
	public function __construct()
	{
		$this->userAgent = $_SERVER['HTTP_USER_AGENT'];
		$this->accept = $_SERVER['HTTP_ACCEPT'];

		if (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
			$this->isMobile = true;
		} elseif (strpos($this->accept, 'text/vnd.wap.wml') > 0 || strpos($this->accept, 'application/vnd.wap.xhtml+xml') > 0) {
			$this->isMobile = true;
		} else {
			foreach ($this->devices as $device => $regexp) {
				if ($this->isDevice($device)) {
					$this->isMobile = true;
				}
			}
		}
	}

	public static function get_instance() {
		if(self::$instance == null) {
			self::$instance	=	new ET_MobileDetect ();
		}
		return self::$instance;
	}

	/**
	 * Overloads isAndroid() | isAndroidtablet() | isIphone() | isIpad() | isBlackberry() | isBlackberrytablet() | isPalm() | isWindowsphone() | isWindows() | isGeneric() through isDevice()
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return bool
	 */
	public function __call($name, $arguments) {
		$device = substr($name, 2);
		if ($name == "is" . ucfirst($device) && array_key_exists(strtolower($device), $this->devices)) {
			return $this->isDevice($device);
		} else {
			trigger_error("Method $name not defined", E_USER_WARNING);
		}
	}

	/**
	 * Returns true if any type of mobile device detected, including special ones
	 * @return bool
	 */
	public function isMobile() {
		return $this->isMobile;
	}

	/**
	 * check a divce
	 * @param string $device device name input
	 * @return bool
	*/
	protected function isDevice($device) {
		$var = "is" . ucfirst($device);
		$return = $this->$var === null ? (bool) preg_match("/" . $this->devices[strtolower($device)] . "/i", $this->userAgent) : $this->$var;
		if ($device != 'generic' && $return == true) {
			$this->isGeneric = false;
		}

		return $return;
	}

}

}
