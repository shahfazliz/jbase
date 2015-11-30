<?php
    class StringManipulator{
        public function ToCamelCase($stringObject){
            return str_replace(" ", "", ucwords($stringObject));
        }
    }
?>