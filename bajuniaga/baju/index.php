<?php
    header('Content-Type: application/json'); // JSON
    header('Access-Control-Allow-Origin: *'); // Cross-Origin Resource Sharing (CORS)
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: X-Requested-With');
    
    require_once($_SERVER["DOCUMENT_ROOT"].'/helper/PHPServlet.php');
    require_once($_SERVER["DOCUMENT_ROOT"].'/helper/DBEvents.php');
    class Baju extends PHPServlet{
        
        public function doOPTIONS(){
            // Echo json OR jsonp
            echo parent::returnJSONPResponse($REQUEST, file_get_contents("Baju.json"));
        }
        
        // test with: curl -X POST -d "name=Asus&cat=Mobo" https://jbase-shahfazliz.c9.io/pcparts/product/
        public function doPOST($REQUEST){
            if(!parent::checkAutorization($REQUEST['APIToken'], 'Create')) return false; // throw new UnexpectedValueException('Request unauthorized');
            parent::setAuthId($REQUEST['APIToken']);
            unset($REQUEST['APIToken']);
            
            $db     = new DBEvents(parent::getModule(), parent::getModel(), parent::getAuthId());
            $result = $db-> postData($REQUEST);
            $db-> close();
            
            // Echo json OR jsonp
            $json = json_encode($result);
            echo parent::returnJSONPResponse($REQUEST, $json);
        }
        
        // test with: curl -X GET https://jbase-shahfazliz.c9users.io/bajuniaga/baju/
        // test with: curl -X GET https://jbase-shahfazliz.c9users.io/bajuniaga/baju/?id=72
        public function doGet($REQUEST){
            if(!parent::checkAutorization($REQUEST['APIToken'], 'Read')) return false; // throw new UnexpectedValueException('Request unauthorized');
            parent::setAuthId($REQUEST['APIToken']);
            unset($REQUEST['APIToken']);
            
            $db     = new DBEvents(parent::getModule(), parent::getModel());
            $results = $db-> getData($REQUEST);
            $db-> close();
            
            $json = array();
            foreach($results as $result){
                $jsonArray = json_decode(file_get_contents("Baju.json"), true);
                foreach($jsonArray['properties'] as $key => $value){
                    $value['value'] = $result[str_replace(' ', '_', $key)];
                    $jsonArray['properties'][$key] = $value;
                }
                $json[] = $jsonArray;
            }
            
            // $jsonArray = json_decode(file_get_contents("Baju.json"), true);
            // foreach($jsonArray['properties'] as $key => $value){
            //     $value['value'] = $result[str_replace(' ', '_', $key)];
            //     $jsonArray['properties'][$key] = $value;
            // }
            
            // Echo json OR jsonp
            // $json = json_encode($jsonArray);
            $json = json_encode($json);
            echo parent::returnJSONPResponse($REQUEST, $json);
        }
        
        // test with: curl -X PUT -d "id=109&Name=PikomMaster&Description=Lalalalalaa" https://jbase-shahfazliz.c9.io/bajuniaga/baju/
        public function doPut($REQUEST){
            if(!parent::checkAutorization($REQUEST['APIToken'], 'Update (other)')) return false; // throw new UnexpectedValueException('Request unauthorized');
            parent::setAuthId($REQUEST['APIToken']);
            unset($REQUEST['APIToken']);
            
            $db     = new DBEvents(parent::getModule(), parent::getModel());
            $result = $db-> putData($REQUEST);
            $db-> close();
            
            // Echo json OR jsonp
            $json   = json_encode($result);
            echo parent::returnJSONPResponse($REQUEST, $json);
        }
        
        // test with: curl -X DELETE -d "id=117" https://jbase-shahfazliz.c9.io/bajuniaga/baju/
        public function doDelete($REQUEST){
            if(!parent::checkAutorization($REQUEST['APIToken'], 'Delete (other)')) return false; // throw new UnexpectedValueException('Request unauthorized');
            parent::setAuthId($REQUEST['APIToken']);
            unset($REQUEST['APIToken']);
            
            $db     = new DBEvents(parent::getModule(), parent::getModel());
            $result = $db-> deleteData($REQUEST);
            $db-> close();
            
            // Echo json OR jsonp
            $json   = json_encode($result);
            echo parent::returnJSONPResponse($REQUEST, $json);
        }
    }
    new Baju;
?>