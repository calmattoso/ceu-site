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

        $p = proc_open('nice -n19 ./timeout -k9 1s '.$cmd,
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

    function ceu ($ceu_code, &$stdout, &$stderr)
    {
        //$cmd = "./ceu --warn-nondeterminism --c-calls '_printf _assert _inc' - --out-h - --out-c -";
        $cmd = "./ceu --c-calls '_printf _assert _inc' - --out-h - --out-c -";
//$f = fopen('_aaa.ceu', 'w');
//fwrite($f, $ceu_code);
//fclose($f);
        return exe($cmd, $ceu_code, $stdout, $stderr);
    }

    function gcc ($c_code, $run_name, &$stdout, &$stderr)
    {
$all = <<<XXXX
#include <stdio.h>

XXXX;
$all = $all . $c_code . <<<XXXX

int main (int argc, char *argv[])
{
    byte CEU_DATA[sizeof(CEU_Main)];
    tceu_app app;
        app.data = (tceu_org*) &CEU_DATA;
        app.init = &ceu_app_init;

    int ret = ceu_go_all(&app);
    printf("*** END: %d\\n", ret);

    return ret;
}
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
            $all =  ' par do ' .
                        $_REQUEST['code'] .
                    ' with ' .
                        ' async do ' .
                            $input .
                        ' end ' .
                    ' end ' ;
        }

        $ret = true;
        $out = '';
        $err = '';

        if ($ret)
        {
            $ret = ceu($all, $stdout, $stderr);
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
        $subject = "[try-ceu] new code";
        if (!mail($to, $subject, $body))
            error_log("message delivery failed");
    ?>
<?php endif; ?>
