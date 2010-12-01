<?php
/*
   Ploy
    
   Simple deployment mechanism

   R.Lerdorf WePay Inc. Nov.2010
*/
class Host {
    private $conn = NULL;
    public  $name = NULL;
        
    function __construct($host, $target, $log, $pwd=NULL) {
        $this->log = $log;
        $this->loud = $target['verbose'];
        $this->quiet = $target['quiet'];
        $this->user = $target['user'];
        $this->conn = $this->connect($host, $target, $pwd);
    }

    private function connect($host, $target, $pwd = NULL) {
        $this->name = $host;
        $this->log->verbose("Connecting to $host on port {$target['ssh_port']}");
        if(!$conn = ssh2_connect($host, $target['ssh_port'], NULL, array('disconnect',array($this,'disconnect')))) {
            $this->log->error("Unable to connect");
        }
        $this->log->verbose("Authenticating as {$target['deploy_user']} using {$target['public_key_file']}");
        if(!ssh2_auth_pubkey_file($conn, $target['deploy_user'], $target['public_key_file'], $target['private_key_file'], $pwd)) {
            $this->log->error("Unable to authenticate");
        }
        $this->log->ssh[$host] = $this;
        return $conn;
    }

    private function disconnect($reason, $message, $language) {
        $this->conn = NULL;
        $this->log->error("Server disconnected with reason code [$reason] and message: $message");
    }

    function exec($cmd) {
        $this->log->verbose("Executing remote command: $cmd");
        $stdout = ssh2_exec($this->conn, $cmd." && echo 'RETOK'", 'ansi');
        $stderr = ssh2_fetch_stream($stdout, SSH2_STREAM_STDERR);   
        stream_set_blocking($stderr, true);
        $errors = stream_get_contents($stderr);
        if($errors) $this->log->verbose("STDERR: ".trim($errors));
        stream_set_blocking($stdout, true);
        $output = stream_get_contents($stdout);
        if(!strstr($output,'RETOK')) {
            $this->log->error($output);
        } else {
            $output = substr($output,0,strpos($output,'RETOK'));
        }
        $this->log->verbose(trim($output));
        return $output;
    }

    function scp($local_file, $remote_file, $mode = 0644) {
        $this->log->verbose("scp'ing $local_file to $remote_file mode ".decoct($mode));
        $ret = ssh2_scp_send($this->conn, $local_file, $remote_file, $mode);
        return $ret;
    }

    function sftp($local_file, $remote_file) {
        $this->log->verbose("sftp'ing $local_file to $remote_file");
        $sftp = ssh2_sftp($this->conn);
        $remote = fopen("ssh2.sftp://$sftp$remote_file", 'w');
        $local = fopen($local_file,"r");
        $ret = stream_copy_to_stream($local, $remote);
        return $ret;
    }
}