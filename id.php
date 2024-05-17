<?php
require_once 'utils.php'; // Include the file containing the getResultByRequestId() function

// Get the request ID from the query parameters
$requestId = $_GET['requestId'];

// Retrieve the risk assessment result by request ID
$result = getResultByRequestId($requestId);

// Return the result as a JSON response
header('Content-Type: application/json');
echo json_encode($result);
?>