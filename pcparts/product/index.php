<?php
    header('Content-Type: application/json');
    
    require_once($_SERVER["DOCUMENT_ROOT"].'/helper/PHPServlet.php');
    require_once($_SERVER["DOCUMENT_ROOT"].'/helper/DBEvents.php');
    class Product extends PHPServlet{
        
        // test with: curl -X POST -d "name=Asus&cat=Mobo" https://jbase-shahfazliz.c9.io/pcparts/product/
        public function doPOST($REQUEST){
            $db = new DBEvents(parent::getModule(), parent::getModel());
            $result = $db-> postData($REQUEST);
            echo json_encode($result);
        }
        
        // test with: curl -X GET -d "id=3" https://jbase-shahfazliz.c9.io/pcparts/product/
        public function doGET($REQUEST){
            $db = new DBEvents(parent::getModule(), parent::getModel());
            $result = $db-> getData($REQUEST);
            echo json_encode($result);
        }
        
        // test with: curl -X PUT -d "id=3&name=Asus&cat=Mobo" https://jbase-shahfazliz.c9.io/pcparts/product/
        public function doPUT($REQUEST){
            $db = new DBEvents(parent::getModule(), parent::getModel());
            $result = $db-> putData($REQUEST);
            echo json_encode($result);
        }
        
        // test with: curl -X DELETE -d "id=3" https://jbase-shahfazliz.c9.io/pcparts/product/
        public function doDELETE($REQUEST){
            $db = new DBEvents(parent::getModule(), parent::getModel());
            $result = $db-> deleteData($REQUEST);
            echo json_encode($result);
        }
    }
    new Product;
?>