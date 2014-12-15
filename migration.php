<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 12/15/14
 * Time: 9:22 PM
 */
include_once 'vendor/autoload.php';

class migration {
    public $zm_host;
    public $zm_user;
    public $zm_passwrod;
    public $accounts;
    public $db_host;
    public $db_user;
    public $db_pass;
    public $db_name;

public function __construct($zm_host, $zm_user, $zm_password, $domain, array $accounts, $db_host, $db_user, $db_pass, $db_name)
{
    // zimbra server credentials
    $this->zm_host = $zm_host;//readline("Zimbra Host: ").PHP_EOL;
    $this->zm_user = $zm_user;//readline("Zimbra Admin Username: ").PHP_EOL;
    $this->zm_password = $zm_password;//readline("Zimbra Admin Password: ").PHP_EOL;

    //mysql db information
    $this->db_host = $db_host;
    $this->db_user = $db_user;
    $this->db_pass = $db_pass;
    $this->db_name = $db_name;

    //zimbra connection
}

}