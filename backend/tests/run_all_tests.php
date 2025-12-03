<?php

echo "\n";
echo "DreamJournal - Test Suite\n\n";

require_once __DIR__ . '/../repository/config/db.php';

require_once __DIR__ . '/DatabaseTest.php';
require_once __DIR__ . '/AuthTest.php';
require_once __DIR__ . '/DreamTest.php';

$allPassed = true;

try {
    $dbTest = new DatabaseTest($pdo);
    $passed = $dbTest->runAll();
    $allPassed = $allPassed && $passed;
} catch (Exception $e) {
    echo "Error running Database tests: " . $e->getMessage() . "\n";
    $allPassed = false;
}

try {
    $authTest = new AuthTest($pdo);
    $passed = $authTest->runAll();
    $allPassed = $allPassed && $passed;
} catch (Exception $e) {
    echo "Error running Authentication tests: " . $e->getMessage() . "\n";
    $allPassed = false;
}

try {
    $dreamTest = new DreamTest($pdo);
    $passed = $dreamTest->runAll();
    $allPassed = $allPassed && $passed;
} catch (Exception $e) {
    echo "Error running Dream tests: " . $e->getMessage() . "\n";
    $allPassed = false;
}

echo "\n";
if ($allPassed) {
    echo "Result: ALL TESTS PASSED\n";
} else {
    echo "Result: SOME TESTS FAILED\n";
}
echo "\n";

exit($allPassed ? 0 : 1);

