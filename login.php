<?php 
header("Content-Type:application/json");
include 'connection.php';
$response = [];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	if (isset($_POST['email']) && isset($_POST['password'])) {
		$email = $_POST['email']; 
		$password = $_POST['password']; 
		if ($email == "" || $password == "") {
			http_response_code(422);
			$response = [
				'response' => false,
				'message' => "Incomplete Request"
			];
		} else {
			$password = md5($password);
			$user = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' and password='$password' ");
			if (mysqli_num_rows($user) > 0) {
				$fetch = mysqli_fetch_assoc($user);
				$response = [
					'response' => true,
					'message' => 'Login Successfully',
					'data' => [
						'id' => $fetch['id'],
						'name' => $fetch['name'],
						'email' => $fetch['email'],
						'apikey' => $fetch['apikey']
					]
				];
			} else {
				http_response_code(422);
				$response = [
					'response' => false,
					'message' => 'Login Failed'
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