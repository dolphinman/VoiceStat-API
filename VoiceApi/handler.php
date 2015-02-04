<?php

/**
 * Designed for Redhead Technology
 * @author Matthew de Cuijper <mcdecuijper@gmail.com>
 * @copyright Copyright (c) 2015, Matthew de Cuijper
 * @version 1.0
 */


$method   = (isset($_GET['a']) ? $_GET['a'] : null); //<status|start|stop|restart>
$service  = (isset($_GET['b']) ? $_GET['b'] : null); //service <name|all>
$optional = (isset($_GET['c']) ? $_GET['c'] : null); //optioneel
$voice = new voiceHandler(false, $method, $service, $optional);


class voiceHandler {

    private $opt = array (
        "services" => array (
            "MySQL",
            "SSH",
            "Fail2Ban",
            "SendMail",
            "Dahdi",
            "Apache",
            "Samba",
            "Iptables",
            "Webmin",
            "Crond",
            "SendMail",
            "asterisk"
        ),
        "blacklist" => array (
            "httpd"
        )
    );

    /**
     * __construct
     * Sets the header <xml|plaintext> and starts handling the request
     */
    public function __construct($xml = false, $method, $service, $optional){
        header(sprintf('Content-Type: %s; charset=utf-8', ($xml ? 'application/xml': 'text/plain')));
        $this->handler($method, $service, $optional);
    }

    /**
     * handler
     * Handles the request
     */
    public function handler($a = null, $b = null, $c = null){
        $method = $service = $optional = $str = null;
        if((isset($a)) && ($a != null)) $method = $a;
        if((isset($b)) && ($b != null)) $service = $b;
        if((isset($c)) && ($c != null)) $optional = $c;

        if($method != null){
            switch($method){
                case "status":
                    if($service != null)
                        ($service == "all" ? $this->funcs("status", "all") : $this->funcs("status", $service));
                    else
                        $this->badRequest(0);
                break;
                case "start":
                    if($service != null)
                        (in_array($service, $this->opt["blacklist"]) ? $this->badRequest(4) : $this->funcs("start", $service));
                    else
                        $this->badRequest(1);
                break;
                case "stop":
                    if($service != null)
                        (in_array($service, $this->opt["blacklist"]) ? $this->badRequest(5) : $this->funcs("stop", $service));
                    else
                        $this->badRequest(2);
                break;
                case "restart":
                    if($service != null)
                        (in_array($service, $this->opt["blacklist"]) ? $this->badRequest(4) : $this->funcs("restart", $service));
                    else
                        $this->badRequest(3);
                break;
            }
        }
        return;
    }

    /**
     * funcs
     * Has a set functions which executes and return data
     */
    private function funcs($call, $service){
        $str = $result = null;
        $str = $this->xmlHeader();
        switch($call){
            case 'start':
                $result = exec(sprintf("/usr/bin/sudo /sbin/service %s start", $service));
                $str .= ("<result><service>".$service."</service><status>".$result."</status></result>");
            break;
            case 'stop':
                $result = exec(sprintf("/usr/bin/sudo /sbin/service %s stop", $service));
                $str .= ("<result><service>".$service."</service><status>".$result."</status></result>");
            break;
            case 'restart':
                $result = exec(sprintf("/usr/bin/sudo /sbin/service %s restart", $service));
                $str .= ("<result><service>".$service."</service><status>".$result."</status></result>");
            break;
            case 'status':
                if($service == "all"){
                    $str .= "<result>";
                    foreach($this->opt["services"] as $serv){
                        $exec = "\n<service><name>%s</name><status>%s</status></service>";
                        $str .= sprintf($exec, $serv, trim(shell_exec("sh /var/www/html/voiceapi/stat.sh " . strtolower($serv))));
                    }
                    $str .= "</result>";
                } else {
                    $exec = "<result><service>%s</service><status>%s</status></result>";
                    $str .= sprintf($exec, $service, trim(shell_exec("sh /var/www/html/voiceapi/stat.sh " . strtolower($service))));
                }
            break;
        }
        print $str;
    }

    /**
     * badRequest
     * Returns the 'exception'
     */
    private function badRequest($type){
        $str = $this->xmlHeader();
        switch($type){
            case '0':
                $str .= "<result><status>0:BAD STATUS CALL</status></result>";
            break;
            case '1':
                $str .= "<result><status>1:BAD START CALL</code></error>";
            break;
            case '2':
                $str .= "<result><status>2:BAD STOP CALL</code></error>";
            break;
            case '3':
                $str .= "<result><status>3:BAD RESTART CALL</code></error>";
            break;
            case '4':
                $str .= "<result><status>4:BAD START CALL, SERVICE NAME BLACKLISTED</code></error>";
            break;
            case '5':
                $str .= "<result><status>5:BAD STOP CALL, SERVICE NAME BLACKLISTED</code></error>";
            break;
            case '6':
                $str .= "<result><status>6:BAD RESTART CALL, SERVICE NAME BLACKLISTED</code></error>";
            break;
        }
        print $str;
    }

    /**
     * xmlHeader
     * xml head
     */
    private function xmlHeader(){
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
    }
}

?>