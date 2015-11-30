<?php
    require_once($_SERVER["DOCUMENT_ROOT"].'/helper/DBEvents.php');
    class APIToken{
        // Structure in memcache = {
        //      '<token string>': {
        //          id : 1,
        //          Roles: ['SuperAdmin','Public','Normal']
        //      }
        // }
        
        // private $ttl = 259200; // 3 days
        private $ttl = 600; // 600 seconds
        
        public function createToken($id, $obj){
            $token = $this-> generateToken($id);
            apc_add($token, array('id'=>$id, 'Roles'=>$obj), $this->ttl);
            return urlencode($token);
        }
        public function readToken($token){
            if($token){
                $token = urldecode($token);
                $obj = apc_fetch($token);
                $this-> updateToken($token, $obj);
                return $obj;
            }
            else{ 
                // error_log('no token present');
                return array();
            }
        }
        public function updateToken($token, $obj){
            $token = urldecode($token);
            apc_store($token, $obj, $this->ttl);
        }
        public function deleteToken($token){
            $token = urldecode($token);
            apc_delete($token);
        }
        public function getAuthId($token){
            if($token === null) return 0;
            
            $token = urldecode($token);
            $obj = apc_fetch($token);
            return $obj['id'];
        }
        public function checkAutorization($token, $model, $action){
            // If for public have access, then dont need to check for other roles
            $public = apc_fetch('Public');
            if(!$public){
                // Fetch from database
                $db = new DBEvents('auth', 'role');
                $results = $db-> getData(array());
                
                // var_dump($results);
                
                foreach($results as $result){
                    $roleName = '';
                    $roleObject = array();
                    foreach($result as $key=>$value){
                        switch($key){
                            case 'id': break;
                            case 'Name':
                                $roleName = $value;
                                break;
                            case 'timestamp': break;
                            case 'creator': break;
                            
                            // Models go here
                            default:
                                $roleObject[$key] = json_decode($value, true);
                                break;
                        }
                    }
                    $addResult = apc_add($roleName, $roleObject, 600);
                    if(!$addResult) apc_store($roleName, $roleObject, 600);
                }
                $public = apc_fetch('Public');
            };
            
            // If model is not for public, then check for other roles provided in token
            if($public[$model][$action]) return true;
            else{ 
                $obj = $this-> readToken($token);
                if($obj){
                    foreach($obj['Roles'] as $role){
                        $roleObject = apc_fetch($role);
                        if($roleObject[$model][$action]) return true;
                    }
                }
            }
            return false;
        }
        
        private function generateToken($id){
            return password_hash($id, PASSWORD_DEFAULT);
        }
        
        private function verifyToken($id, $token){
            $token = urldecode($token);
            if(apc_exists($token)){
                if(password_verify($id, $token)){
                    $obj    = apc_fetch($token);
                    $token  = urlencode($token);
                    $this-> updateToken($token, $obj);
                    return true;
                }
            }
            return false;
        }
    }
?>