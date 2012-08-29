<?php

/**
 * A Service implementation aiming at checking if the server side can talk 'JSON' and
 * receive information from the server to be 'synchronized' with it.
 * Information received are the TAO root URL, ...
 * 
 * Please refer to tao/install/api.php for more information about how to call this service.
 */
class tao_install_services_SyncService extends tao_install_services_Service{
    
    /**
     * Creates a new instance of the service.
     * @param tao_install_services_Data $data The input data to be handled by the service.
     * @throws InvalidArgumentException If the input data structured is malformed or is missing data.
     */
    public function __construct(tao_install_services_Data $data){
        parent::__construct($data);
        
        // Check data integrity.
        $content = $this->getData()->getContent();
        if (!isset($content['type']) || empty($content['type']) || $content['type'] !== 'Sync'){
            throw new InvalidArgumentException("Unexpected type: 'type' must be equal to 'Sync'.");
        }
    }
    
    /**
     * Executes the main logic of the service.
     * @return tao_install_services_Data The result of the service execution.
     */
    public function execute(){
        $ext = new common_configuration_PHPExtension(null, null, 'json');
        $report = $ext->check();
                                       
        // We fake JSON encoding for a gracefull response in any case.
        $json = $report->getStatus() == common_configuration_Report::VALID;
        if (!$json){
        	$data = '{"type": "SyncReport", "value": { "json": '. (($json) ? 'true' : 'false') . '}}';
        }
        else{
        	// We return basic information to the client about server-side.
        	$rootUrl = 
        	
        	$data = json_encode(array('type' => 'SyncReport', 'value' => array(
        		'json' => true,
        		'rootURL' => self::getRootUrl()
        	)));
        }
        
                                       
        $this->setResult(new tao_install_services_Data($data));
    }
    
    private static function getRootUrl(){
    	// Returns TAO ROOT url based on a call to the API.
    	$isHTTPS = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']);
    	$host = $_SERVER['HTTP_HOST'];
    	$uri = $_SERVER['REQUEST_URI'];
    	$currentUrl = (true == $isHTTPS) ? 'https' : 'http' . '://' . $host . $uri;
    	$parsed = parse_url($currentUrl);
    	$rootUrl = $parsed['scheme'] . '://' . $parsed['host'] . $parsed['path'];
    	return str_replace('/tao/install/api.php', '', $rootUrl);
    }
}
?>