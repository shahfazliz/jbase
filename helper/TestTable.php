<html><head><title>MySQL Table Viewer</title></head><body>
<?php
    $username   = 'shahfazliz';
    $password   = '';
    $host       = '127.0.0.1';
    $port       = '3306';
    $database   = 'c9';

    $conn = null;
    $stmt = null;
    
    // connect database
    try {
        $conn = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
        $conn-> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // prepare statement
        $stmt = $conn-> prepare("SELECT * FROM BigTable");
        
        // execute
        $stmt-> execute();
        $obj = $stmt-> fetchAll(PDO::FETCH_ASSOC);
        
        echo '<table><tr><th>id</th><th>item</th><th>x</th><th>y</th><th>timestamp</th><th>creator</th></tr>';
        foreach($obj as $value){
            echo '<tr>';
            foreach($value as $key=>$val){
                echo '<td style="border:1px solid black;">', $val, '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
    }
    catch(PDOException $e){
        echo "Connection failed: " . $e->getMessage();
    }
?>
</body></html>