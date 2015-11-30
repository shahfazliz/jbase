<?php
    header('Content-Type: application/json'); // JSON
    header("access-control-allow-origin: *"); // Cross-Origin Resource Sharing (CORS)
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('Access-Control-Allow-Headers: X-Requested-With');
    
    require_once($_SERVER["DOCUMENT_ROOT"].'/helper/PHPServlet.php');
    require_once($_SERVER["DOCUMENT_ROOT"].'/helper/DBEvents.php');
    class Product extends PHPServlet{
        
        // test with: curl -X POST -d "name=Asus&cat=Mobo" https://jbase-shahfazliz.c9.io/pcparts/product/
        public function doPOST($REQUEST){
            $db     = new DBEvents(parent::getModule(), parent::getModel());
            $result = $db-> postData($REQUEST);
            $db-> close();
            
            // Echo json OR jsonp
            $json   = json_encode($result);
            echo parent::returnJSONPResponse($REQUEST, $json);
        }
        
        // test with: curl -X GET -d "id=3" https://jbase-shahfazliz.c9.io/pcparts/product/
        public function doGET($REQUEST){
            $db     = new DBEvents(parent::getModule(), parent::getModel());
            $result = $db-> getData($REQUEST);
            $db-> close();
            
            // Echo json OR jsonp
            $json   = json_encode($result);
            echo parent::returnJSONPResponse($REQUEST, $json);
        }
        
        // test with: curl -X PUT -d "id=8&name=Asusah&cat=Mobo" https://jbase-shahfazliz.c9.io/pcparts/product/
        // test with: curl -X PUT -d "id=11&name=Pikom&cat=Mobo&price=150.00" https://jbase-shahfazliz.c9.io/pcparts/product/
        // test with: curl -X PUT -d "id=8&name=Asusah&cat=Mobo&price=250.00" https://jbase-shahfazliz.c9.io/pcparts/product/
        public function doPUT($REQUEST){
            $db     = new DBEvents(parent::getModule(), parent::getModel());
            $result = $db-> putData($REQUEST);
            $db-> close();
            
            // Echo json OR jsonp
            $json   = json_encode($result);
            echo parent::returnJSONPResponse($REQUEST, $json);
        }
        
        // test with: curl -X DELETE -d "id=3" https://jbase-shahfazliz.c9.io/pcparts/product/
        public function doDELETE($REQUEST){
            $db     = new DBEvents(parent::getModule(), parent::getModel());
            $result = $db-> deleteData($REQUEST);
            $db-> close();
            
            // Echo json OR jsonp
            $json   = json_encode($result);
            echo parent::returnJSONPResponse($REQUEST, $json);
        }
    }
    new Product;
?>