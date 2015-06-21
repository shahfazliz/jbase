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
        
        // getData
        // data_id: item=id, X=0, Y=MODEL_ID
        // data: X=key_id, Y=data_id
        public function getData($REQUEST){
            switch($REQUEST['id']){
                case !null:
                    
                    // test if the supplied id exists
                    $data = $this-> selectWhereID($REQUEST['id']);
                    $this-> stmt = null;
                    if($data['item'] === 'id'){
                        
                        // get keyIDs of this model
                        $keys = $this-> selectAllWhereXY($this-> MODEL_ID, 0);
                        $this-> stmt = null;
                        
                        $result['id'] = $data['id'];
                        foreach($keys as $key){
                            $item = $this-> selectItemWhereXY($key['id'], $data['id']);
                            $result[$key['item']] = $item['item'];
                        }
                        $this-> stmt = null;
                        $result['timestamp'] = $data['timestamp'];
                        return $result;
                    }
                    break;
                
                default:
                    $result = array();
                    foreach($REQUEST as $key => $value){
                        // get key_id
                        $keyID = $this-> selectIDWhereItemXY($key, $this-> MODEL_ID, 0);
                        $this-> stmt = null;
                        
                        // get data_id where data X=key_id
                        $datas = $this-> selectYsWhereX($keyID);
                        $this-> stmt = null;
                        
                        foreach($datas as $data){
                            $query['id'] = $data['y'];
                            $result[] = $this-> getData($query);
                        }
                    }
                    return $result;
                    break;
            }
        }
        
        // getData
        // data: X=key_id, Y=data_id
        public function putData($REQUEST){
            try{
                // test if the supplied id exists
                $data = $this-> selectWhereID($REQUEST['id']);
                $this-> stmt = null;
                if($data['item'] === 'id'){
                    
                    // get keyIDs
                    $requestQuery = $REQUEST;
                    unset($requestQuery['id']);
                    foreach($requestQuery as $key => $value){
                        $keys[$key] = $this-> insertIfNotExistWhereItemXY($key, $this-> MODEL_ID, 0);
                    }
                    $this-> stmt = null;
                    
                    foreach($requestQuery as $key => $value){
                        $this-> updateItemWhereXY($value, $keys[$key], $REQUEST['id']);
                    }
                    $this-> stmt = null;
                    return true;
                }
            }catch(PDOException $e){
                echo "Connection failed: " . $e->getMessage();
                return false;
            }
        }
        
        public function deleteData($REQUEST){
            // test if $REQUEST['id'] even exist
            if($REQUEST['id'] === null) return false;
            
            try{
                // test if the supplied id exists
                $data = $this-> selectWhereID($REQUEST['id']);
                $this-> stmt = null;
                if($data['item'] === 'id'){
                    $this-> deleteWhereID($REQUEST['id']);
                    $this-> stmt = null;
                    
                    $this-> deleteWhereY($REQUEST['id']);
                    $this-> stmt = null;
                }
                return true;
            }catch(PDOException $e){
                echo "Connection failed: " . $e->getMessage();
                return false;
            }
        }
        
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
        
        private function insertIfNotExistWhereXY($item, $x, $y){
            $result = $this-> selectIDWhereXY($x, $y);
            if(!$result){
                $this-> stmt = null;
                $result = $this-> insertWhereItemXY($item, $x, $y);
                $this-> stmt = null;
            }
            
            return $result;
        }
        
        private function selectYsWhereX($x){
            // prepare statement
            if($this-> stmt === null){
                $this-> stmt = $this-> conn-> prepare("SELECT y FROM BigTable WHERE x=:x");
            }
            
            // bind value
            $this-> stmt-> bindValue(':x', $x, PDO::PARAM_INT);
            
            // execute
            $this-> stmt-> execute();
            return $this-> stmt-> fetchAll(PDO::FETCH_ASSOC);;
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
        
        private function selectIDWhereXY($x, $y){
            // prepare statement
            if($this-> stmt === null){
                $this-> stmt = $this-> conn-> prepare("SELECT id FROM BigTable WHERE x=:x AND y=:y");
            }
            
            // bind value
            $this-> stmt-> bindValue(':x', $x, PDO::PARAM_INT);
            $this-> stmt-> bindValue(':y', $y, PDO::PARAM_INT);
            
            // execute
            $this-> stmt-> execute();
            $result = $this-> stmt-> fetch(PDO::FETCH_ASSOC);
            return $result['id'];
        }
        
        private function selectAllWhereXY($x, $y){
            // prepare statement
            if($this-> stmt === null){
                $this-> stmt = $this-> conn-> prepare("SELECT * FROM BigTable WHERE x=:x AND y=:y");
            }
            
            // bind value
            $this-> stmt-> bindValue(':x', $x, PDO::PARAM_INT);
            $this-> stmt-> bindValue(':y', $y, PDO::PARAM_INT);
            
            // execute
            $this-> stmt-> execute();
            return $this-> stmt-> fetchAll(PDO::FETCH_ASSOC);
        }
        
        private function selectItemsWhereXY($x, $y){
            // prepare statement
            if($this-> stmt === null){
                $this-> stmt = $this-> conn-> prepare("SELECT item FROM BigTable WHERE x=:x AND y=:y");
            }
            
            // bind value
            $this-> stmt-> bindValue(':x', $x, PDO::PARAM_INT);
            $this-> stmt-> bindValue(':y', $y, PDO::PARAM_INT);
            
            // execute
            $this-> stmt-> execute();
            return $this-> stmt-> fetchAll(PDO::FETCH_ASSOC);
        }
        
        private function selectItemWhereXY($x, $y){
            // prepare statement
            if($this-> stmt === null){
                $this-> stmt = $this-> conn-> prepare("SELECT item FROM BigTable WHERE x=:x AND y=:y");
            }
            
            // bind value
            $this-> stmt-> bindValue(':x', $x, PDO::PARAM_INT);
            $this-> stmt-> bindValue(':y', $y, PDO::PARAM_INT);
            
            // execute
            $this-> stmt-> execute();
            return $this-> stmt-> fetch(PDO::FETCH_ASSOC);
        }
        
        private function selectWhereID($id){
            // prepare statement
            if($this-> stmt === null){
                $this-> stmt = $this-> conn-> prepare("SELECT * FROM BigTable WHERE id=:id");
            }
            
            // bind value
            $this-> stmt-> bindValue(':id', $id, PDO::PARAM_INT);
            
            // execute
            $this-> stmt-> execute();
            return $this-> stmt-> fetch(PDO::FETCH_ASSOC);
        }
        
        private function updateItemWhereXY($item, $x, $y){
            // prepare statement
            if($this-> stmt === null){
                $this-> stmt = $this-> conn-> prepare("UPDATE BigTable SET item=:item WHERE x=:x AND y=:y");
            }
            
            // bind value
            $this-> stmt-> bindValue(':item', $item, PDO::PARAM_STR);
            $this-> stmt-> bindValue(':x', $x, PDO::PARAM_INT);
            $this-> stmt-> bindValue(':y', $y, PDO::PARAM_INT);
            
            // execute
            $this-> stmt-> execute();
            $this-> stmt = null;
            
            $this-> insertIfNotExistWhereXY($item, $x, $y);
            $this-> stmt = null;
        }
        
        private function deleteWhereID($id){
            // prepare statement
            if($this-> stmt === null){
                $this-> stmt = $this-> conn-> prepare("DELETE FROM BigTable WHERE id=:id");
            }
            
            // bind value
            $this-> stmt-> bindValue(':id', $id, PDO::PARAM_INT);
            
            // execute
            $this-> stmt-> execute();
        }
        
        private function deleteWhereY($y){
            // prepare statement
            if($this-> stmt === null){
                $this-> stmt = $this-> conn-> prepare("DELETE FROM BigTable WHERE y=:y");
            }
            
            // bind value
            $this-> stmt-> bindValue(':y', $y, PDO::PARAM_INT);
            
            // execute
            $this-> stmt-> execute();
        }
    }
?>