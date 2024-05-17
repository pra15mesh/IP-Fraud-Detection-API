
<?php

// api.php

header('Content-Type: application/json');
require_once 'Logger.php';
require_once 'AuthMiddleware.php';
require_once 'utils.php';
$config = include 'config.php';
$auth = new AuthMiddleware();
$logger = new Logger();

// Authenticate Request
if (!isset($_GET['apiKey']) || empty($_GET['apiKey'])) {
    $requestId = uniqid('req_', true); // Generate a unique request ID 
    $logger::log("Access attempt without API key", ['IP' => $_SERVER['REMOTE_ADDR'], 'requestId' => $requestId]);
    echo json_encode(["error" => "API key is required"]);
    exit;
}

$auth->authenticate($_GET['apiKey']);

$visitorIp = getClientIP();
$providedIp = $_GET['ip'] ?? $visitorIp;
$requestId = $_GET['requestId'] ?? null;
$behaviorId = uniqid('behavior_', true); // Generate a unique behavior ID


if ($requestId) {
    // Retrieve the saved result from the logs based on the requestId
    $savedResult = retrieveSavedResult($requestId);
    if ($savedResult) {
        echo json_encode($savedResult);
        exit;
    } else {
        echo json_encode(["error" => "No result found for the provided Request ID"]);
        exit;
    }
}
if (!filter_var($providedIp, FILTER_VALIDATE_IP)) {
    $logger::log("Invalid IP address detected", ['IP' => $providedIp]);
    echo json_encode(["error" => "Invalid IP address detected"]);
    exit;
}

// Track user behavior for visitor's IP
trackUserBehavior($visitorIp, $behaviorId);

try {
    $scamalyticsData = fetchFromScamalytics($providedIp, $config['scamalytics_key']);
    $dbIpData = fetchFromDbIp($providedIp, $config['db_ip_key']);
    $consolidatedRiskData = consolidateRiskData($scamalyticsData, $dbIpData);
    $riskResponse = evaluateRisk($consolidatedRiskData, $providedIp); // Pass the IP address as the second argument

    // Adjusted response handling
    echo json_encode([
        "IP" => $providedIp,
        "Risk Data" => $consolidatedRiskData,
        "Risk Assessment" => $riskResponse
    ]);

    // Log the successful processing of the request with risk assessment details
    $logger::log("Successful API response for IP: $providedIp", [
        'IP' => $providedIp,
        'Risk Assessment' => json_encode($riskResponse),
        'requestId' => $riskResponse['requestId']
    ]);

       // Save the risk assessment as a log entry
       $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip' => $providedIp,
        'riskData' => $consolidatedRiskData,
        'riskAssessment' => $riskResponse
    ];
    saveRiskAssessmentLog($logEntry);

} catch (Exception $e) {
    $logger::log("API Processing Error: " . $e->getMessage(), [
        'IP' => $providedIp,
        'requestId' => uniqid('req_', true)
    ]);
    echo json_encode(["error" => "An error occurred while processing your request", "details" => $e->getMessage()]);
}
function retrieveSavedResult($requestId) {
    $logFile = 'bl.log';
    $logContent = file_get_contents($logFile);
    $logEntries = explode(",\n", trim($logContent));

    foreach ($logEntries as $entry) {
        $logData = json_decode($entry, true);
        if ($logData && isset($logData['requestId']) && $logData['requestId'] === $requestId) {
            return $logData['riskAssessment'];
        }
    }

    return null;
}


function saveRiskAssessmentLog($logEntry) {
    $logFile = 'risk_assessment_logs.json';
    $logData = [];

    if (file_exists($logFile)) {
        $logData = json_decode(file_get_contents($logFile), true);
    }

    $logData[] = $logEntry;

    file_put_contents($logFile, json_encode($logData, JSON_PRETTY_PRINT));
}

function extractSavedResult($logEntry) {
    $pattern = '/\"riskAssessment\":\s*\"(.+?)\"/';
        preg_match($pattern, $logEntry, $matches);
    
    if (isset($matches[1])) {
        $savedResult = json_decode($matches[1], true);
        return $savedResult;
    }
    
    return null;
}