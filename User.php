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
    public function __construct($id, $login, $email, $firstname, $lastname) 
    {
        // connection à la BDD
        $this->bdd = new mysqli('localhost', 'root', '', 'classes');
    }

    /* Méthodes */

    public function register($login, $password, $email, $firstname, $lastname)
    {
        // Enregistrement
        $connect = $this->bdd;
        $login = mysqli_real_escape_string($connect,htmlspecialchars($this->login));
        $password = mysqli_real_escape_string($connect,htmlspecialchars($this->password));
        $email = mysqli_real_escape_string($connect,htmlspecialchars($this->email));
        $firstname = mysqli_real_escape_string($connect,htmlspecialchars($this->firstname));
        $lastname = mysqli_real_escape_string($connect,htmlspecialchars($this->lastname));

        if($login !== "" && $password !== "" && $email !=="" && $firstname !=="" && $lastname!=="" ){
            $requete = "SELECT count(*) FROM utilisateurs where login = $login";
            $exec_requete = $connect -> query($requete);
            $reponse      = mysqli_fetch_array($exec_requete);
            $count = $reponse['count(*)'];

            if($count==0){
                $password = password_hash($password, PASSWORD_DEFAULT);
                $requete = "INSERT INTO utilisateurs (login, password, email, firstname, lastname) VALUES ($login, $password, $email, $firstname, $lastname)";
                $exec_requete = $connect -> query($requete);

            }
            else{
                $error = "Utilisateur déjà existant";
                return $error; // utilisateur déjà existant
            }
        }
        else{
            $error = "Tous les champs ne sont pas renseignés, il faut ; // utilisateur ou mot de passe vide
        }

    mysqli_close($connect); // fermer la connexion
    }

}