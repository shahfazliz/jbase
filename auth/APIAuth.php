<?php
    require_once($_SERVER["DOCUMENT_ROOT"].'/auth/APIToken.php');
    
    class APIAuth{
        private $stmt = null;
        private $conn = null;
        
        function __construct(){
            $username   = 'shahfazliz';
            $password   = '';
            $host       = '127.0.0.1';
            $port       = '3306';
            $database   = 'c9';
        
            // connect database
            try {
                $this-> conn = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
                $this-> conn-> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // create APIAuth if not exist
                $this-> conn-> exec('CREATE TABLE IF NOT EXISTS APIAuth (id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT, Username VARCHAR(255) NOT NULL UNIQUE, Password VARCHAR(100) NOT NULL, Roles TEXT, timestamp TIMESTAMP, PRIMARY KEY (id));');
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
        public function postData($REQUEST){
            // prepare statement
            if($this-> stmt === null)
                $this-> stmt = $this-> conn-> prepare("INSERT INTO APIAuth (Username, Password, Roles) VALUES (:username, :password, :roles)");
                
            // bind value
            $this-> stmt-> bindValue(':username', $REQUEST['Username'], PDO::PARAM_STR);
            $this-> stmt-> bindValue(':password', $this-> encryptPassword($REQUEST['Password']), PDO::PARAM_STR);
            
            $roles = explode(',',$REQUEST['Roles']);
            if(!in_array('RegisteredUser', $roles)) $roles[] = 'RegisteredUser';
            $REQUEST['Roles'] = implode(',',$roles);
            $this-> stmt-> bindValue(':roles', $REQUEST['Roles'], PDO::PARAM_STR);
            
            // execute
            $this-> stmt-> execute();
            $this-> stmt = null;
            return array('id' => $this-> conn-> lastInsertId());
        }
        
        // getData
        public function getData($REQUEST){
            // test if $REQUEST['id']  exist
            switch($REQUEST['id'] === null){
                // id is not provided
                case true:
                    return false;
                    break;
                
                // id provided
                case false:
                    // prepare statement
                    if($this-> stmt === null){
                        $this-> stmt = $this-> conn-> prepare("SELECT * FROM APIAuth WHERE id=:id");
                    }
                    
                    // bind value
                    $this-> stmt-> bindValue(':id', $REQUEST['id'], PDO::PARAM_INT);
                    
                    // execute
                    $this-> stmt-> execute();
                    return $this-> stmt-> fetch(PDO::FETCH_ASSOC);
                    break;
            }
        }
        
        // putData
        public function putData($REQUEST){
            // test if $REQUEST['id'] even exist
            switch($REQUEST['id'] === null){
                // id is not provided but username and password is
                case true:
                    // prepare statement
                    if($this-> stmt === null){
                        $this-> stmt = $this-> conn-> prepare("SELECT id, Password, Roles FROM APIAuth WHERE Username=:username");
                    }
                    
                    // bind value
                    $this-> stmt-> bindValue(':username', $REQUEST['Username'], PDO::PARAM_STR);
                    
                    // execute
                    $this-> stmt-> execute();
                    $result = $this-> stmt-> fetch(PDO::FETCH_ASSOC);
                    
                    switch($this-> verifyPassword($REQUEST['Password'], $result['Password'])){
                        case true:
                            $response = [];
                            $response['id'] = $result['id'];
                            
                            $apiToken = new APIToken();
                            $response['APIToken'] = $apiToken-> createToken($result['id'], explode(',', $result['Roles']));
                            
                            return $response;
                            break;
                        case false:
                            return false;
                            break;
                    }
                    break;
                    
                // id is provided, assuming edit data
                case false:
                    try{
                        $item   = '';
                        
                        if($REQUEST['Username']){
                            $item = $REQUEST['Username'];
                            
                            // prepare statement
                            if($this-> stmt === null){
                                $this-> stmt = $this-> conn-> prepare("UPDATE APIAuth SET Username=:item WHERE id=:id");
                            }
                        }
                        
                        else if($REQUEST['Password']){
                            $item = $this-> encryptPassword($REQUEST['Password']);
                            
                            // prepare statement
                            if($this-> stmt === null){
                                $this-> stmt = $this-> conn-> prepare("UPDATE APIAuth SET Password=:item WHERE id=:id");
                            }
                        }
                        
                        // bind value
                        $this-> stmt-> bindValue(':item', $item, PDO::PARAM_STR);
                        $this-> stmt-> bindValue(':id', $REQUEST['id'], PDO::PARAM_INT);
                        
                        // execute
                        $this-> stmt-> execute();
                        $this-> stmt = null;
                        
                        return true;
                            
                    }catch(PDOException $e){
                        echo "Connection failed: " . $e->getMessage();
                        return false;
                    }
                    break;
            }
        }
        
        public function deleteData($REQUEST){
            // test if $REQUEST['id'] even exist
            if($REQUEST['id'] === null) return false;
            
            try{
                // prepare statement
                if($this-> stmt === null){
                    $this-> stmt = $this-> conn-> prepare("DELETE FROM APIAuth WHERE id=:id");
                }
                
                // bind value
                $this-> stmt-> bindValue(':id', $REQUEST['id'], PDO::PARAM_INT);
                
                // execute
                $this-> stmt-> execute();
                return true;
            }catch(PDOException $e){
                echo "Connection failed: " . $e->getMessage();
                return false;
            }
        }
        
        private function encryptPassword($password){
            return password_hash($password, PASSWORD_DEFAULT);
        }
        
        private function verifyPassword($password, $hashedPassword){
            return password_verify($password, $hashedPassword);
        }
    }
?>