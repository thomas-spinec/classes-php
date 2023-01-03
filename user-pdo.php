<?php
session_start();
// Création des class

class Userpdo {
    /* Propriétés */
    private $id;
    private $login;
    private $password;
    private $email;
    private $firstname;
    private $lastname;
    private $bdd;

    /* Constructeur */
    public function __construct() 
    {
        // connection à la BDD avec PDO
        $servername = 'localhost';
        $dbname = 'classes';
        $db_username = 'root';
        $db_password = '';

        // essaie de connexion
        try {
            $this->bdd = new PDO("mysql:host=$servername;dbname=$dbname; charset=utf8", $db_username, $db_password);

            // On définit le mode d'erreur de PDO sur Exception
            $this->bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //echo "Connexion réussie"; 
        } 
        // si erreur, on capture les exceptions, s'il y en a une on affiche les infos
        catch(PDOException $e)
        {
            echo "Echec de la connexion : " . $e->getMessage();
            exit;
        }


        // Vérification de la connexion
        if (isset($_SESSION['user'])){
            $this->id = $_SESSION['user']['id'];
            $this->login = $_SESSION['user']['login'];
            $this->password = $_SESSION['user']['password'];
            $this->email = $_SESSION['user']['email'];
            $this->firstname = $_SESSION['user']['firstname'];
            $this->lastname = $_SESSION['user']['lastname'];
        }
    }

    /* Méthodes */
        // Enregistrement
    public function register($login, $password, $email, $firstname, $lastname)
    {
        if($login !== "" && $password !== "" && $email !=="" && $firstname !=="" && $lastname !=="" ){
            // requête
            $requete = "SELECT * FROM utilisateurs where login = :login";

            // préparation de la requête
            $select = $this->bdd->prepare($requete);

            // exécution de la requête avec liaison des paramètres
            $select-> execute(array(':login' => $login));

            // récupération du tableau
            $fetch_all = $select->fetchAll();

            if(count($fetch_all) === 0){ // si = 0 --> utilisateur disponible

                // hachage du mot de passe
                $password = password_hash($password, PASSWORD_DEFAULT);

                // requête pour ajouter l'utilisateur dans la base de données
                $requete2 = "INSERT INTO utilisateurs (login, password, email, firstname, lastname) VALUES (:login, :password, :email, :firstname, :lastname)";

                // préparation de la requête
                $insert = $this->bdd -> prepare($requete2);

                // exécution de la requête avec liaison des paramètres
                $insert-> execute(array(
                    ':login' => $login,
                    ':password' => $password,
                    ':email' => $email,
                    ':firstname' => $firstname,
                    ':lastname' => $lastname));

                $error = "Inscription réussie";
                return $error; // inscription réussie
            }
            else{
                $error = "Utilisateur déjà existant";
                return $error; // utilisateur déjà existant
            }
        }
        else{
            $error = "Tous les champs ne sont pas renseignés, il faut le login, le mot de passe, l'email, le prénom et le nom";
            return $error; // utilisateur ou mot de passe vide
        }
        // fermer la connexion
        $this->bdd = null;
    }

        // Connexion
    public function connect($login, $password)
    {
        if($login !== "" && $password !== ""){
            // requête
            $requete = "SELECT * FROM utilisateurs where login = :login";

            // préparation de la requête
            $select = $this->bdd->prepare($requete);

            // exécution de la requête avec liaison des paramètres
            $select-> execute(array(':login' => $login));

            // récupération du tableau
            $fetch_all = $select->fetchAll();

            if(count($fetch_all) > 0){ // utilisateur existant
                
                // récupération du mot de passe avec ASSOC
                $select-> execute(array(':login' => $login));
                $fetch_assoc = $select->fetch(PDO::FETCH_ASSOC);
                $password_hash = $fetch_assoc['password'];

                if(password_verify($password, $password_hash)){
                    $error = "Connexion réussie";
                    // récupération des données pour les attribuer aux attributs
                    $this->id = $fetch_assoc['id'];
                    $this->login = $fetch_assoc['login'];
                    $this->password = $fetch_assoc['password'];
                    $this->email = $fetch_assoc['email']; 
                    $this->firstname = $fetch_assoc['firstname'];
                    $this->lastname = $fetch_assoc['lastname'];

                    $_SESSION['user']= [
                        'id' => $fetch_assoc['id'],
                        'login' => $fetch_assoc['login'],
                        'password' => $fetch_assoc['password'],
                        'email' => $fetch_assoc['email'],
                        'firstname' => $fetch_assoc['firstname'],
                        'lastname' => $fetch_assoc['lastname']
                    ];
                    return $error; // connexion réussie
                }
                else{
                    $error = "Mot de passe incorrect";
                    return $error; // mot de passe incorrect
                }
            }
            else{
                $error = "Utilisateur inexistant";
                return $error; // utilisateur inexistant
            }
        }
        else{
            $error = "Tous les champs ne sont pas renseignés, il faut le login et le mot de passe";
            return $error; // utilisateur ou mot de passe vide
        }
        // fermer la connexion
        $this->bdd = null;
    }

        // Déconnexion
    public function disconnect()
    {
        // verifier la connexion
        if($this->isConnected()){
            // rendre les attributs null
            $this->id = null;
            $this->login = null;
            $this->password = null;
            $this->email = null;
            $this->firstname = null;
            $this->lastname = null;

            // détruire la session
            session_destroy();

            $error = "Déconnexion réussie";
            return $error; // déconnexion réussie
        }
        else{
            $error = "Vous n'êtes pas connecté";
            return $error; // vous n'êtes pas connecté
        }
    }

