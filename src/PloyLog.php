<?php
/*
   Ploy
    
   Simple deployment mechanism

   R.Lerdorf WePay Inc. Nov.2010
*/
class PloyLog {
    public $loud     = false;
    public $quiet    = false;
    public $buffer   = '';
    public $rollback = array();
    public $ssh      = array();  // ssh connections for rollbacks
    public $progress = 0;        // Estimated completion percentage
    private $rolling_back = false;

    function __construct($rev) {
        $this->rev = $rev;
    }
    
    function flush() {
        if(php_sapi_name()=='fpm-fcgi') {
            echo str_repeat(" ",4096); // blah - remove for non-fastcgi where flush() works
            echo "\n<script>$('#progress_bar .ui-progress').animateProgress({$this->progress},null);</script>\n";
            flush();
        } else flush();
    }

    function output($str, $buffer=true) {
        if(php_sapi_name()!="cli") $eol = "<br />\n";
        else $eol = "\n";
        if($buffer) $this->buffer .= date("M.d H:i:s").": $str\n";
        if(!$this->quiet && $str) echo $str.$eol;   
        $this->flush();
    }

    function verbose($str) {
        $this->buffer .= date("M.d H:i:s").": $str\n";
        if($this->loud) $this->output($str, false);
        else $this->flush();
    }

    function error($str) {
        if(php_sapi_name()!="cli") $eol = "<br />\n";
        else $eol = "\n";
        $this->buffer .= date("M.d H:i:s").": $str\n";
        error_log("Connection Error: $str");
        if(!$this->rolling_back) {
            echo "Deploy failed - rolling back".$eol;
            $this->flush();
            $this->rollback_exec();
            if(empty($GLOBALS['log_file'])) $GLOBALS['log_file'] = "/tmp/ploy-{$this->rev}.txt";
            file_put_contents($GLOBALS['log_file'], $this->buffer);
            echo "deploy log written to $GLOBALS[log_file]".$eol;
        }
        $this->flush();
        exit(1);    
    }

    function rollback_set($cmd, $ip='local') {
        $this->rollback[$ip] = array($cmd);     
    }

    function rollback_add($cmd, $ip='local') {
        $this->rollback[$ip][] = $cmd;      
    }

    function rollback_exec($ip=NULL) {
        if(!count($this->rollback)) {
            $this->output("Nothing to roll back\n");
        }
        $this->rolling_back = true;

        foreach($this->rollback as $ip=>$cmds) {
            // Roll back in reverse order
            foreach(array_reverse($cmds) as $cmd) {
                if($ip=='local') {
                    $this->output("Local: $cmd");
                    `$cmd`;
                } else {
                    $this->ssh[$ip]->exec($cmd);
                }
            }
        }
    }
}
