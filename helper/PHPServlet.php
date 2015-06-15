<?php
    class PHPServlet{
        private $MODULE;
        private $MODEL;
        function __construct(){
            
            // get module and model
            $arrStrURL = explode('/', $_SERVER['PHP_SELF']);
            $this-> MODULE = $arrStrURL[1];
            $this-> MODEL  = $arrStrURL[2];
            
            // respond according to request method
            switch($_SERVER['REQUEST_METHOD']){
                case 'POST':
                    $this-> doPOST($_POST);
                    break;
                case 'GET':
                    $this-> doGET($_GET);
                    break;
                case 'PUT':
                    parse_str(file_get_contents("php://input"),$_PUT);
                    $this-> doPUT($_PUT);
                    break;
                case 'DELETE':
                    parse_str(file_get_contents("php://input"),$_DELETE);
                    $this-> doDELETE($_DELETE);
                    break;
            }
        }
        
        public function getModule(){return $this-> MODULE;}
        public function getModel(){return $this-> MODEL;}
        
        public function doPOST($request){}
        public function doGET($request){}
        public function doPUT($request){}
        public function doDELETE($request){}
    }
?>