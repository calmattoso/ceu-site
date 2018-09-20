<?php
    function isset_or ($v, $alt)
    {
        if (isset($v)) {
            return $v;
        } else {
            return $alt;
        }
    }

    function exe ($cmd, $input, &$stdout, &$stderr)
    {
        $dscs = array (
            0 => array("pipe", "r"),  // stdin
            1 => array("pipe", "w"),  // stdout
            2 => array("pipe", "w"),  // stderr
        );

        $p = proc_open('./timeout -k9 1s '.$cmd,
                $dscs, $pipes, null, null);
        //$p = proc_open('nice -n19 ./timeout -k9 1s '.$cmd,
                //$dscs, $pipes, null, null);
        //$p = proc_open($cmd, $dscs, $pipes, null, null);
        fwrite($pipes[0], $input);
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        return proc_close($p) == 0;
    }

    function ceu ($ceu_code, $run_name, &$stdout, &$stderr)
    {
        //$cmd = "./ceu --warn-nondeterminism --c-calls '_printf _assert _inc' - --out-h - --out-c -";
        //$cmd = "./ceu --c-calls '_printf _assert _inc' - --out-h - --out-c -";
        $cmd = "./ceu --pre --pre-input=-" .
                    " --ceu " .
                    " --env --env-types=types.h --env-main=main.c" .
                    " --cc --cc-output=$run_name";
//$f = fopen('_aaa.ceu', 'w');
//fwrite($f, $ceu_code);
//fclose($f);
        $f = fopen('tmp/xxx.ceu', 'w');
        fwrite($f, $ceu_code);
        fclose($f);
        return exe($cmd, $ceu_code, $stdout, $stderr);
    }

    function gcc ($c_code, $run_name, &$stdout, &$stderr)
    {
$all = <<<XXXX
#include <stdio.h>

#define ceu_out_assert(v)
#define ceu_out_log(m,s)

XXXX;
$all = $all . $c_code . <<<XXXX
XXXX;
        // TODO: -ansi
        return exe('gcc -xc - -o '.$run_name, $all, $stdout, $stderr);
    }
?>

<?php if (isset($_REQUEST['go'])): ?>

    <?php
        $output = '';
        $debug  = '';

        $changed = ($_REQUEST['changed'] == 'true');

        $input = $_REQUEST['input'];
        if ($input == '') {
            $all =  $_REQUEST['code'];
        } else {
            $all =  "native/nohold _printf;     " .     // no new lines
                    "native/pre do              " .     // correct lines in
                    "     ##include <stdio.h>   " .     // compiling errors
                    "end                        " .
                    "native/end;                " .
                    "par/or do                  " .
                        $_REQUEST['code']       ." ".
                    "with                       " .
                    "   await async do          " .
                            $input              ." ".
                    "   end                     " .
                    "with                       " .
                    "   do await FOREVER; end   " .
                    "   _printf(\"oi\");        " .
                    "end                        " .
                    "escape 0;                  " ;
        }

        $ret = true;
        $out = '';
        $err = '';

        if ($ret)
        {
            $run_name = 'tmp/'. uniqid('ceu_') . '.exe';
            $ret = ceu($all, $run_name, $stdout, $stderr);
            $err = $err . $stderr;
            if ($ret) {
                exe($run_name, '', $stdout, $stderr);
                $err = $err . $stderr;
                $out = $stdout;
                unlink($run_name);
            }
        }

        $response = array(
          "output" => htmlspecialchars($out),
          "debug"  => htmlspecialchars($err)
        );
        echo json_encode($response); 

        $ip = isset_or($_SERVER['REMOTE_ADDR'],'') . ' | ' .
              isset_or($_SERVER['HTTP_X_FORWARDED_FOR'], '') . ' | ' .
              isset_or($_SERVER['HTTP_CLIENT_IP'],'');

        $comments = <<<XXX
/*                                                                  
 * Any comments or questions about this example?                    
 * Fill in this space and "Run" the example.                      
 * We'll get an e-mail with your comments.                          
 *                                                                  
 * NAME:                                                            
 * E-MAIL:                                                          
 * COMMENTS:                                                        
 *                                                                  
 *                                                                  
 */                                                                 
XXX;
        $same = strpos($input, $comments);
        if ($same) {
            $subject = "[try-ceu] new code";
        } else {
            $subject = "new code";
        }

        $body = "=== IP ===\n\n"      . $ip . "\n\n";
        if ($changed) {
            $body = $body .
                "=== CODE ===\n\n"    . $_REQUEST['code'] . "\n\n" .
                "=== INPUT === \n\n"  . $input . "\n\n" .
                "=== OUTPUT === \n\n" . $out . "\n\n" .
                "=== DEBUG === \n\n"  . $err . "\n\n";
        } else {
            $body = $body .
                "=== NO CHANGES === \n\n";
        }
        $body = $body . "======================================" . "\n\n";

        $f = fopen('tmp/TRY.txt', 'a');
        fwrite($f, $body);
        fclose($f);

        $to = "francisco.santanna@gmail.com";
        if (!mail($to, $subject, $body)) {
            error_log("message delivery failed");
        }
    ?>
<?php endif; ?>
