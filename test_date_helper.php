<?php
require_once 'controllers/DateHelper.php';

function testDate($dateStr, $expectedWeek, $expectedYear) {
    $info = DateHelper::getWeekYear($dateStr);
    echo "Date: $dateStr -> Week: {$info['week']}, Year: {$info['year']} ";
    if ($info['week'] == $expectedWeek && $info['year'] == $expectedYear) {
        echo "[PASS]\n";
    } else {
        echo "[FAIL] Expected Week: $expectedWeek, Year: $expectedYear\n";
    }
}

echo "Testing DateHelper...\n";
// Dec 28 2025 is Sunday. Should be Week 1 2026 (based on our logic: Sun+1 = Mon Dec 29 -> ISO Week 1 2026)
testDate('2025-12-28', 1, 2026);
// Dec 29 2025 is Monday. Should be Week 1 2026
testDate('2025-12-29', 1, 2026);
// Dec 31 2025 is Wednesday. Should be Week 1 2026
testDate('2025-12-31', 1, 2026);
// Jan 1 2026 is Thursday. Should be Week 1 2026
testDate('2026-01-01', 1, 2026);
// Jan 3 2026 is Saturday. Should be Week 1 2026
testDate('2026-01-03', 1, 2026);
// Jan 4 2026 is Sunday. Should be Week 2 2026 (Sun+1 = Mon Jan 5 -> ISO Week 2 2026)
testDate('2026-01-04', 2, 2026);

// Normal week
// Dec 21 2025 is Sunday. Should be Week 52 2025 (Sun+1 = Mon Dec 22 -> ISO Week 52 2025)
testDate('2025-12-21', 52, 2025);

// Check reverse logic
$range = DateHelper::getWeekRange(1, 2026);
echo "Week 1 2026 Range: " . $range['start'] . " to " . $range['end'] . "\n";
// Expected: 2025-12-28 to 2026-01-03
if ($range['start'] === '2025-12-28' && $range['end'] === '2026-01-03') {
    echo "Range Logic [PASS]\n";
} else {
    echo "Range Logic [FAIL] Expected 2025-12-28 to 2026-01-03\n";
}
