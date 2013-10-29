<?php
class SshTask extends Shell {
	/**
    * Path to work in
    */
  var $path = null;
  
  /**
    * Connection to work in
    */
  var $connection = null;
  
  /**
    * Connection to work in
    */
  var $verbose = true;
	
  /**
  	* 
  	*/
	function execute(){
		$this->out('Ssh Utility Task');
	}
	/**
    * Connect and authenticate to an ssh server
    * @param string server to connec to
    * @param string user to use to conenct to server with
    * @param string password to use to conenct to server with
    * @param string port to use to conenct to server with
    * @return void
    */
  function open($server, $user, $pass, $port = 22){
    if(!function_exists("ssh2_connect")){
      $this->__errorAndExit("function ssh2_connect doesn't exit.  Run sudo apt-get install libssh2-1-dev libssh2-php");
    }
    
    if($server == 'server.example.com'){
      $this->__errorAndExit("Please fill in the deploy() function in your new app task");
    }
    
    $this->connection = ssh2_connect($server, $port);
    
    if(!$this->connection){
      $this->__errorAndExit("Unable to connect to $server");
    }
    
    if(!ssh2_auth_password($this->connection, $user, $pass)){
      $this->__errorAndExit("Failed to authenticate");
    }
  }
  
  /**
    * Send and receive the result of an ssh command
    * @param string command to execute on remote server
    * @param boolean get stderr instead of stdout stream
    * @return mixed result of command.
    */
  function exec($cmd, $error = false){
    if(!$this->connection){
      $this->__errorAndExit("No open connection detected.");
    }
    
    if($this->path){
      $cmd = "cd {$this->path} && $cmd";
    }
    if($this->verbose){
      $this->out($cmd);
    }
    $stream = ssh2_exec($this->connection, $cmd);
    
    if(!$stream){
      $this->__errorAndExit("Unable to execute command $cmd");
    }
    
    $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
    
    stream_set_blocking($stream, true);
    stream_set_blocking($errorStream, true);
    
    $retval = $error ? stream_get_contents($errorStream) : stream_get_contents($stream);
    $retval = trim($retval);
    
    fclose($stream);
    fclose($errorStream);
    
    //Show output or at least progress dots.
    if($this->verbose){
      $this->out($retval);
    }
    else {
      echo '.';
    }
    
    return $retval;
  }
  
  /**
    * Set the path to append to each command.
    * @param string path (without cd)
    * @return void
    */
  function setpath($path){
    $this->path = $path;
  }
  
  /**
    * Close the current connection
    */
  function close(){
    if($this->connection){
      $this->ssh_exec("exit");
    }
    $this->connection = null;
  }
  
  /**
  	* Get a file from the remote server to the local server
  	* @param string remote directory to copy from
  	* @param string local directoy to copy to.
  	* @return boolean success
  	*/
  function get($remote, $local){
  	if(!$this->connection){
  		$this->__errorAndExit('No Connection to work with');
  	}
  	return ssh2_scp_recv($this->connection, $remote, $local);
  }
  
  /**
  	* Get a file from the remote server to the local server
  	* @param string remote directory to copy from
  	* @param string local directoy to copy to.
  	* @return boolean success
  	*/
  function put($remote, $local){
  	if(!$this->connection){
  		$this->__errorAndExit('No Connection to work with');
  	}
  	return ssh2_scp_send($this->connection, $remote, $local);
  }
  
  /**
    * Private method to output the error and exit(1)
    * @param string message to output
    * @return void
    * @access private
    */
  function __errorAndExit($message){
    $this->out("Error: $message");
    exit(1);
  }
}
?>