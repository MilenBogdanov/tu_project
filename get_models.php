<?php
include 'includes/db_connect.php';

$brand_id = (int)$_GET['brand_id'];
$result = $conn->query("SELECT model_id, model_name FROM models WHERE brand_id = $brand_id ORDER BY model_name");
$models = [];

while ($m = $result->fetch_assoc()) {
    $models[] = $m;
}
echo json_encode($models);
?>
