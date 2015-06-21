## JBase

Aku buat JBase sebab aku tau mau buat lagi dah back end programming 
yang sama. Aku nak focus kat building application and focus on the 
business rules. Which is the Model in MCV. View and Controller boleh 
pakai Angular.js

Aku pilih unutk code dalam PHP (walaupun tak berapa pandai 
pakai PHP), sebab nak beli shared service server lebih murah dari 
java based server (walaupun aku lagi familier dengan Java Programming
Languge siap ada Oracle Certified Associate Programmer). Lagi satu
PHP ni lebih common dalam pasaran kalau banding dengan Python ataupun
Ruby.

### Benda-benda nak upgrade

1. Maybe nak buat user login sendiri insted of bagi programmer buat
   sendiri. Programmer boleh buat User Model, tapi tak perlu buat
   custom login. so each User ada login_id (maybe?) untuk match
   identity.

2. Since ada login sendiri, senang nak buat session+cookie untuk
   login presist untuk seminggu (maybe?) unless logout early.

### Cara nak pakai

1. Extract content folder 'jbase' dalam workspace/www
2. Dalam folder workspace/www, create folder untuk module.
3. Dalam folder module, create folder model.

>    eg: /www/module/model
>    eg: /www/accounting/invoice
>    eg: /www/accounting/receipt

4. Dalam each forlder model, create index.php
5. Dalam index.php, create
   
```php 
    <?php
        header('Content-Type: application/json'); // JSON
        header("access-control-allow-origin: *"); // Cross-Origin Resource Sharing (CORS)
    
        require_once($_SERVER["DOCUMENT_ROOT"].'/helper/PHPServlet.php');
        require_once($_SERVER["DOCUMENT_ROOT"].'/helper/DBEvents.php');

        class NamaModelKorang extends PHPServlet{
        
            public function doPOST  ($REQUEST){ ... Your code here ... }
            public function doGET   ($REQUEST){ ... Your code here ... }
            public function doPUT   ($REQUEST){ ... Your code here ... }
            public function doDELETE($REQUEST){ ... Your code here ... }
        }
        new NamaModelKorang();
    ?>
```
6. Tukar la nama class NamaModelKorang dengan apa-apa. Aku recomendkan
   guna name model.
7. So setiap kali index.php dipanggil dengan apa-apa http request,
   new Product() akan instanciate new object, since Product class
   extends PHPServlet class, constructor class tu akan dipanggil
   jugak. Constructor tu akan detect apa jenis http request, then
   execute doPOST/doGET/doPUT/doDELETE.
8. Sebab tu aku tulis kat atas "... Your code here ..." sebab nak kena
   override function parent. Aku suggest:

```php
        $db     = new DBEvents(parent::getModule(), parent::getModel());
        $result = $db-> postData($REQUEST);
        $json   = json_encode($result);
        
        // Echo json OR jsonp
        echo parent::returnJSONPResponse($REQUEST, $json);
```

9. Tu paling basic, tapi real life, mungkin kena instanciate lebih
   database object ($db) untuk postData lain, dapatkan id dia, then
   push() dalam array data yg nak postData() dalam $db lain. eg:

```php
        $db1     = new DBEvents("ModuleLainOrSama", "ModelLain");
        $result1 = $db-> postData(array("Key1": "Value1", "Key2": "Value2"));
        
        $db2     = new DBEvents(parent::getModule(), parent::getModel());
        $result2 = $db-> postData($REQUEST["key_id"] = $result1);
        
        $json   = json_encode($result);
        
        // Echo json OR jsonp
        echo parent::returnJSONPResponse($REQUEST, $json);
```

10. check dulu dengan echo var_dump($result2) make sure formatting
    tu betul baru guna postData sbb dia juma ambil key pair value ja.