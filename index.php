<?php 
header("Content-Type:application/json");
include 'connection.php';
$response = [];

if (isset($_SERVER['HTTP_APIKEY']) && $_SERVER['HTTP_APIKEY'] != "") {
	$apikey = $_SERVER['HTTP_APIKEY'];
	$user = mysqli_query($conn, "SELECT * FROM users WHERE apikey='$apikey' ");
	if (mysqli_num_rows($user) > 0) {
		if ($_SERVER['REQUEST_METHOD'] == "GET") {
			if (isset($_GET['id'])) {
				$product = mysqli_query($conn, "SELECT products.id,products.name,category_id,price,description,categories.name as category_name FROM products,categories WHERE products.category_id=categories.id and products.id='$_GET[id]' ");
				if (mysqli_num_rows($product) > 0) {
					$response['response'] = true;
					$fetch = mysqli_fetch_assoc($product);
					$val['id'] = $fetch['id'];
					$val['name'] = $fetch['name'];
					$val['price'] = $fetch['price'];
					$val['category'] = [
						'id' => $fetch['category_id'],
						'name' => $fetch['category_name'],
					];
					$val['description'] = $fetch['description'];
					$response['data'] = $val;
				} else {
					http_response_code(404);
					$response = [
						'response' => false,
						'message' => "Product Not Found"
					];
				}
			} else{
				$response['response'] = true;
				$response['data'] = [];
				$products = mysqli_query($conn, "SELECT products.id,products.name,category_id,price,description,categories.name as category_name FROM products,categories WHERE products.category_id=categories.id ORDER BY products.id DESC");
				while ($fetch = mysqli_fetch_assoc($products)) {
					$val['id'] = $fetch['id'];
					$val['name'] = $fetch['name'];
					$val['price'] = $fetch['price'];
					$val['category'] = [
						'id' => $fetch['category_id'],
						'name' => $fetch['category_name'],
					];
					$val['description'] = $fetch['description'];
					array_push($response['data'], $val);
				}
			}
		} elseif ($_SERVER['REQUEST_METHOD'] == "POST") {
			if (isset($_POST['name']) && isset($_POST['price']) && isset($_POST['category_id']) && isset($_POST['description'])) {
				$name = $_POST['name'];
				$price = $_POST['price'];
				$category_id = $_POST['category_id'];
				$description = $_POST['description'];
				if ($name == "" || $price == "" || $category_id == "" || $description == "") {
					http_response_code(422);
					$response = [
						'response' => false,
						'message' => "Incomplete Request"
					];
				} else {
					$category = mysqli_query($conn, "SELECT * FROM categories WHERE id='$category_id' ");
					if (mysqli_num_rows($category) > 0) {
						mysqli_query($conn, "INSERT INTO products (category_id, name, price, description) VALUES('$category_id', '$name', '$price', '$description') ");
						$response = [
							'response' => true,
							'message' => "Data Saved Successfully"
						];
					} else {
						http_response_code(404);
						$response = [
							'response' => false,
							'message' => "Category Not Found"
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
		} elseif ($_SERVER['REQUEST_METHOD'] == "PUT") {
			parse_str(file_get_contents("php://input"), $_PUT);
			if (isset($_GET['id']) && isset($_PUT['name']) && isset($_PUT['price']) && isset($_PUT['category_id']) && isset($_PUT['description'])) {
				$id = $_GET['id'];
				$name = $_PUT['name'];
				$price = $_PUT['price'];
				$category_id = $_PUT['category_id'];
				$description = $_PUT['description'];
				if ($id == "" || $name == "" || $price == "" || $category_id == "" || $description == "") {
					http_response_code(422);
					$response = [
						'response' => false,
						'message' => "Incomplete Request"
					];
				} else {
					$product = mysqli_query($conn, "SELECT * FROM products WHERE id='$id' ");
					if (mysqli_num_rows($product) > 0) {
						$category = mysqli_query($conn, "SELECT * FROM categories WHERE id='$category_id' ");
						if (mysqli_num_rows($category) > 0) {
							mysqli_query($conn, "UPDATE products SET category_id='$category_id', name='$name', price='$price', description='$description' WHERE id='$id' ");
							$response = [
								'response' => true,
								'message' => "Data Updated Successfully"
							];					
						} else {
							http_response_code(404);
							$response = [
								'response' => false,
								'message' => "Category Not Found"
							];
						}	
					} else {
						http_response_code(404);
						$response = [
							'response' => false,
							'message' => "Product Not Found"
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
		} elseif ($_SERVER['REQUEST_METHOD'] == "DELETE") {
			if (isset($_GET['id'])) {
				$product = mysqli_query($conn, "SELECT * FROM products WHERE id='$_GET[id]' ");
				if (mysqli_num_rows($product) > 0) {
					mysqli_query($conn, "DELETE FROM products WHERE id='$_GET[id]' ");

					$response = [
						'response' => true,
						'message' => "Data Deleted Successfully"
					];
				} else {
					http_response_code(404);
					$response = [
						'response' => false,
						'message' => "Product Not Found"
					];
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
	} else {
		http_response_code(401);
		$response = [
			'response' => false,
			'message' => 'User Unauthorized'
		];
	}
} else {
	http_response_code(401);
	$response = [
		'response' => false,
		'message' => 'User Unauthorized'
	];
}

echo json_encode($response);

?>