<?php

function fetchData($url) {
    // cURL initialization and configuration
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);

    // Execute the cURL request
    $response = curl_exec($curl);
    $error = curl_error($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    // Close the cURL session
    curl_close($curl);

    // Check for errors and return the response
    if ($response === false || $httpCode != 200) {
        return ["error" => "API request failed with code: $httpCode, Error: $error"];
    }
    return json_decode($response, true);
}

/**
 * Evaluates risk based on consolidated risk data.
 *
 * @param array $riskData Consolidated risk data from all sources.
 * @param string $ip The IP address being evaluated.
 * @return array Risk assessment result.
 */
function evaluateRisk($riskData, $ip) {
    // Generate a unique request ID
    $requestId = uniqid('req_', true);

    // Initialize the risk response with default values
    $riskResponse = [
        'action' => 'allow',
        'code' => 100,
        'requestId' => $requestId,
        'ip' => $ip,
        'messages' => []
    ];

    // Check if the required keys exist in the $riskData array
    if (isset($riskData['score']) && isset($riskData['risk'])) {
        // Evaluate risk based on score and risk level
        if ($riskData['score'] > 75 || $riskData['risk'] === 'very high') {
            $riskResponse['action'] = 'block';
            $riskResponse['code'] = 300;
            $riskResponse['messages'][] = 'High risk detected: Immediate action required.';
        } elseif ($riskData['score'] > 50 || $riskData['risk'] === 'medium') {
            $riskResponse['action'] = 'review';
            $riskResponse['code'] = 200;
            $riskResponse['messages'][] = 'Medium risk detected; review recommended.';
        }
    }

    // Check if the 'isCrawler' key exists in the $riskData array
    if (isset($riskData['isCrawler']) && $riskData['isCrawler']) {
        $riskResponse['action'] = 'block';
        $riskResponse['code'] = 300;
        $riskResponse['messages'][] = 'Crawler activity detected; potentially harmful.';
    }

    // Check if the 'isProxy' key exists in the $riskData array
    if (isset($riskData['isProxy']) && $riskData['isProxy']) {
        $riskResponse['action'] = 'block';
        $riskResponse['code'] = 300;
        $riskResponse['messages'][] = 'Proxy usage detected; security risk.';
    }

    // Add a default message for low risk
    if ($riskResponse['code'] === 100) {
        $riskResponse['messages'][] = 'Low risk; normal operations.';
    }

    // Store the risk assessment result
    storeRiskResult($requestId, $riskResponse);

    // Return the risk assessment result
    return $riskResponse;
}

/**
 * Tracks user behavior by logging the IP address and behavior ID.
 *
 * @param string $ip The IP address of the user.
 * @param string $behaviorId The ID of the user behavior.
 * @return void
 */
function trackUserBehavior($ip, $behaviorId) {
    $file = 'user_behavior.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "$timestamp\t$ip\t$behaviorId\n";
    file_put_contents($file, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Fetches geographical data for a given IP address using the IPStack API.
 *
 * @param string $ip The IP address to fetch geographical data for.
 * @return array The fetched geographical data as an associative array.
 */
function fetchGeographicalData($ip) {
    $apiKey = "df727623d7af3d3b30e47f4b6beecc8e";
    $url = "https://api.ipstack.com/$ip/$apiKey";
    return fetchData($url);
}

/**
 * Consolidates risk data from Scamalytics and DB-IP.
 *
 * @param array $scamalyticsData Risk data from Scamalytics.
 * @param array $dbIpData Risk data from DB-IP.
 * @return array The consolidated risk data as an associative array.
 */
function consolidateRiskData($scamalyticsData, $dbIpData) {
    // Implement your merging logic here, ensuring proper handling of data points
    $mergedData = array_merge($scamalyticsData, $dbIpData);
    return $mergedData;
}

/**
 * Fetches risk data from Scamalytics for a given IP address.
 *
 * @param string $ip The IP address to fetch risk data for.
 * @param string $apiKey The API key for accessing Scamalytics.
 * @return array The fetched risk data as an associative array.
 */
function fetchFromScamalytics($ip, $apiKey) {
    $url = "https://api11.scamalytics.com/1rajesh08kumar98/?key=$apiKey&ip=$ip";
    return fetchData($url);
}

/**
 * Fetches risk data from DB-IP for a given IP address.
 *
 * @param string $ip The IP address to fetch risk data for.
 * @param string $apiKey The API key for accessing DB-IP.
 * @return array The fetched risk data as an associative array.
 */
function fetchFromDbIp($ip, $apiKey) {
    $url = "https://api.db-ip.com/v2/$apiKey/$ip";
    return fetchData($url);
}

/**
 * Retrieves the client's IP address.
 *
 * @return string The client's IP address.
 */
function getClientIP() {
    $ip = $_SERVER['REMOTE_ADDR'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim(end($ipList));
    }
    return $ip;
}

/**
 * Stores the risk assessment result using the request ID as the key.
 *
 * @param string $requestId The unique request ID.
 * @param array $riskResponse The risk assessment result.
 * @return void
 */
function storeRiskResult($requestId, $riskResponse) {
    $resultsFile = 'risk_assessment_results.json';
    $resultsData = [];

    if (file_exists($resultsFile)) {
        $resultsData = json_decode(file_get_contents($resultsFile), true);
    }

    $resultsData[$requestId] = $riskResponse;

    file_put_contents($resultsFile, json_encode($resultsData, JSON_PRETTY_PRINT));
}

/**
 * Retrieves the risk assessment result by request ID.
 *
 * @param string $requestId The unique request ID.
 * @return array The risk assessment result or an error message if not found.
 */
function getResultByRequestId($requestId) {
    $resultsFile = 'risk_assessment_results.json';

    if (file_exists($resultsFile)) {
        $resultsData = json_decode(file_get_contents($resultsFile), true);

        if (isset($resultsData[$requestId])) {
            return $resultsData[$requestId];
        }
    }

    return ["error" => "No result found for the provided Request ID"];
}
