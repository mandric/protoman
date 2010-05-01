<?php


include('../../base/Framework.php');


Framework::$tests['blog'] = array(
    '$x = 1;' => '1',
    '$y = 1;' => '1',
    '$x + $y;' => '2',
    );

$output = '';

foreach (Framework::$tests as $app => $tests)
{
    $output .= "Testing app: {$app}\n";
    
    $test_count = count($tests);
    $failure_count = 0;
    
    foreach ($tests as $test => $expected)
    {
        eval('$result = ' . $test);
        
        if ($result != $expected)
        {
            $output .= "FAILURE: {$test}\n Expected: {$expected}\n Result: {$result}\n";
            $failure_count++;
        }
    }
    
    $output .= "Tests for app: {$app}\n {$test_count} tests\n {$failure_count} failures\n";
}

echo $output;
