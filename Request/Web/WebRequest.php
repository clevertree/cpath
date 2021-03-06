<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/14
 * Time: 3:18 PM
 */
namespace CPath\Request\Web;

use CPath\Render\Helpers\CookieUtil;
use CPath\Render\HTML\HTMLMimeType;
use CPath\Render\JSON\JSONMimeType;
use CPath\Render\Text\TextMimeType;
use CPath\Render\XML\XMLMimeType;
use CPath\Request\Cookie\ICookieRequest;
use CPath\Request\Exceptions\RequestException;
use CPath\Request\MimeType\IRequestedMimeType;
use CPath\Request\MimeType\UnknownMimeType;
use CPath\Request\Request;
use CPath\Request\Session\ISessionRequest;
use CPath\Request\Session\SessionRequest;
use CPath\Request\Session\SessionRequestException;
use CPath\Response\IResponse;

class WebRequest extends Request implements ISessionRequest, ICookieRequest
{
    private $mHeaders = null;

	private $mPrefixPath = null;
	protected $mValueSource = null;

	private $mSessionRequest = null;

    public function __construct($method, $path = null, $parameters = array(), IRequestedMimeType $MimeType=null) {
	    if(!$path) {
		    $parse = parse_url($_SERVER['REQUEST_URI']);
		    $path = $parse['path'];
//		    if(!$parameters)
//			    parse_str($parse['query'], $parameters);
	    }
	    $root = dirname($_SERVER['SCRIPT_NAME']);
	    if (stripos($path, $root) === 0) {
		    $this->mPrefixPath = ltrim(substr($path, 0, strlen($root)), '/') . '/';
		    $path = substr($path, strlen($root));
	    }

        parent::__construct($method, $path, $parameters, $MimeType ?: $this->getHeaderMimeType());

        if(preg_match('/\.(js|css|png|gif|jpg|bmp|ico)/i', $this->getPath(), $matches))
            throw new RequestException("File request was passed to Script: ", IResponse::HTTP_NOT_FOUND);
    }

	public function getSessionRequest() {
		return $this->mSessionRequest ?:
			$this->mSessionRequest = new SessionRequest();
	}

	protected function getAllFormValues() {
		if ($this->mValueSource !== null)
			return $this->mValueSource;

		if(!$_GET && $p = strpos($_SERVER['REQUEST_URI'], '?')) {
			$queryString = substr($_SERVER['REQUEST_URI'], $p+1);
			parse_str($queryString, $vars);
			$this->log('$_GET data not available. input parsed from request string', static::ERROR);
			return $this->mValueSource = $vars;
		}

		return $this->mValueSource = $_GET;
	}

	/**
	 * @param bool $withDomain
	 * @return String
	 */
	function getDomainPath($withDomain=true) {
		$path = $this->mPrefixPath;
		if($withDomain) {
			$protocol = 'http';
			if(isset($_SERVER['HTTPS']))
				$protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";

			$path = $protocol . "://" . rtrim($_SERVER['SERVER_NAME'], '/') . '/' . ltrim($path, '/');
		}
		return $path;
	}

	function getQueryStringValue($paramName, $filter=FILTER_SANITIZE_SPECIAL_CHARS) {
		$values = $this->getAllFormValues();
		return $this->getNamedRequestValue($paramName, $values, null, $filter);
	}

    /**
     * Return a request parameter (GET) value
     * @param String $paramName
     * @param int $filter
     * @return mixed|null the request parameter value or null if not found
     */

	function getRequestValue($paramName, $filter=FILTER_SANITIZE_SPECIAL_CHARS) {
		return parent::getRequestValue($paramName, $filter)
			?: $this->getQueryStringValue($paramName, $filter);
	}

	/**
     * Get the requested Mime type(s) for rendering purposes
     * @return \CPath\Request\MimeType\IRequestedMimeType
     */
    function getHeaderMimeType() {
        $accepts = 'text/html';
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            $accepts = $_SERVER['HTTP_ACCEPT'];
        } else if (function_exists('getallheaders')) {
            foreach (getallheaders() as $key => $value)
                if ($key == 'Accept')
                    $accepts = $value;
        }

