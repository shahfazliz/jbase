<?php
    require_once($_SERVER["DOCUMENT_ROOT"].'/auth/APIToken.php');
    require_once($_SERVER["DOCUMENT_ROOT"].'/helper/StringManipulator.php');
    
    class PHPServlet{
        private $MODULE;
        private $MODEL;
        private $AUTH_ID;
        function __construct(){
            
            // get module and model
            $arrStrURL = explode('/', $_SERVER['PHP_SELF']);
            $this-> MODULE = $arrStrURL[1];
            $this-> MODEL  = $arrStrURL[2];
            
            // respond according to request method
            switch($_SERVER['REQUEST_METHOD']){
                case 'POST':
                    // error_log(($this-> MODEL).' REQUEST in GET: '.print_r($_POST, true));
                    $this-> doPOST($_POST);
                    break;
                    
                case 'GET':
                    // error_log(($this-> MODEL).' REQUEST in GET: '.print_r($_GET, true));
                    $this-> doGET($_GET);
                    break;
                    
                case 'PUT':
                    parse_str(file_get_contents("php://input"),$_PUT);
                    // error_log(($this-> MODEL).' REQUEST in GET: '.print_r($_PUT, true));
                    $this-> doPUT($_PUT);
                    break;
                case 'DELETE':
                    parse_str(file_get_contents("php://input"),$_DELETE);
                    // error_log(($this-> MODEL).' REQUEST in GET: '.print_r($_DELETE, true));
                    $this-> doDELETE($_DELETE);
                    break;
                case 'OPTIONS':
                    $this-> doOPTIONS(); 
                    break;
            }
        }
        
        // Added security from injection into callback
        // Got this from http://www.geekality.net/2010/06/27/php-how-to-easily-provide-json-and-jsonp/
        private function is_valid_callback($subject){
            $identifier_syntax
              = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';
        
            $reserved_words = array('break', 'do', 'instanceof', 'typeof', 'case',
              'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue', 
              'for', 'switch', 'while', 'debugger', 'function', 'this', 'with', 
              'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum', 
              'extends', 'super', 'const', 'export', 'import', 'implements', 'let', 
              'private', 'public', 'yield', 'interface', 'package', 'protected', 
              'static', 'null', 'true', 'false');
        
            return preg_match($identifier_syntax, $subject)
                && ! in_array(mb_strtolower($subject, 'UTF-8'), $reserved_words);
        }
        
        // Echo json OR jsonp
        // Got this from http://www.geekality.net/2010/06/27/php-how-to-easily-provide-json-and-jsonp/
        public function returnJSONPResponse($REQUEST, $json){
            if(isset($REQUEST['callback'])){
                if($this-> is_valid_callback(isset($REQUEST['callback'])))
                    return "{$REQUEST['callback']}($json)";
                else return header('status: 400 Bad Request', true, 400);
            }
            else return $json;
        }
        
        public function getModule(){return $this-> MODULE;}
        public function getModel(){return $this-> MODEL;}
        public function getAuthId(){return $this-> AUTH_ID;}
        
        public function doPOST($request){}
        public function doGET($request){}
        public function doPUT($request){}
        public function doDELETE($request){}
        public function doOPTIONS(){}
        
        // Set Auth Id
        public function setAuthId($token){
            // get Auth id
            $api = new APIToken();
            $this-> AUTH_ID = $api-> getAuthId($token);
        }
        
        // 
        public function checkAutorization($token, $action){
            $tool = new StringManipulator();
            $api = new APIToken();
            if($api-> checkAutorization($token, $tool-> ToCamelCase($this-> MODEL), $action)){
                return true;
            }
            else return false;
        }
        
        // Function to get the client ip address
        public function getClientIp() {
            $ipaddress = '';
            if ($_SERVER['HTTP_CLIENT_IP'])
                $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
            else if($_SERVER['HTTP_X_FORWARDED_FOR'])
                $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
            else if($_SERVER['HTTP_X_FORWARDED'])
                $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
            else if($_SERVER['HTTP_FORWARDED_FOR'])
                $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
            else if($_SERVER['HTTP_FORWARDED'])
                $ipaddress = $_SERVER['HTTP_FORWARDED'];
            else if($_SERVER['REMOTE_ADDR'])
                $ipaddress = $_SERVER['REMOTE_ADDR'];
            else
                $ipaddress = 'UNKNOWN';
         
            return $ipaddress;
        }
    }
?>