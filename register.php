<?php 
header("Content-Type:application/json");
include 'connection.php';
$response = [];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password'])) {
		$name = $_POST['name']; 
		$email = $_POST['email']; 
		$password = $_POST['password']; 
		if ($name == "" || $email == "" || $password == "") {
			http_response_code(422);
			$response = [
				'response' => false,
				'message' => "Incomplete Request"
			];
		} else {
			$cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' ");
			if (mysqli_num_rows($cek) > 0) {
				http_response_code(422);
				$response = [
					'response' => false,
					'message' => "The email has already been taken"
				];
			} else {
				// generate apikey 
				$apikey = md5($email . $password . time()); 
				$password = md5($password);
				mysqli_query($conn, "INSERT INTO users (name, email, password, apikey) VALUES ('$name','$email','$password','$apikey')");
				$response = [
					'response' => true,
					'message' => 'Registration Successfully'
				];
			}
		}
	} else {
		http_response_code(422);
		$response = [
			'response' => false,
			'message' => "Incomplete Request"
		];
	}
} else {
	http_response_code(405);
	$response = [
		'response' => false,
		'message' => 'Method Not Allowed'
	];
}

echo json_encode($response);

?>