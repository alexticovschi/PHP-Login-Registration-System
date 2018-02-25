<?php 

/* ======================= */
/*     HELPER FUNCTIONS    */ 
/* ======================= */

function clean($string) {
	return htmlentities($string);
}


function redirect($location) {
	return header("Location: {$location}");
}


function set_message($message) {
	if(!empty($message)) {
		$_SESSION['message'] = $message;
	} else {
		$message = '';
	}
}


function display_message() {
	if(isset($_SESSION['message'])) {
		echo $_SESSION['message'];

		unset($_SESSION['message']);
	}
}


function token_generator() {
	$token = $_SESSION['token'] = md5(uniqid(mt_rand(), true));

	return $token;
}


function validation_errors($error_message) {
	echo '
		<div class="alert alert-danger alert-dismissible" role="alert">
			<strong>Warning!</strong> ' . $error_message . '
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	';
}


function email_exists($email) {
	$sql = "SELECT id FROM users WHERE email = '$email'";

	$result = query($sql);

	if(row_count($result) == 1) {
		return true;
	} else {
		return false;
	}
}


function username_exists($username) {
	$sql = "SELECT id FROM users WHERE username = '$username'";

	$result = query($sql);

	if(row_count($result) == 1) {
		return true;
	} else {
		return false;
	}
}




function send_email($email, $subject, $msg, $header) {

	return mail($email, $subject, $msg, $header);

}



/* ======================== */
/*   VALIDATION FUNCTIONS   */ 
/* ======================== */

function validate_user_registration() {
	$errors = [];

	$min = 3;
	$max = 20;

	if($_SERVER['REQUEST_METHOD'] == "POST") {
		$first_name 		= clean($_POST['first_name']);
		$last_name  		= clean($_POST['last_name']);
		$username   		= clean($_POST['username']);
		$email 				= clean($_POST['email']);
		$password 			= clean($_POST['password']);
		$confirm_password 	= clean($_POST['confirm_password']);

		if(strlen($first_name) < $min) {
			$errors[] = "Your first name cannot be less than {$min} characters";
		}

		if(strlen($first_name) > $max) {
			$errors[] = "Your first name cannot be greater than {$max} characters";
		}

		if(strlen($last_name) < $min) {
			$errors[] = "Your last name cannot be less than {$min} characters";
		}

		if(strlen($last_name) > $max) {
			$errors[] = "Your last name cannot be greater than {$max} characters";
		}

		if(strlen($username) < $min) {
			$errors[] = "Your username cannot be less than {$min} characters";
		}

		if(strlen($username) > $max) {
			$errors[] = "Your username cannot be greater than {$max} characters";
		}

		if(username_exists($username)) {
			$errors[] = "Sorry this username is already taken";
		}

		if(email_exists($email)) {
			$errors[] = "Sorry this email is already registered";
		}

		// if(strlen($email) > $max) {
		// 	$errors[] = "Your email cannot be greater than {$max} characters";
		// }

		if($password !== $confirm_password) {
			$errors[] = "Your password fields do not match";
		}


		if(!empty($errors)) {
			foreach ($errors as $error) { 
				validation_errors($error);
			}
		} else {
			if(register_user($first_name, $last_name, $username, $email, $password)) {
				set_message("<p class='bg-success text-center'>Please check your email for activation link</p>");
				
				redirect("index.php");
			} else {
				set_message("<p class='bg-danger text-center'>Sorry, this user cannot be registered!</p>");
				
				redirect("index.php");
			}
		}
	}
}



function register_user($first_name, $last_name, $username, $email, $password) {

	$first_name = escape($first_name);
	$last_name  = escape($last_name);
	$username   = escape($username);
	$email 		= escape($email);
	$password   = escape($password);


	if(email_exists($email)) {
		return false;
	} else if(username_exists($username)) {
		return false;
	} else {
		$password = md5($password);
		$validation_code = md5($username + microtime());

		$sql = "INSERT INTO users (";
		$sql .= "first_name, last_name, username, email, password, validation_code, active";
		$sql .= ")";
		$sql .= " VALUES ('$first_name', '$last_name', '$username', '$email', '$password',";
		$sql .= " '$validation_code', 0)";

		$result = query($sql);

		confirm($result);


		$subject = "Activate Account";
		$msg     = "Please click the kink bellow to activate your Account
					http://localhost/login/activate.php?email=$email&code=$validation_code
					";
		$header = "From: noreply@yourwebsite.com";

		send_email($email, $subject, $msg, $headers);

		return true;
	}

} 


/** ACTIVATE USER **/

function activate_user_account() {

	if($_SERVER['REQUEST_METHOD'] == "GET") {

		if(isset($_GET['email'])) {

			echo $email = clean($_GET['email']);

			echo $validation_code = clean($_GET['code']);

			$sql = "SELECT id FROM users WHERE email = '" .escape($_GET['email']). "' AND validation_code = '" .escape($_GET['code']) ."'";

			$result = query($sql);
			confirm($result);

			

			if(row_count($result) == 1) {

				$sql2 = "UPDATE users SET active = 1, validation_code = 0 WHERE email = '".escape($email)."' AND validation_code = '" .escape($validation_code). "'";
				$result2 = query($sql2);
				confirm($result2);

				set_message("<p class='bg-success text-center'>Account activated! Please Login</p>");

				redirect("login.php");

			} else {
				set_message("<p class='bg-danger text-center'>Account cannot be activated!</p>");

				redirect("login.php");
			}

		} 
	}

	// http://localhost/login/activate.php?email=frankie@gmail.com&code=e8b31e688cc507204f2de7b7f36fbc7a 
}





?>

