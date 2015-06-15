<?php
    class DBEvents{
        private $MODULE;
        private $MODEL;
        
        private $MODULE_ID;
        private $MODEL_ID;
        
        private $stmt = null;
        private $conn = null;
        
        function __construct($MODULE, $MODEL){
            $this-> MODULE = $MODULE;
            $this-> MODEL  = $MODEL;
            
            $username   = 'shahfazliz';
            $password   = '';
            $host       = '127.0.0.1';
            $port       = '3306';
            $database   = 'c9';
        
            // connect database
            try {
                $this-> conn = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
                $this-> conn-> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // create table if not exist
                $this-> conn-> exec('CREATE TABLE IF NOT EXISTS BigTable (id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT, item TEXT, x BIGINT(20) UNSIGNED NOT NULL DEFAULT 0, y BIGINT(20) UNSIGNED NOT NULL DEFAULT 0, timestamp TIMESTAMP, PRIMARY KEY (id));');
                
                // get MODULE id
                // item = MODULE, X=0 Y=0
                $this-> MODULE_ID = $this-> insertIfNotExistWhereItemXY($MODULE, 0, 0);
                $this-> stmt = null;
                
                // get MODEL id
                // item = MODEL, X=0, Y=MODEL_ID
                $this-> MODEL_ID = $this-> insertIfNotExistWhereItemXY($MODEL, 0, $this-> MODULE_ID);
                $this-> stmt = null;
            }
            catch(PDOException $e){
                echo "Connection failed: " . $e->getMessage();
            }
        }
        
        public function close(){
            $this-> stmt = null;
            $this-> conn = null;
        }
        
        // postData
        // data_id: item=id, X=0, Y=MODEL_ID
        // data: X=key_id, Y=data_id
        public function postData($REQUEST){
            // prevent post key=id
            if($REQUEST['id']) return array();
            
            // get keys_id
            foreach($REQUEST as $key => $value){
                $keys[$key] = $this-> insertIfNotExistWhereItemXY($key, $this-> MODEL_ID, 0);
            }
            $this-> stmt = null;
            
            // create data_id
            $dataID = $this-> insertWhereItemXY('id', 0, $this-> MODEL_ID);
            $this-> stmt = null;
            
            // insert data
            foreach($keys as $key => $keyID){
                $this-> insertWhereItemXY($REQUEST[$key], $keyID, $dataID);
            }
            
            $this-> stmt = null;
            return array('id' => $dataID);
        }
        
        public function getData($REQUEST){}
        public function putData($REQUEST){}
        public function deleteData($REQUEST){}
        
        private function insertWhereItemXY($item, $x, $y){
            // prepare statement
            if($this-> stmt === null)
                $this-> stmt = $this-> conn-> prepare("INSERT INTO BigTable (item, x, y) VALUES (:item, :x, :y)");
            
            // bind value
            $this-> stmt-> bindValue(':item', $item, PDO::PARAM_STR);
            $this-> stmt-> bindValue(':x', $x, PDO::PARAM_INT);
            $this-> stmt-> bindValue(':y', $y, PDO::PARAM_INT);
            
            // execute
            $this-> stmt-> execute();
            return $this-> conn-> lastInsertId();;
        }
        
        private function insertIfNotExistWhereItemXY($item, $x, $y){
            $result = $this-> selectIDWhereItemXY($item, $x, $y);
            if(!$result){
                $this-> stmt = null;
                $result = $this-> insertWhereItemXY($item, $x, $y);
                $this-> stmt = null;
            }
            
            return $result;
        }
        
        private function selectIDWhereItemXY($item, $x, $y){
            // prepare statement
            if($this-> stmt === null){
                $this-> stmt = $this-> conn-> prepare("SELECT id FROM BigTable WHERE item=:item AND x=:x AND y=:y");
            }
            
            // bind value
            $this-> stmt-> bindValue(':item', $item, PDO::PARAM_STR);
            $this-> stmt-> bindValue(':x', $x, PDO::PARAM_INT);
            $this-> stmt-> bindValue(':y', $y, PDO::PARAM_INT);
            
            // execute
            $this-> stmt-> execute();
            $result = $this-> stmt-> fetch(PDO::FETCH_ASSOC);
            return $result['id'];
        }
        private function selectWhere(){}
        private function updateWhere(){}
        private function deleteWhere(){}
    }
?>