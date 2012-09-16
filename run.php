<?php
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

    function ceu ($ceu_code, $dfa, &$stdout, &$stderr)
    {
        $cmd = './ceu --m4 --c-calls false -';
        if ($dfa)
            $cmd = $cmd . ' --dfa';
        return exe($cmd, $ceu_code, $stdout, $stderr);
    }

    function gcc ($c_code, $run_name, &$stdout, &$stderr)
    {
$all = <<<XXXX
#include <stdio.h>
#include <assert.h>
typedef long  s32;
typedef short s16;
typedef char   s8;
typedef unsigned long  u32;
typedef unsigned short u16;
typedef unsigned char   u8;

XXXX;
$all = $all . $c_code;
$all = $all . <<<XXXX
int main (int argc, char *argv[])
{
    int ret = ceu_go_all();
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
            $input = 'nothing;' ;
        }

        $all =  'par/and do ' .
                    $_REQUEST['code'] .
                ' with ' .
                    ' async do ' .
                        $input .
                    ' end ' .
                ' end';

        $ret = ceu($all, isset($_REQUEST['dfa']), $out_ceu, $err_ceu);
        $output = $out_ceu;
        $debug  = $err_ceu;

        if ($ret and $_REQUEST['mode']=='run') {
            $run_name = 'tmp/'. uniqid('ceu_') . '.exe';
            if (gcc($out_ceu, $run_name, $out_gcc, $err_gcc)) {
                exe($run_name, '', $out_run, $err_run);
                unlink($run_name);
                $output = $out_run;
                $debug  = $err_ceu . $out_gcc . $err_gcc . $err_run;
            } else {
                $output = '';
                $debug  = $err_ceu . $out_gcc . $err_gcc;
            }
        }

        if ($_REQUEST['new']) {
            $ip = isset_or($_SERVER['REMOTE_ADDR'],'') . ' | ' .
                  isset_or($_SERVER['HTTP_X_FORWARDED_FOR'], '') . ' | ' .
                  isset_or($_SERVER['HTTP_CLIENT_IP'],'');

            $body = "=== IP ===\n\n"      . $ip . "\n\n" .
                    "=== CODE ===\n\n"    . $_REQUEST['code'] . "\n\n" .
                    "=== INPUT === \n\n"  . $input . "\n\n" .
                    "=== OUTPUT === \n\n" . $output . "\n\n" .
                    "=== DEBUG === \n\n"  . $debug . "\n\n" .
                    "======================================" . "\n\n";

            $f = fopen('tmp/TRY.txt', 'a');
            fwrite($f, $body);
            fclose($f);

/*
            $to = "francisco.santanna@gmail.com";
            $subject = "[try-ceu] new code";
            if (!mail($to, $subject, $body))
                error_log("message delivery failed");
*/
        }

        $response = array(
          "output" => htmlspecialchars($output),
          "debug"  => htmlspecialchars($debug)
				);

        echo json_encode($response); 
    ?>
<?php endif; ?>
