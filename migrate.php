#!/usr/bin/php
<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 12/15/14
 * Time: 9:22 PM
 */


require_once 'zimbraAdmin.php';
class migrate {
    public $zim;
    public $domains = array();
    public $db_host;
    public $db_user;
    public $db_pass;
    public $db_name;
    public $pdo;
    public $server;
    public $user;
    public $pass;
    public $server2;
    public $password2 = 'P@ssw0rd';
    public $migrationScript;

    public function __construct($server, $user, $pass, $db_host, $db_user, $db_pass, $db_name, $server2)
    {
        $this->db_name = $db_name;
        $this->db_host = $db_host;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;
        $this->server = $server;
        $this->user = $user;
        $this->pass = $pass;
        $this->server2 = $server2;

        $this->pdo = new PDO(sprintf("mysql:dbname=%s;host=%s",$this->db_name,$this->db_host),$this->db_user, $this->db_pass);
        $this->zim = new \zimbraAdmin('mail2.i-webb.net', 'admin@mail2.i-webb.net', '!AFtony19833');
        $this->doMigrate();

    }
    public function getAllDomains()
    {
        if($request = $this->zim->zimbra_get_all_domains())
        {
            $out = array();
            foreach ($request['DOMAIN'] as $domain) {
                $out[] = $domain['NAME'];
            }

            $this->domains = $out;

        }
        else
        {
            return false;
        }
    }
    public function doMigrate()
    {
        $this->getAllDomains();

        foreach ($this->domains as $domain) {
            $this->insertDomain($domain);
            $this->createMailboxes($domain);
        }
        file_put_contents('migrationScript.sh',substr($this->migrationScript,0,strlen($this->migrationScript)-4));
        return;

    }
    public function insertDomain($name)
    {
        try
        {
            $ask = "select domain from domain where domain = '$name'";
            if(count($this->pdo->query($ask)->fetchAll()) > 0)
            {
                echo "\n"."Domain $name already exists. Skipping domain creation".PHP_EOL;
                return false;
            }
            else
            {

                $stmt = "INSERT into domain values(:new_domain,NULL,NULL,0,0,0,0,'dovecot',0,NULL, now(), now(),'9999-12-31 00:00:00',1)";
                $query = $this->pdo->prepare($stmt);
                $query->execute(array(':new_domain' => $name));
                return;
            }
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            return false;
        }

    }
    public function createMailboxes($domain)
    {
        $mailboxes = $this->getDomainAccounts($domain);
        $cmd = realpath('create_mail_user_SQL.sh')." ".$domain;
        $imap = '';
        foreach ($mailboxes as $mailbox) {
            $qr = "select username from mailbox where username= '$mailbox'";
            if(count($this->pdo->query($qr)->fetchAll()) > 0)
            {
                $split = explode("@", $mailbox);
                $name = $split[0];
                $imap .= "imapsync --host1 ".$this->server." --ssl1 --ssl2 --user1 ".$this->user." --password1 ". $this->pass.
                    " --authuser1 ".$this->user." --host2 ".$this->server2." --user2 ".$mailbox." --password2 ".$this->password2." && ";

                echo "Mailbox $mailbox already exists. Skipping creation of $mailbox".PHP_EOL;
            }
            else
            {
                $split = explode("@", $mailbox);
                $name = $split[0];
                $cmd .= " ".$name;
                //if(file_exists('dump/output.sql')){ unlink('dump/output.sql');}
                //exec("sh %s % %",realpath("create_mail_user_SQL.sh"),$domain, trim($name));
                $imap .= "imapsync --host1 ".$this->server." --ssl1 --ssl2 --user1 ".$this->user." --password1 ". $this->pass.
                    " --authuser1 ".$this->user." --host2 ".$this->server2." --user2 ".$mailbox." --password2 ".$this->password2." && ";
            }
        }
        //Delete old SQL file
        echo 'Deleting old sql file'.PHP_EOL;
        if(file_exists('dump/output.sql')){ unlink('dump/output.sql');}

        //Generate SQL file
        echo 'Generating new sql file for mailboxes'.PHP_EOL;
        shell_exec("$cmd");

        //Execue SQL to create mailboxes
        echo 'Executing sql in db'.PHP_EOL;
        $cr = "mysql -u".$this->db_user." -p".$this->db_pass." -h".$this->db_host." -D".$this->db_name." < ".realpath('dump/output.sql');
        shell_exec("$cr");
        $this->migrationScript .= $imap;
    }

    public function getDomainAccounts($domain)
    {
        if($response = $this->zim->zimbra_get_all_accounts($domain))
        {
            $result = array();
            foreach($response as $account)
            {
                //$result = array(); // single account record
                //$result['id'] = $account['ID'];
                $exclude = array(
                    'galsync@'.$domain,
                    'ham@'.$domain,
                    'spam@'.$domain,
                    'admin@'.$domain,
                    'virus-quarantine@'.$domain,
                    'postmaster@'.$domain,
                );
                if(!in_array($account['NAME'], $exclude))
                {
                    $result[] = $account['NAME'];
                }
            }

            return $result;
        }
    }





}

$obj = new migrate('mail2.i-webb.net','tony@mail2.i-webb.net','AFtony19833','localhost','root','AFtony19833','vmail','mx.softcube.co');
//$obj->insertDomain('example.com');
//print_r($obj->getDomainAccounts('bullion.com.gh'));
//exec(sprintf("%s > %s 2>&1 & echo $! >> %s", "ls -la", 'dump/outfile', "dump/pid"),$out,$ret);
//echo realpath('create_mail_user_SQL.sh').PHP_EOL;
//$obj->createMailboxes('petroniacity.com');