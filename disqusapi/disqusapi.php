<?php
/**
 * Implementation of the Disqus API.
 *
 * http://disqus.com/api/
 *
 * @author		DISQUS <team@disqus.com>
 * @copyright	2007-2010 Big Head Labs
 * @link		http://disqus.com/
 * @package		disqusapi
 * @version		0.1.0
 * 
 * $disqus = new DisqusAPI($secret_key)
 * $disqus->trends->listThreads()
 * 
 */

if (!defined('DISQUS_API_HOST')) {
    define('DISQUS_API_HOST', 'disqus.com');
}
if (!defined('DISQUS_API_SSL_HOST')) {
    define('DISQUS_API_SSL_HOST', 'secure.disqus.com');
}
define('DISQUS_API_VERSION', '0.0.1');

require_once(dirname(__FILE__) . '/url.php');

if (!extension_loaded('json')) {
	require_once(dirname(__FILE__) . '/json.php');
	function dsq_json_decode($data) {
		$json = new JSON;
		return $json->unserialize($data);
	}
} else {
	function dsq_json_decode($data) {
		return json_decode($data);
	}	
}

global $DISQUS_API_INTERFACES;

$DISQUS_API_INTERFACES = dsq_json_decode(file_get_contents(dirname(__FILE__) . '/interfaces.json'));

class DisqusInterfaceNotDefined extends Exception {}
class DisqusAPIError extends Exception {
    public function __construct($code, $message) {
        $this->code = $code;
        $this->message = $message;
    }
}

class DisqusResource {
    public function __construct($api, $interface=null, $node=null, $tree=array()) {
        global $DISQUS_API_INTERFACES;
        
        if (!$interface) {
            $interface = $DISQUS_API_INTERFACES;
        }
        $this->api = $api;
        $this->interface = $interface;
        $this->node = $node;
        if ($node) {
            array_push($tree, $node);
        }
        $this->tree = $tree;
    }
    
    public function __get($attr) {
        $interface = $this->interface->$attr;
        if (!$interface) {
            throw new DisqusInterfaceNotDefined();
        }
        return new DisqusResource($this->api, $interface, $attr, $this->tree);
    }
    
    public function __call($name, $args) {
        $resource = $this->interface->$name;
        if (!$resource) {
            throw new DisqusInterfaceNotDefined();
        }
        $kwargs = (array)$args[0];
        
	foreach ((array) $resource->required as $k) {
	    if (empty($kwargs[$k])) {
		// Check if query types are available, and we have one we can override
		if ($resource->query_type && $resource->query_type->insteadof == $k) {
		    if (empty($kwargs[$resource->query_type->requires])) {
			$missing[] = $k .  ' or ' . $resource->query_type->requires;
		    } else {
			// Check for other required args to make up the query type ..
			$missing_or = array();
			foreach ((array) $resource->query_type->with_either as $ek) {
			    if (isset($kwargs[$ek])) {
				// Now must have everything needed for the query
				break;
			    } else {
				$missing_or[] = $ek;
			    }
			}
			if (!empty($missing_or)) {
			    $missing[] = join(' or ', $missing_or);
			}
			unset($missing_or);
		    }
		} else {
		    $missing[] = $k;
		}
	    }
        }
        
        $api = $this->api;
        
        if (empty($kwargs['api_secret'])) {
            $kwargs['api_secret'] = $api->key;
        }
        
        // emulate a named pop
        $version = (!empty($kwargs['version']) ? $kwargs['version'] : $api->version);
        $format = (!empty($kwargs['format']) ? $kwargs['format'] : $api->format);
        unset($kwargs['version'], $kwargs['format']);
        
        $url = ($api->is_secure ? 'https://'.DISQUS_API_SSL_HOST : 'http://'.DISQUS_API_HOST);
        $path = '/api/'.$version.'/'.implode('/', $this->tree).'/'.$name.'.'.$format;
        
        if (!empty($kwargs)) {
            if ($resource->method == 'POST') {
                $post_data = $kwargs;
            } else {
                $post_data = false;
                $path .= '?'.dsq_get_query_string($kwargs);
            }
        }
        

        $response = dsq_urlopen($url.$path, $post_data);
        
        $data = call_user_func($api->formats[$format], $response['data']);
        
        if ($response['code'] != 200) {
            throw new DisqusAPIError($data->code, $data->response);
        }
        
        return $data->response;
    }
}


class DisqusAPI extends DisqusResource {
    public $formats = array(
        'json' => 'dsq_json_decode'
    );

    public function __construct($key=null, $format='json', $version='3.0', $is_secure=false) {
        $this->key = $key;
        $this->format = $format;
        $this->version = $version;
        $this->is_secure = $is_secure;
        parent::__construct($this);
    }

    public function __invoke() {
        throw new Exception('You cannot call the API without a resource.');
    }

    public function setKey($key) {
        $this->key = $key;
    }

    public function setFormat($format) {
        $this->format = $format;
    }
    
    public function setVersion($version) {
        $this->version = $version;
    }
}