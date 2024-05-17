
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #007bff;
            color: #fff;
            font-weight: bold;
            border-radius: 10px 10px 0 0;
        }

        .form-control {
            border-radius: 5px;
        }

        .btn {
            border-radius: 5px;
        }

        pre {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }

        .spinner-border {
            display: none;
        }
        
        @media (max-width: 576px) {
            .card-header {
                font-size: 1.2rem;
            }
            .form-control {
                font-size: 0.9rem;
            }
            .btn {
                font-size: 0.9rem;
            }
            .table {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mt-4 mb-4 text-center">Admin Panel</h1>
        <button type="button" class="btn btn-danger btn-sm float-right" id="logoutBtn">Logout</button>
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-shield-alt mr-2"></i>IP Risk Assessment
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="ipInput">Enter IP Address:</label>
                            <input type="text" class="form-control" id="ipInput" placeholder="Enter IP address">
                        </div>
                        <button type="button" class="btn btn-primary btn-block mb-2" id="assessRiskBtn">
                            <i class="fas fa-search mr-2"></i>Assess Risk
                        </button>
                        <button type="button" class="btn btn-secondary btn-block mb-2" id="assessOwnIpBtn">
                            <i class="fas fa-user mr-2"></i>Assess Own IP
                        </button>
                        <div class="mt-3 spinner-border text-primary" role="status" id="riskSpinner">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
                <div id="riskResult" class="mt-4"></div>
            </div>
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-history mr-2"></i>User Behavior Log
                        <button id="toggleLogBtn" class="btn btn-sm btn-secondary float-right">Minimize Log</button>
                    </div>
                    <div class="card-body" id="userBehaviorLogContainer">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>IP Address</th>
                                        <th>Behavior ID</th>
                                    </tr>
                                </thead>
                                <tbody id="userBehaviorLog"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-search mr-2"></i>Check Result by Request ID
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="requestIdInput">Enter Request ID:</label>
                            <input type="text" class="form-control" id="requestIdInput" placeholder="Enter Request ID">
                        </div>
                        <button type="button" class="btn btn-primary btn-block" id="checkResultBtn">
                            <i class="fas fa-check mr-2"></i>Check Result
                        </button>
                        <div class="mt-3 spinner-border text-primary" role="status" id="resultSpinner">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
                <div id="savedResult" class="mt-4"></div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $('#assessRiskBtn').click(function () {
                var ip = $('#ipInput').val();
                var apiKey = 'bl'; // Replace with your API key

                $('#riskSpinner').show();
                assessRisk(ip, apiKey);
            });

            $('#assessOwnIpBtn').click(function () {
                var apiKey = 'bl'; // Replace with your API key

                $('#riskSpinner').show();
                // Get visitor's own IP address
                $.getJSON('https://api.ipify.org?format=json', function (data) {
                    var visitorIp = data.ip;
                    assessRisk(visitorIp, apiKey);
                });
            });

            function assessRisk(ip, apiKey) {
                $.ajax({
                    url: 'api.php',
                    method: 'GET',
                    data: {
                        apiKey: apiKey,
                        ip: ip
                    },
                    success: function (response) {
                        var riskResult = '<div class="card mt-4">';
                        riskResult += '<div class="card-header">Risk Assessment for IP: ' + ip +
                            '</div>';
                        riskResult += '<div class="card-body"><pre>' + JSON.stringify(response,
                            null, 2) + '</pre></div>';
                        riskResult += '</div>';
                        $('#riskResult').html(riskResult);
                        $('#riskSpinner').hide();
                    },
                    error: function (xhr, status, error) {
                        var errorMessage = '<p class="text-danger mt-3">Error: ' + error + '</p>';
                        $('#riskResult').html(errorMessage);
                        $('#riskSpinner').hide();
                    }
                });
            }

            $('#checkResultBtn').click(function () {
                var requestId = $('#requestIdInput').val();
                var apiKey = 'bl'; // Replace with your API key

                $('#resultSpinner').show();
                $.ajax({
                    url: 'id.php',
                    method: 'GET',
                    data: {
                        requestId: requestId
                    },
                    success: function (response) {
                        var savedResult = '<div class="card mt-4">';
                        savedResult +=
                            '<div class="card-header">Saved Result for Request ID: ' +
                            requestId + '</div>';
                        savedResult += '<div class="card-body"><pre>' + JSON.stringify(
                            response, null, 2) + '</pre></div>';
                        savedResult += '</div>';
                        $('#savedResult').html(savedResult);
                        $('#resultSpinner').hide();
                    },
                    error: function (xhr, status, error) {
                        var errorMessage = '<p class="text-danger mt-3">Error: ' + error +
                            '</p>';
                        $('#savedResult').html(errorMessage);
                        $('#resultSpinner').hide();
                    }
                });
            });
            // Fetch user behavior log
            function fetchUserBehaviorLog() {
                $.ajax({
                    url: 'user_behavior.log',
                    method: 'GET',
                    success: function (response) {
                        var logEntries = response.trim().split('\n');
                        var tableRows = '';
                        logEntries.forEach(function (entry) {
                            var logParts = entry.split('\t');
                            if (logParts.length === 3) {
                                var timestamp = logParts[0];
                                var ip = logParts[1];
                                var behaviorId = logParts[2];
                                tableRows += '<tr><td>' + timestamp + '</td><td>' + ip +
                                    '</td><td>' + behaviorId + '</td></tr>';
                            }
                        });
                        $('#userBehaviorLog').html(tableRows);
                    },
                    error: function (xhr, status, error) {
                        console.log('Error fetching user behavior log:', error);
                    }
                });
            }

            // Fetch user behavior log initially
            fetchUserBehaviorLog();

            // Refresh user behavior log every 5 seconds
            setInterval(fetchUserBehaviorLog, 5000);

             // Toggle user behavior log visibility
             $('#toggleLogBtn').click(function () {
                $('#userBehaviorLogContainer').toggle();
                if ($(this).text() === 'Minimize Log') {
                    $(this).text('Maximize Log');
                } else {
                    $(this).text('Minimize Log');
                }
            });
        });

        $('#logoutBtn').click(function () {
            $.ajax({
                url: 'logout.php',
                method: 'POST',
                success: function () {
                    window.location.href = 'loginc.php';
                }
            });
        });
    </script>
</body>

</html>