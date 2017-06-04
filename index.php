<?php
require __DIR__ . '/src/CustomAI.php';

use Informagenie\CustomAI;

$db = new PDO('sqlite:database.sqlite');

$cuai = new CustomAI($db, 'Person', 'id', '2018(0000)');
echo $cuai->auto_increment();
if (!empty($_POST)) {
    $req = $db->prepare('INSERT INTO Person VALUES(?, ?, ?)');
    $success = $req->execute(array($cuai::auto_increment(), $_POST['nom'], $_POST['postnom']));
}

?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test Custom AutoIncrement</title>
</head>
<body>
<form method="post" action="">
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">Votre enresitrement a été effectué avec succès</div>
    <?php endif ?>
    <div class="form-group">
        <label for="nom">Entrez votre nom :</label>
        <input type="text" name="nom" class="form-control">
    </div>
    <div class="form-group">
        <label for="nom">Entrez votre postnom :</label>
        <input type="text" name="postnom" class="form-control">
    </div>
    <input type="submit" value="Enregistrer">
</form>
</body>
</html>
