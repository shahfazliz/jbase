<?php
    class CacheEvents{
        private $TTL = 259200; // 3 days
        
        public function create($key, $obj, $ttl = 259200){
            apc_add($key, $obj, $ttl);
        }
        public function read($key){
            return apc_fetch($key);
        }
        public function update($key, $obj, $ttl = 259200){
            apc_store($key, $obj, $ttl);
        }
        public function delete($key){
            apc_delete($key);
        }
    }
?>