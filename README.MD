# Custom auto_increment (CuAI)
Customize le système d'auto_incrément mysql depuis PHP
## Fonctionnalités
- Possibilité de préciser une masque
- Customization libre de gauche à droite
## Exemple
```php
    require_once __DIR__.'/src/CustomAI.php';
    use Informagenie\CustomAI;
    $db = new PDO('mysql:host=localhost;dbname=root', 'root', '');
    CustomAI::create(['dsn'=>$db, 'table'=>'Users', 'column'=>'id']);
    CustomAI::$mask = "ETUDIANT_(00000)";
    echo CustomAI::id(); // affiche ETUDIANT_00001
    /*
        Le prochain enregistrement sera ETUDIANT_00002, ETUDIANT_00003, ETUDIANT_00004...ETUDIANT_99999 and ETUDIANT_100000
    */
```

## Installation
### Avec composer
```
composer install informagenie/CustomAI
```
Dans votre fichier PHP
```php
use Informagenie\CustomAI;
```
### Sans Composer

Téléchargez le fichier CustomAI.php et mettez-le dans votre projet
```php
require_once __DIR__.'/dossier-vers/CustomAI.php';
use Informagenie\CustomAI;
```

## Utilisation
### Sans instance
```
    $db = new PDO('mysql:host=localhost;dbname=root', 'root', '');
    CustomAI::create(['dsn'=>$db, 'table'=>'Users', 'column'=>'id']);
    CustomAI::$mask = 'ETUDIANT_(00000)'; // Valeur par default est 201700001
    echo CustomAI::id(); // affiche ETUDIANT_00001
```
### Avec instance
```
    $db = new PDO('mysql:host=localhost;dbname=root', 'root', '');
    $cuai = new CustomAI($db, 'Users', 'id');
    $cuai::$mask = 'ETUDIANT_(00000)';
    echo $cuai->generate() // or $cuai::id()
```

### Masque
La masque doit avoir deux parties. Dans ETUDIANT_(00000), on a :

* ETUDIANT_ : La partie constante qui peut être n'importe quel caractère du fait qu'il peut être enregistré dans votre base de données.
* (00000) : La partie variable qui doit toujours être un chiffre et être entouré par les paranthèses. C'est cette partie qui s'incrémente.

#### Exemple
- () ou (0) : 1, 2...9, 10, 11...99, 100...
- A() : A1, A2...
- A(papa) : A0001, A0002