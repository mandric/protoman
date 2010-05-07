<?php


class Cli
{
    private static $callback = array();
    private static $args = array();
    
    public static function process($apps)
    {
        if (!in_array('apps', array_keys(Cli::$args)) || !Cli::$args['apps'])
        {
            Cli::$args[] = $apps;
        }
        
        return call_user_func_array(Cli::$callback, Cli::$args);
    }
    
    public static function preprocess($argv)
    {
        $output = array();
        
        switch ($argv[1])
        {
            // Command: php index.php test [appname]
            case 'test':
                $app = '';
                
                if (count($argv) >= 3)
                {
                    $app = $argv[2];
                }
                
                if (!defined('TEST'))
                {
                    define('TEST', true);
                }
                
                Cli::$callback = array('Cli', 'test');
                Cli::$args = array($app);
                //$output = array_merge($output, Framework::test($apps));
                
                break;
            case 'sql':
                if (count($argv) >= 3)
                {
                    if (in_array($argv[2], $apps))
                    {
                        $apps = array($argv[2]);
                    }
                    else
                    {
                        throw new Exception("Specified invalid app to test: {$argv[2]}");
                    }
                }
                
                Cli::$callback = array('Cli', 'sql');
                Cli::$args = array('apps' => $apps);
                
                break;
            case 'rebuild':
                if (count($argv) < 3)
                {
                    throw new Exception("Command requires a a database ('dev', 'test' or 'prod') and an app name (or 'all')");
                }
                
                $db = $argv[2];
                
                if (!in_array($db, array('dev', 'test', 'prod')))
                {
                    throw new Exception("Database must be one of 'dev', 'test' or 'prod'");
                }
                
                if ($db == 'test')
                {
                    if (!defined('TEST'))
                    {
                        define('TEST', true);
                    }
                }
                
                $app = $argv[3];
                
                Cli::$callback = array('Cli', 'rebuild');
                Cli::$args = array($db, $app);
                
                break;
            default:
                throw new Exception("Invalid command provided");
        }
        
        Response::$content .= implode("\n", $output) . "\n";
    }
    
    private static function sql($apps)
    {
        $drops = array();
        $creates = array();
        
        foreach ($apps as $app)
        {
            foreach (Saveable::$apps[$app] as $cls)
            {
                $obj = new $cls();
                
                $drops = array_merge($drops, $obj->sql(true));
                $creates = array_merge($creates, $obj->sql());
            }
        }
        
        Response::$content .= implode("\n", $drops) . "\n";
        Response::$content .= implode("\n", $creates) . "\n";
    }
    
    private static function rebuild($db, $specified_app, $apps)
    {
        if ( !in_array($specified_app, $apps) && ($specified_app != 'all') )
        {
            throw new Exception("Specify an app or 'all'");
        }
        
        if ($specified_app != 'all')
        {
            $apps = array($specified_app);
        }
        
        eval('$db = new ' . DB_TYPE . '();');
        
        $drops = array();
        $creates = array();
        
        foreach ($apps as $app)
        {
            foreach (Saveable::$apps[$app] as $cls)
            {
                $obj = new $cls();
                
                foreach ($obj->sql(true) as $drop)
                {
                    $db->drop($drop);
                }
                
                foreach ($obj->sql() as $create)
                {
                    $db->create($create);
                }
            }
        }
    }
    
    public static function test($specified_app, $apps)
    {
        if ( !in_array($specified_app, $apps) && ($specified_app != 'all') )
        {
            throw new Exception("Specify an app or 'all'");
        }
        
        if ($specified_app != 'all')
        {
            $apps = array($specified_app);
        }
        
        Cli::rebuild('test', 'all', $apps);
        
        $output = array();
        
        foreach ($apps as $app)
        {
            $loadable = get_loadable_path($app, 'test.php');
            
            if ($loadable)
            {
                $file = trim(preg_replace(array('/<\?php/', '/\?>/', '/\/\/[^\n]*\n/'), '', file_get_contents($loadable)));
                $raw_tests = preg_split('/\n{2,}/', $file);
                
                $tests = array();
                
                foreach ($raw_tests as $key => $test)
                {
                    $parts = explode("\n", $test);
                    
                    switch (count($parts))
                    {
                        case 1:
                            $tests[] = $parts[0];
                            break;
                        case 2:
                            $tests[$parts[0]] = $parts[1];
                            break;
                        default:
                            throw new Exception("Tests must be one line of code or one line of code, a newline and an expected value followed by two newlines");
                    }
                }
                
                $output[] = "Testing app: {$app}";
                
                $test_count = count($tests);
                $test_count = 0;
                $failure_count = 0;
                
                foreach ($tests as $key => $value)
                {
                    $test = '';
                    $expected = '';
                    
                    try
                    {
                        if (is_string($key))
                        {
                            $test = $key;
                            $expected = $value;
                            
                            $code = '$result = ' . $test . ';';
                        }
                        else
                        {
                            $test = $value;
                            unset($expected);
                            
                            $code = $test . ';';
                        }
                        
                        eval($code);
                    }
                    catch (Exception $e)
                    {
                        $result = "Exception: " . $e->getMessage();
                    }
                    
                    if (isset($expected))
                    {
                        $test_count++;
                        
                        if ($result != $expected)
                        {
                            $output[] = " FAILURE: {$test}";
                            $output[] = "  Expected: {$expected}";
                            $output[] = "  Result: {$result}";
                            
                            $failure_count++;
                        }
                    }
                }
                
                $output[] = "Results for app: {$app}";
                $output[] = " {$test_count} tests";
                $output[] = " {$failure_count} failures";
            }
            else
            {
                $output[] = "No tests found for app: {$app}";
            }
        }
        
        Response::$content .= implode("\n", $output) . "\n";
    }
}