        // Suppression
    public function delete()
    {
        //vérification que la personne est connecté
        if($this->isConnected()){
            // requête pour supprimer l'utilisateur dans la base de données
            $requete = "DELETE FROM utilisateurs WHERE id = :id";
            // préparation de la requête
            $delete = $this->bdd->prepare($requete);
            // exécution de la requête avec liaison des paramètres
            $delete-> execute(array(':id' => $this->id));

            $this->disconnect();
            $error = "Suppression et deconnexion réussies";
            return $error; // suppression réussie
        }
        else{
            $error = "Vous n'êtes pas connecté, vous devez être connecté pour supprimer le compte";
            return $error; // utilisateur non connecté
        }
        // fermer la connexion
        $this->bdd = null;
    }

        // Modification
    public function update($login, $password, $email, $firstname, $lastname)
    {
        //vérification que la personne est connecté
        if($this->isConnected()){
            //vérification que les champs ne sont pas vides
            if($login !== "" && $password !== "" && $email !=="" && $firstname !=="" && $lastname !=="" ){

                $password = password_hash($password, PASSWORD_DEFAULT);
                // récupération des données pour les attribuer aux attributs
                $_SESSION['user']= [
                    'id' => $this->id,
                    'login' => $login,
                    'password' => $password,
                    'email' => $email,
                    'firstname' => $firstname,
                    'lastname' => $lastname
                ];

                // requête pour modifier l'utilisateur dans la base de données
                $requete = "UPDATE utilisateurs SET login = :login, password = :password, email = :email, firstname = :firstname, lastname = :lastname WHERE id = :id";
                // préparation de la requête
                $update = $this->bdd->prepare($requete);
                // exécution de la requête avec liaison des paramètres
                $update-> execute(array(
                    ':id' => $this->id,
                    ':login' => $login, 
                    ':password' => $password, 
                    ':email' => $email, 
                    ':firstname' => $firstname, 
                    ':lastname' => $lastname));

                $error = "Modification réussie";
                return $error; // modification réussie
            }
            else{
                $error = "Tous les champs ne sont pas renseignés, il faut le login, le mot de passe, l'email, le prénom et le nom";
                return $error; // utilisateur ou mot de passe vide
            }
        }
        else{
            $error = "Vous n'êtes pas connecté, vous devez être connecté pour modifier le compte";
            return $error; // utilisateur non connecté
        }
    }

        // Vérification de la connexion
    public function isConnected()
    {
        if($this->id !== null && $this->login !== null && $this->password !== null && $this->email !== null && $this->firstname !== null && $this->lastname !== null){
            return true; // utilisateur connecté
        }
        else{
            return false; // utilisateur non connecté
        }
    }

        // Récupération des données
    public function getAllInfos()
    {
        //vérification que la personne est connecté
        if($this->isConnected()){
            //affichage
            ?>
            <table>
                <thead>
                    <tr>
                        <th>login</th>
                        <th>email</th>
                        <th>firstname</th>
                        <th>lastname</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= $this->login; ?></td>
                        <td><?= $this->email; ?></td>
                        <td><?= $this->firstname; ?></td>
                        <td><?= $this->lastname; ?></td>
                    </tr>
            </table>
            <?php
        }
        else{
            echo "Vous n'êtes pas connecté, vous devez être connecté pour voir les informations du compte";
        }
    }

        // Récupération du login
    public function getLogin()
    {
        //vérification que la personne est connecté
        if($this->isConnected()){
            ?>
            <p><strong>Votre login est: </strong><?= $this->login; ?></p>
            <?php
        }
        else{
            echo "Vous n'êtes pas connecté, vous devez être connecté pour voir le login du compte";
        }
    }

        // Récupération de l'email
    public function getEmail()
    {
        //vérification que la personne est connecté
        if($this->isConnected()){
            ?>
            <p><strong>Votre email est: </strong><?= $this->email; ?></p>
            <?php
        }
        else{
            echo "Vous n'êtes pas connecté, vous devez être connecté pour voir l'email du compte";
        }
    }

        // Récupération du prénom
    public function getFirstname()
    {
        //vérification que la personne est connecté
        if($this->isConnected()){
            ?>
            <p><strong>Votre prénom est: </strong><?= $this->firstname; ?></p>
            <?php
        }
        else{
            echo "Vous n'êtes pas connecté, vous devez être connecté pour voir le prénom du compte";
        }
    }

        // Récupération du nom
    public function getLastname()
    {
        //vérification que la personne est connecté
        if($this->isConnected()){
            ?>
            <p><strong>Votre nom est: </strong><?= $this->lastname; ?></p>
            <?php
        }
        else{
            echo "Vous n'êtes pas connecté, vous devez être connecté pour voir le nom du compte";
        }
    }

}

/* *************
    TEST 
************** */
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Les classes (PDO)</title>
</head>
<body>
    <h1>Test des classes avec PDO</h1>
    <?php
        $user = new Userpdo();

        // Test du register
        // echo $user->register('testPDO2', 'testPDO2', 'testPDO2@test.com', 'prenom_testPDO2', 'nom_testPDO2');

        // Test du connect
        // echo $user->connect('testPDO2', 'testPDO2');

        // Test du disconnect
        // echo $user->disconnect();

        // Test du delete
        // echo $user->delete();

        // Test du update
        // echo $user->update('testPDO2bis', 'testPDO2bis', 'testPDO2bis@test.com', 'prenom_testPDO2bis', 'nom_testPDO2_bis');

        // Test du isConnected
        // echo $user->isConnected();

        // Test du getAllInfos
        // echo $user->getAllInfos();

        // Test du getLogin
        // echo $user->getLogin();

        // Test du getEmail
        // echo $user->getEmail();

        // Test du getFirstname
        // echo $user->getFirstname();

        // Test du getLastname
        // echo $user->getLastname();
    ?>
</body>
</html>