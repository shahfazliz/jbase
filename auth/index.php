<?php
    header('Content-Type: application/json'); // JSON
    header('Access-Control-Allow-Origin: *'); // Cross-Origin Resource Sharing (CORS)
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: X-Requested-With');
    
    require_once($_SERVER["DOCUMENT_ROOT"].'/helper/PHPServlet.php');
    require_once($_SERVER["DOCUMENT_ROOT"].'/auth/APIAuth.php');
    class Authentication extends PHPServlet{
        public function doOPTIONS(){
            // Echo json OR jsonp
            echo parent::returnJSONPResponse($REQUEST, file_get_contents("Auth.json"));
        }
        
        // test with: curl -X POST -d "Username=Asus&Password=Mobo" https://jbase-shahfazliz.c9.io/auth/
        // Register new Auth
        public function doPOST($REQUEST){
            if($this-> verifyCaptcha($REQUEST['g-recaptcha-response'])){
                unset($REQUEST['g-recaptcha-response']);
                
                $db     = new APIAuth();
                $result = $db-> postData($REQUEST);
                $db-> close();
                
                // Echo json OR jsonp
                $json = json_encode($result);
                echo parent::returnJSONPResponse($REQUEST, $json);
            }
            else return false;
        }
        
        // test with: curl -X GET https://jbase-shahfazliz.c9.io/auth/
        // test with: curl -X GET https://jbase-shahfazliz.c9.io/auth/?id=1
        public function doGet($REQUEST){
            if(!parent::checkAutorization($REQUEST['APIToken'], 'Read')) return false; // throw new UnexpectedValueException('Request unauthorized');
            parent::setAuthId($REQUEST['APIToken']);
            unset($REQUEST['APIToken']);
            
            $db     = new APIAuth();
            $result = $db-> getData($REQUEST);
            $db-> close();
            
            $jsonArray = json_decode(file_get_contents("Auth.json"), true);
            foreach($jsonArray['properties'] as $key => $value){
                $value['value'] = $result[str_replace(' ', '_', $key)];
                $jsonArray['properties'][$key] = $value;
            }
            
            // Echo json OR jsonp
            $json = json_encode($jsonArray);
            echo parent::returnJSONPResponse($REQUEST, $json);
        }
        
        // test with: curl -X PUT -d "Username=test&Password=test123" https://jbase-shahfazliz.c9.io/auth/
        // test with: curl -X PUT -d "id=109&Username=PikomMaster" https://jbase-shahfazliz.c9.io/auth/
        public function doPut($REQUEST){
            if($REQUEST['id']){
                if(!parent::checkAutorization($REQUEST['APIToken'], 'Update (other)')) return false; // throw new UnexpectedValueException('Request unauthorized');
                parent::setAuthId($REQUEST['APIToken']);
                unset($REQUEST['APIToken']);
            }
            
            // else if $REQUEST['id'] is null, then it's requesting to login. then do the rest below
            
            $db     = new APIAuth();
            $result = $db-> putData($REQUEST);
            $db-> close();
            
            // Echo json OR jsonp
            $json   = json_encode($result);
            echo parent::returnJSONPResponse($REQUEST, $json);
        }
        
        // test with: curl -X DELETE -d "id=1" https://jbase-shahfazliz.c9.io/auth/
        public function doDelete($REQUEST){
            if(!parent::checkAutorization($REQUEST['APIToken'], 'Delete (other)')) return false; // throw new UnexpectedValueException('Request unauthorized');
            parent::setAuthId($REQUEST['APIToken']);
            unset($REQUEST['APIToken']);
            
            $db     = new APIAuth();
            $result = $db-> deleteData($REQUEST);
            $db-> close();
            
            // Echo json OR jsonp
            $json   = json_encode($result);
            echo parent::returnJSONPResponse($REQUEST, $json);
        }
        
        private function verifyCaptcha($sitekey){
            $fields = array(
                'secret' => '6LfMkBETAAAAAExaKn7yt7eioL1TlLdnlrZJIeYP',
                'response' => $sitekey,
                'remoteip' => parent::getClientIp()
            );
            $response = $this-> httpPOST('https://www.google.com/recaptcha/api/siteverify', $fields);
            $response = json_decode($response, true);
            // error_log(print_r($response, true));
            if($response['success']) return true;
            else false;
        }
        
        private function httpPOST($url, $data){
            // use key 'http' even if you send the request to https://...
            $options = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                ),
            );
            $context  = stream_context_create($options);
            return file_get_contents($url, false, $context);
        }
    }
    new Authentication;
?>