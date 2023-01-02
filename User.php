<?php 
    // function connection_bdd() {
    //     // Connexion à la base de données en local
    //     $sqli = new mysqli('localhost', 'root', '', 'classes');
    //     return $sqli;
    // }
?>

<?php
// Création des class

class User {
    /* Propriétés */
    private int $id;
    public string $login;
    private string $password;
    public string $email;
    public string $firstname;
    public string $lastname;
    private $bdd;

    /* Constructeur */
    public function __construct() 
    {
        // connection à la BDD
        $this->bdd = new mysqli('localhost', 'root', '', 'classes');
    }

    /* Méthodes */

    public function register($login, $password, $email, $firstname, $lastname)
    {
        // Enregistrement
        $connect = $this->bdd;
        $login = mysqli_real_escape_string($connect,htmlspecialchars($login));
        $password = mysqli_real_escape_string($connect,htmlspecialchars($password));
        $email = mysqli_real_escape_string($connect,htmlspecialchars($email));
        $firstname = mysqli_real_escape_string($connect,htmlspecialchars($firstname));
        $lastname = mysqli_real_escape_string($connect,htmlspecialchars($lastname));

        if($login !== "" && $password !== "" && $email !=="" && $firstname !=="" && $lastname !=="" ){
            $requete = "SELECT count(*) FROM utilisateurs where login = '$login'";
            $exec_requete = $connect -> query($requete);
            $reponse      = mysqli_fetch_assoc($exec_requete);
            $count = $reponse['count(*)'];

            if($count==0){

                // requête pour ajouter l'utilisateur dans la base de données
                $password = password_hash($password, PASSWORD_DEFAULT);
                $requete = "INSERT INTO utilisateurs (login, password, email, firstname, lastname) VALUES ('$login', '$password', '$email', '$firstname', '$lastname')";
                $exec_requete = $connect -> query($requete);
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

        mysqli_close($connect); // fermer la connexion
    }

    public function connect($login, $password)
    {
        // Connexion
        $connect = $this->bdd;
        $login = mysqli_real_escape_string($connect,htmlspecialchars($login));
        $password = mysqli_real_escape_string($connect,htmlspecialchars($password));

        if($login !== "" && $password !== ""){
            $requete = "SELECT count(*) FROM utilisateurs where login = '$login'";
            $exec_requete = $connect -> query($requete);
            $reponse      = mysqli_fetch_assoc($exec_requete);
            $count = $reponse['count(*)'];

            if($count!=0){
                $requete = "SELECT * FROM utilisateurs where login = '$login'";
                $exec_requete = $connect -> query($requete);
                $reponse      = mysqli_fetch_assoc($exec_requete);
                $password_hash = $reponse['password'];

                if(password_verify($password, $password_hash)){
                    $error = "Connexion réussie";
                    // récupération des données pour les attribuer aux attributs
                    $this->id = $reponse['id'];
                    $this->login = $reponse['login'];
                    $this->password = $reponse['password'];
                    $this->email = $reponse['email']; 
                    $this->firstname = $reponse['firstname'];
                    $this->lastname = $reponse['lastname'];
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

        mysqli_close($connect); // fermer la connexion
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
    <title>Document</title>
</head>
<body>
    <?php
        $user = new User();

        // Test du register
        // echo $user->register('test2', 'test2', 'test2@test.com', 'prenom_test2', 'nom_test2');

        // Test du connect
        // echo $user->connect('test2', 'test2');
    ?>
</body>
</html>