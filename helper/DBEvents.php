<?php
    class DBEvents{
        private $MODULE;
        private $MODEL;
        function __construct($MODULE, $MODEL){
            $this-> MODULE = $MODULE;
            $this-> MODEL  = $MODEL;
            
            // connect database
        }
        
        public function postData(){}
        public function getData(){}
        public function putData(){}
        public function deleteData(){}
        
        private function insertWhere($item, $x, $y){}
        private function selectWhere(){}
        private function updateWhere(){}
        private function deleteWhere(){}
    }
?>