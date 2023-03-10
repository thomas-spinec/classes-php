<?php
session_start();
// Création des class

class User {
    /* Propriétés */
    private $id;
    public $login;
    private $password;
    public $email;
    public $firstname;
    public $lastname;
    private $bdd;

    /* Constructeur */
    public function __construct() 
    {
        // connection à la BDD
        $this->bdd = new mysqli('localhost', 'root', '', 'classes');

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
        $login = mysqli_real_escape_string($this->bdd,htmlspecialchars($login));
        $password = mysqli_real_escape_string($this->bdd,htmlspecialchars($password));
        $email = mysqli_real_escape_string($this->bdd,htmlspecialchars($email));
        $firstname = mysqli_real_escape_string($this->bdd,htmlspecialchars($firstname));
        $lastname = mysqli_real_escape_string($this->bdd,htmlspecialchars($lastname));

        if($login !== "" && $password !== "" && $email !=="" && $firstname !=="" && $lastname !=="" ){
            $requete = "SELECT count(*) FROM utilisateurs where login = '$login'";
            $exec_requete = $this->bdd -> query($requete);
            $reponse      = mysqli_fetch_assoc($exec_requete);
            $count = $reponse['count(*)'];

            if($count==0){

                // requête pour ajouter l'utilisateur dans la base de données
                $password = password_hash($password, PASSWORD_DEFAULT);
                $requete2 = "INSERT INTO utilisateurs (login, password, email, firstname, lastname) VALUES ('$login', '$password', '$email', '$firstname', '$lastname')";
                $exec_requete2 = $this->bdd -> query($requete2);
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

        mysqli_close($this->bdd); // fermer la connexion
    }

        // Connexion
    public function connect($login, $password)
    {
        if(!$this->isConnected()){
            $login = mysqli_real_escape_string($this->bdd,htmlspecialchars($login));
            $password = mysqli_real_escape_string($this->bdd,htmlspecialchars($password));

            if($login !== "" && $password !== ""){
                $requete = "SELECT count(*) FROM utilisateurs where login = '$login'";
                $exec_requete = $this->bdd -> query($requete);
                $reponse      = mysqli_fetch_assoc($exec_requete);
                $count = $reponse['count(*)'];

                if($count!=0){
                    $requete2 = "SELECT * FROM utilisateurs where login = '$login'";
                    $exec_requete2 = $this->bdd -> query($requete2);
                    $reponse2      = mysqli_fetch_assoc($exec_requete2);
                    $password_hash = $reponse2['password'];

                    if(password_verify($password, $password_hash)){
                        $error = "Connexion réussie";
                        // récupération des données pour les attribuer aux attributs
                        $this->id = $reponse2['id'];
                        $this->login = $reponse2['login'];
                        $this->password = $reponse2['password'];
                        $this->email = $reponse2['email']; 
                        $this->firstname = $reponse2['firstname'];
                        $this->lastname = $reponse2['lastname'];
                        $_SESSION['user']= [
                            'id' => $reponse2['id'],
                            'login' => $reponse2['login'],
                            'password' => $reponse2['password'],
                            'email' => $reponse2['email'],
                            'firstname' => $reponse2['firstname'],
                            'lastname' => $reponse2['lastname']
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
        }
        else{
            $error = "Un utilisateur est déjà connecté";
            return $error; // vous êtes déjà connecté
        }

        mysqli_close($this->bdd); // fermer la connexion
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
            $requete = "DELETE FROM utilisateurs WHERE id = '$this->id'";
            $this->bdd -> query($requete);
            $this->disconnect();
            $error = "Suppression et deconnexion réussies";
            return $error; // suppression réussie

            mysqli_close($this->bdd); // fermer la connexion
        }
        else{
            $error = "Vous n'êtes pas connecté, vous devez être connecté pour supprimer le compte";
            return $error; // utilisateur non connecté
        }
    }

        // Modification
    public function update($login, $password, $email, $firstname, $lastname)
    {
        //vérification que la personne est connecté
        if($this->isConnected()){
            //vérification que les champs ne sont pas vides
            if($login !== "" && $password !== "" && $email !=="" && $firstname !=="" && $lastname !=="" ){

                $requete = "SELECT count(*) FROM utilisateurs where login = '$login'";
                $exec_requete = $this->bdd -> query($requete);
                $reponse      = mysqli_fetch_assoc($exec_requete);
                $count = $reponse['count(*)'];

                if($count==0){
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
                    $requete2 = "UPDATE utilisateurs SET login = '$login', password = '$password', email = '$email', firstname = '$firstname', lastname = '$lastname' WHERE id = '$this->id'";
                    $this->bdd -> query($requete2);

                    $error = "Modification réussie";
                    return $error; // modification réussie
                }
                else{
                    $error = "Le login existe déjà";
                    return $error; // login déjà existant
                }
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
            // requête pour récupérer les données de l'utilisateur dans la base de données
            $requete = "SELECT * FROM utilisateurs WHERE id = '$this->id'";
            $exec_requete = $this->bdd -> query($requete);
            $reponse = mysqli_fetch_assoc($exec_requete);

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
                        <td><?= $reponse['login']; ?></td>
                        <td><?= $reponse['email']; ?></td>
                        <td><?= $reponse['firstname']; ?></td>
                        <td><?= $reponse['lastname']; ?></td>
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
    <title>Les classes (MSQLI)</title>
</head>
<body>
    <h1>Test des classes avec mysqli</h1>
    <?php
        $user = new User();

        // Test du register
        // echo $user->register('test2', 'test2', 'test2@test.com', 'prenom_test2', 'nom_test2');

        // Test du connect
        // echo $user->connect('test2', 'test2');

        // Test du disconnect
        // echo $user->disconnect();

        // Test du delete
        // echo $user->delete();

        // Test du update
        // echo $user->update('test2bis', 'test2bis', 'test2bis@test.com', 'prenom_test2bis', 'nom_test2_bis');

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