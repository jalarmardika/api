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
				$category = mysqli_query($conn, "SELECT * FROM categories WHERE id='$_GET[id]' ");
				if (mysqli_num_rows($category) > 0) {
					$fetch = mysqli_fetch_assoc($category);
					$val = [
						'id' => $fetch['id'],
						'name' => $fetch['name']
					];

					$val['products'] = [];
					$products = mysqli_query($conn, "SELECT * FROM products WHERE category_id='$_GET[id]' ");
					while ($value = mysqli_fetch_assoc($products)) {
						$p['id'] = $value['id'];
						$p['name'] = $value['name'];
						$p['price'] = $value['price'];
						$p['description'] = $value['description'];
						array_push($val['products'], $p);
					}
					
					$response = [
						'response' => true,
						'data' => $val
					];

				} else {
					http_response_code(404);
					$response = [
						'response' => false,
						'message' => "Category Not Found"
					];
				}
			} else{
				$response['response'] = true;
				$response['data'] = [];
				$categories = mysqli_query($conn, "SELECT categories.id,categories.name,products.id as product_id,products.name as product_name,price,description FROM categories LEFT JOIN products ON categories.id=products.category_id ORDER BY id DESC");
				while ($fetch = mysqli_fetch_assoc($categories)) {
					$categoryExists = false;
					
					$product = [
						'id' => $fetch['product_id'],
						'name' => $fetch['product_name'],
						'price' => $fetch['price'],
						'description' => $fetch['description'],
					];

					foreach ($response['data'] as &$category) {
						if ($category['id'] == $fetch['id']) {
							$categoryExists = true;
							array_push($category['products'], $product);
						}
					}

					if (!$categoryExists) {
						if ($fetch['product_id'] != null) {
							$data = [
								'id' => $fetch['id'],
								'name' => $fetch['name'],
								'products' => [
									$product
								]
							];
						} else {
							$data = [
								'id' => $fetch['id'],
								'name' => $fetch['name'],
								'products' => []
							];
						}
						array_push($response['data'], $data);
					}
				}
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