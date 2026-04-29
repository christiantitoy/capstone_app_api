<?php
// /admin/ui/report_details.php
require_once '../backend/session/auth_admin.php';

$reportId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report #<?php echo $reportId; ?> Details | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/buyer_reports.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Include sidebar or just a back button -->
        <main class="main-content">
            <header class="main-header">
                <div class="header-left">
                    <a href="/admin/ui/buyer_reports.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Reports
                    </a>
                    <h1>Report #<?php echo $reportId; ?> Details</h1>
                </div>
            </header>
            
            <div class="full-width-section">
                <div class="report-details-container" id="reportDetails">
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i> Loading report details...
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Fetch report details when page loads
        document.addEventListener('DOMContentLoaded', function() {
            fetchReportDetails(<?php echo $reportId; ?>);
        });
        
        function fetchReportDetails(reportId) {
            fetch(`/admin/backend/reports/get_buyer_reports.php?report_id=${reportId}`)
                .then(response => response.json())
                .then(data => {
                    // Handle the response and render details
                    console.log(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    </script>
</body>
</html>