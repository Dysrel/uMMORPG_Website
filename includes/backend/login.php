<?php
$thisFile = basename($_SERVER["SCRIPT_FILENAME"], '.php');
if($thisFile == "login.php") {
    header('Location: ../index.php');
    exit;
}
if($session) {
	header('Location: ../index.php');
	exit;
}

$errors = array();

if(isset($_POST['login'])) {
    if(!empty($_POST['name']) && !empty($_POST['password'])) {        
        $name = $_POST['name'];
        $password = pbkdf2($name, $_POST['password']);

        if($config['alphanumeric_names'] == true) {
            if(!ctype_alnum($name)) {
                $errors[] = "Invalid username.";
            }
        } else {
            $filterName = stripslashes(str_replace('/', "", strip_tags($name)));
            if($name != $filterName) {
                $errors[] = "Invalid username.";
            }
        }

        if(empty($errors)) {
            $fetchUser = $db->prepare('SELECT * FROM accounts WHERE name = :name');
            $fetchUser->bindParam(':name', $name);
            $fetchUser->execute();
            $fetchUser = $fetchUser->fetch(PDO::FETCH_OBJ);
            
            if($fetchUser) {
                if($fetchUser->password == $password) {
					session_regenerate_id();
					$_SESSION[$config['project_name']] = $name;
					header('Location: ../index.php');
					exit;
                } else {
                    $errors[] = "Wrong username or password."; // incorrect password
                }
            } else {
                $errors[] = "Wrong username or password."; // user not found
            }
        }
    } else {
        $errors[] = "Missing fields.";
    }
}
?>