        $Type = null;
        foreach (array_reverse(explode(',', $accepts)) as $type) {
            list($type) = explode(';', $type, 2);
            $type = trim($type);
            switch (strtolower($type)) {
                case 'application/json':
                case 'application/x-javascript':
                case 'text/javascript':
                case 'text/x-javascript':
                case 'text/x-json':
                    $Type = new JSONMimeType($type, $Type);
                    break;
                case 'application/xml':
                case 'text/xml':
                    $Type = new XMLMimeType($type, $Type);
                    break;
	            case '*/*':
	            case 'text/html':
                case 'application/xhtml+xml':
                    $Type = new HTMLMimeType($type, $Type);
                    break;
                case 'text/plain':
                    $Type = new TextMimeType($type, $Type);
                    break;
                default:
                    $Type = new UnknownMimeType($type, $Type);
            }
        }

        return $Type;
    }

    function getAllHeaders() {
        if ($this->mHeaders !== null)
            return $this->mHeaders;

        if (function_exists('getallheaders'))
            return $this->mHeaders = getallheaders();

	    $this->mHeaders = array();
        foreach ($_SERVER as $name => $value) {
            if (in_array(substr($name, 0, 5), array('CONTE', 'HTTP_'))) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $this->mHeaders[$name] = $value;
            }
        }
        return $this->mHeaders;
    }

    function getHeader($headerName) {
        $headers = self::getAllHeaders();
	    foreach($headers as $name=>$value)
		    if(strcasecmp($headerName, $name) === 0)
			    return $value;

        return null;
    }

    /**
     * Returns the session id or false if inactive
     * @return string|bool
     */
	function getSessionID() {
        return isset($_COOKIE[session_name()]) ? $_COOKIE[session_name()] : session_id();
	}


	/**
	 * Returns true if the session is active, false if inactive
	 * @return bool
	 */
	function isStarted() {
		return $this->getSessionRequest()->isStarted();
	}

	/**
	 * Return a referenced array representing the request session
	 * @param String|null [optional] $key if set, retrieves &$[Session][$key] instead of &$[Session]
	 * @throws SessionRequestException if no session was active
	 * @return array
	 */
    function &getSession() {
	    return $this->getSessionRequest()->getSession();
    }

	/**
	 * Start a new session
	 * @throws SessionRequestException
	 * @return bool true if session was started, otherwise false
	 */
	function startSession() {
		return $this->getSessionRequest()->startSession();
	}

	/**
	 * End current session
	 * @throws SessionRequestException
	 * @return bool true if session was started, otherwise false
	 */
	function endSession() {
		return $this->getSessionRequest()->endSession();
	}

	/**
	 * Destroy session data
	 * @return bool true if session was destroyed, otherwise false
	 * @throws SessionRequestException if session wasn't active
	 */
	function destroySession() {
		return $this->getSessionRequest()->destroySession();
	}

    /**
     * Get a cookie
     * @param String $name retrieves &$[Cookie][$name]
     * @return String|null
     */
    function getCookie($name) {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }

    /**
     * Set a cookie
     * @param $name
     * @param string $value
     * @param int $maxage
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $HTTPOnly
     * @return bool
     */
    function sendCookie($name, $value = '', $maxage = 0, $path = '', $domain = '', $secure = false, $HTTPOnly = false) {
        $path = $this->getDomainPath(false) . $path;
        $CookieUtil = new CookieUtil();
        return $CookieUtil->sendCookie($name, $value, $maxage, $path, $domain, $secure, $HTTPOnly);
    }

	public function offsetGet($offset) {
		$value = parent::offsetGet($offset);
		return is_string($value) ? htmlspecialchars($value) : $value;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Retrieve an external iterator
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return \Traversable An instance of an object implementing <b>Iterator</b> or
	 * <b>Traversable</b>
	 */
	public function getIterator() {
		$values = $this->getAllFormValues();
		return new \ArrayIterator($values);
	}
}