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
        $p = proc_open('nice -n19 ./timeout -k9 5s '.$cmd,
                $dscs, $pipes, null, null);
        //$p = proc_open($cmd, $dscs, $pipes, null, null);
        fwrite($pipes[0], $input);
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        return proc_close($p) == 0;
    }

    function ceu ($ceu_code, $ana, &$stdout, &$stderr)
    {
        $cmd = "./ceu --m4 --c-calls '_printf _assert _soma' - --output -";
        if ($ana)
            $cmd = $cmd . ' --analysis_'.$ana;
        return exe($cmd, $ceu_code, $stdout, $stderr);
    }

    function gcc ($c_code, $run_name, &$stdout, &$stderr)
    {
$all = <<<XXXX
#include <stdio.h>
#include <assert.h>

#include <stdint.h>
typedef int64_t  s64;
typedef int32_t  s32;
typedef int16_t  s16;
typedef int8_t    s8;
typedef uint64_t u64;
typedef uint32_t u32;
typedef uint16_t u16;
typedef uint8_t   u8;

XXXX;
$all = $all . $c_code;
$all = $all . <<<XXXX
int main (int argc, char *argv[])
{
    int ret = ceu_go_all(0);
    printf("*** END: %d\\n", ret);
    return ret;
}

XXXX;
        return exe('gcc -xc -std=c99 - -o'.$run_name, $all, $stdout, $stderr);
    }
?>

<?php if (isset($_REQUEST['go'])): ?>

    <?php
        $output = '';
        $debug  = '';

        $input = $_REQUEST['input'];
        if ($input == '') {
            $all =  $_REQUEST['code'];
        } else {
            $all =  ' par/or do' .
                        $_REQUEST['code'] .
                    ' with' .
                        ' async do' .
                            $input .
                        ' end' .
                    ' end' .
                    ' return 0;';
        }

        $ret = true;
        $out = '';
        $err = '';

        if (isset($_REQUEST['ana'])) {
            $ret = ceu($all, 'run', $stdout, $stderr);
            $err = $err . $stderr;
            if ($ret) {
                $run_name = 'tmp/'. uniqid('ceu_') . '.exe';
                $ana = file_get_contents('analysis.c');
                $ana = preg_replace('/\\#include "_ceu_code\\.cceu"/',
                             $stdout, $ana);
                $ret = exe('gcc -xc -std=c99 - -o '.$run_name,
                            $ana, $stdout, $stderr);
                $err = $err . $stderr;
                if ($ret) {
                    $ret = exe($run_name.' _ceu_analysis.lua',
                            '', $stdout, $stderr);
                    $err = $err . $stderr;
                }
                unlink($run_name);
            }
        }

        if ($ret)
        {
            if (isset($_REQUEST['ana'])) {
                $ret = ceu($all, 'use', $stdout, $stderr);
            } else {
                $ret = ceu($all, false, $stdout, $stderr);
            }
            $err = $err . $stderr;

            if ($ret) {
                $run_name = 'tmp/'. uniqid('ceu_') . '.exe';
                $ret = gcc($stdout, $run_name, $stdout, $stderr);
                $err = $err . $stderr;

                if ($ret) {
                    exe($run_name, '', $stdout, $stderr);
                    $err = $err . $stderr;
                    $out = $stdout;
                    unlink($run_name);
                }
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

        $body = "=== IP ===\n\n"      . $ip . "\n\n" .
                "=== CODE ===\n\n"    . $_REQUEST['code'] . "\n\n" .
                "=== INPUT === \n\n"  . $input . "\n\n" .
                "=== OUTPUT === \n\n" . $out . "\n\n" .
                "=== DEBUG === \n\n"  . $err . "\n\n" .
                "======================================" . "\n\n";

        $f = fopen('tmp/TRY.txt', 'a');
        fwrite($f, $body);
        fclose($f);

        $to = "francisco.santanna@gmail.com";
        $subject = "[try-ceu] new code";
        if (!mail($to, $subject, $body))
            error_log("message delivery failed");
    ?>
<?php endif; ?>
