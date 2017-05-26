#Custom auto_increment (CuUI)
Customize le système d'auto_incrément mysql depuis PHP
##Fonctionnalités
- Possibilité de préciser une masque
- Customization libre à de gauche à droite
##Exemple
    ```
        require __DIR__.'/src/CustomAI.php';
        use Informagenie\CustomAI;
        $db = new PDO('mysql:host=localhost;dbname=root', 'root', '');
        $cuui = new CustomAI($db, 'my_table', 'my_auto_increment_column');
        $id = $cuui->generate();
    ```
