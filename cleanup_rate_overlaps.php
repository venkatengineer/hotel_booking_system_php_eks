<?php
include 'config.php';

echo "<h2>Starting Rate Overlap Cleanup...</h2>";

$assets = $conn->query("SELECT asset_id, asset_name FROM tbl_assets");

while ($asset = $assets->fetch_assoc()) {
    $asset_id = $asset['asset_id'];
    echo "<h3>Processing: " . htmlspecialchars($asset['asset_name']) . "</h3>";
    
    // Get all rates for this asset ordered by start date
    $rates = $conn->query("SELECT * FROM tbl_rates WHERE asset_id = $asset_id ORDER BY effective_from ASC, rate_id ASC");
    $rate_list = [];
    while ($r = $rates->fetch_assoc()) {
        $rate_list[] = $r;
    }
    
    for ($i = 0; $i < count($rate_list); $i++) {
        $current = &$rate_list[$i];
        
        // If there's a next record, ensure current record ends the day before next one starts
        if (isset($rate_list[$i+1])) {
            $next = $rate_list[$i+1];
            
            $new_end_date = date('Y-m-d', strtotime($next['effective_from'] . ' -1 day'));
            
            if ($current['effective_to'] != $new_end_date) {
                $rid = $current['rate_id'];
                $conn->query("UPDATE tbl_rates SET effective_to = '$new_end_date' WHERE rate_id = $rid");
                echo "Updated Rate ID $rid: New end date is $new_end_date (before Rate ID {$next['rate_id']} starts on {$next['effective_from']})<br>";
            }
        } else {
            // Last record should ideally go to the system max date if it's currently "active"
            // But we'll leave it as is unless it's strictly necessary.
        }
    }
}

echo "<h3>Cleanup Complete! ✨</h3>";
echo "<a href='view_rates.php'>Return to Rates View</a>";
?>
