<?php

echo "Libreria funzioni comunicazione con Telegram creata da @as3ii\n";

$update = json_decode($content,TRUE);

// variabili comuni
$updateID = $update["update_id"];
if(isset($update["message"])) {
    // user
    $userID = $update["message"]["from"]["id"];
    $username = $update["message"]["from"]["username"];
    $nome = addslashes($update["message"]["from"]["first_name"]);
    if(isset($update["message"]["from"]["last_name"])) $cognome = addslashes($update["message"]["from"]["last_name"]);
    $isbot = $update["message"]["from"]["is_bot"];
    $lang = $update["message"]["from"]["language_code"];
    // chat
    $chatID = $update["message"]["chat"]["id"];
    $type = $update["message"]["chat"]["type"];
    $cusername = addslashes($update["message"]["chat"]["username"]);
    if($type == "supergroup") $gtitle = $update["message"]["chat"]["title"];
    // media
    if(isset($update["message"]["voice"]["file_id"]))   $voice = $update["message"]["voice"]["file_id"];
    if(isset($update["message"]["photo"][0]["file_id"]))$photo = $update["message"]["photo"][0]["file_id"];
    if(isset($update["message"]["document"]["file_id"]))$document = $update["message"]["document"]["file_id"];
    if(isset($update["message"]["audio"]["file_id"]))   $audio = $update["message"]["audio"]["file_id"];
    if(isset($update["message"]["sticker"]["file_id"])) $sticker = $update["message"]["sticker"]["file_id"];
    // entities [index][offset/lenght/type]
    if(isset($update["message"]["entities"]))   $entities[] = $update["message"]["entities"];

    $msg = addslashes($update["message"]["text"]);
    $msgID = $update["message"]["message_id"];
    $date = $update["message"]["date"];

    if(isset($update["message"]["reply_to_message"])) {
        $rmsg = addslashes($update["message"]["reply_to_message"]["text"]);
        $rmsgID = $update["message"]["reply_to_message"]["message_id"];
        $ruserID = $update["message"]["reply_to_message"]["from"]["id"];
        $rusername = $update["message"]["reply_to_message"]["from"]["username"];

    }
}
elseif(isset($update["edited_message"])) {
    // user
    $userID = $update["edited_message"]["from"]["id"];
    $username = $update["edited_message"]["from"]["username"];
    $nome = addslashes($update["edited_message"]["from"]["first_name"]);
    if(isset($update["edited_message"]["from"]["last_name"]))$cognome = addslashes($update["edited_message"]["from"]["last_name"]);
    $isbot = $update["edited_message"]["from"]["is_bot"];
    $lang = $update["edited_message"]["from"]["language_code"];
    // chat
    $chatID = $update["edited_message"]["chat"]["id"];
    $type = $update["edited_message"]["chat"]["type"];
    $cusername = $update["edited_message"]["chat"]["username"];
    if($type == "supergroup") $gtitle = $update["edited_message"]["chat"]["title"];
    // media
    $voice = $update["edited_message"]["voice"]["file_id"];
    $photo = $update["edited_message"]["photo"][0]["file_id"];
    $document = $update["edited_message"]["document"]["file_id"];
    $audio = $update["edited_message"]["audio"]["file_id"];
    $sticker = $update["edited_message"]["sticker"]["file_id"];
    // entities [index][offset/lenght/type]
    $entities[] = $update["edited_message"]["entities"];

    $msg = addslashes($update["edited_message"]["text"]);
    $msgID = $update["edited_message"]["message_id"];
    $date = $update["edited_message"]["date"];
    $edit_date = $update["edited_message"]["edit_date"];

    if(isset($update["edited_message"]["reply_to_message"])) {
        $rmsg = addslashes($update["edited_message"]["reply_to_message"]["text"]);
        $rmsgID = $update["edited_message"]["reply_to_message"]["message_id"];
        $ruserID = $update["edited_message"]["reply_to_message"]["from"]["id"];
        $rusername = $update["edited_message"]["reply_to_message"]["from"]["username"];

    }
}
elseif(isset($update["callback_query"])) {
    $cbID = $update["callback_query"]["id"];
    $chatID = $update["callback_query"]["message"]["chat"]["id"];
    $userID = $update["callback_query"]["from"]["id"];
    $msg = addslashes($update["callback_query"]["data"]);
    $msgID = $update["callback_query"]["message"]["message_id"];
    $username = $update["callback_query"]["from"]["username"];
    $nome = addslashes($update["callback_query"]["from"]["first_name"]);
    if(isset($update["callback_query"]["from"]["last_name"])) $cognome = addslashes($update["callback_query"]["from"]["last_name"]);
}
elseif(isset($update["inline_query"])) {
    $queryID = $update["inline_query"]["id"];
    // user
    $userID = $update["inline_query"]["from"]["id"];
    $username = $update["inline_query"]["from"]["username"];
    $nome = addslashes($update["inline_query"]["from"]["first_name"]);
    if(isset($update["inline_query"]["from"]["last_name"])) $cognome = addslashes($update["inline_query"]["from"]["last_name"]);
    $lang = $update["inline_query"]["from"]["language_code"];
    // data
    $msg = addslashes($update["inline_query"]["query"]);
    $offset = $update["inline_query"]["offset"];
}


// importa config.ini se non già importato
if(!isset($config)) {
	$config = parse_ini_file("conf.ini",TRUE) or exit("\nImpossibile aprire \"conf.ini\"");
}
$api = "bot".$config["token"];

// librera per invio dati
require_once("http-post.php");


/* --> https://core.telegram.org/bots/api#sendmessage <-- */

/** Inline menu:
// https://core.telegram.org/bots/api#inlinekeyboardbutton
$menu[] = array(
    array(
    "text" => "",
    "url" => "",
    "callback_data" => "",
    ), // ulteriori tasti sulla stessa riga);
**/

/**Non-inline menu: //https://core.telegram.org/bots/api#replykeyboardmarkup
$menu[] = array("testo","ulteriori tasti sulla stessa riga");
**/

// sendMessage
function sm($chatID, $text, $menu=false, $inline=false, $disprev=false, $replyto=false, $forcereply=false, $disnoti=false, $pm='HTML') {
    global $api;

    // argomenti richiesta
    if($menu) {
    	if(is_string($menu)) {
        	if(stripos($menu,"rimuovi") === 0) {
            	$rm = array("remove_keyboard" => true);
        	}
        } elseif($inline) {
            $rm = array("inline_keyboard" => $menu);
        } else {
            $rm = array(
                "keyboard" => $menu,
                "resize_keyboard" => true,
            );
        }
        $rm = json_encode($rm);
    } elseif($forcereply) {
        $rm = array("force_reply" => true);
        $rm = json_encode($rm);
    } else $rm = false;

    // array principale delle imppostazioni
    $arg = array(
        "chat_id" => $chatID,
        "text" => $text,
        "parse_mode" => $pm
    );
    if($disprev) $arg["disable_web_page_preview"] = $disprev;
    if($disnoti) $arg["disable_notification"] = $disnoti;
    if($replyto) $arg["reply_to_message_id"] = $replyto;
    if($menu xor $forcereply) $arg["reply_markup"] = $rm;

    // dati della richiesta
    $url = "https://api.telegram.org/$api/sendMessage";
    $args = json_encode($arg);

    // richiesta
    $response = http_post($url,$args); // http-post("url","argomenti passati per json_encode");
    $response = json_decode($response, true);

    // decodifica json risposta
    if(!$response["ok"]) {
        return array($response,$arg);   // response in caso di errore={"ok":false,"error_code":4xx,"description":"error description"}
    } else {
        return true;
    }
}


// send foto
function sp($chatID, $photo, $menu=false, $inline=false, $replyto=false, $caption='', $forcereply=false, $disnoti=false) {
    global $api;

    // argomenti richiesta
    if($menu) {
        if(is_string($menu)) {
        	if(stripos($menu,"rimuovi") === 0) {
            	$rm = array("remove_keyboard" => true);
        	}
        } elseif($inline) {
            $rm = array("inline_keyboard" => $menu);
        } else {
            $rm = array(
            "keyboard" => $menu,
            "resize_keyboard" => true,
            );
        }
        $rm = json_encode($rm);
    } elseif($forcereply) {
        $rm = array("force_reply" => true);
        $rm = json_encode($rm);
    } else $rm = false;


    // array principale delle imppostazioni
    $arg = array(
    "chat_id" => $chatID,
    "photo" => $photo,
    "caption" => $caption
    );
    if($disnoti) $arg["disable_notification"] = $disnoti;
    if($replyto) $arg["reply_to_message_id"] = $replyto;
    if($menu xor $forcereply) $arg["reply_markup"] = $rm;

    // dati della richiesta
    $url = "https://api.telegram.org/$api/sendPhoto";
    $args = json_encode($arg);

    // richiesta
    $response = http_post($url,$args); // http-post("url","argomenti passati per json_encode");
    $response = json_decode($response, true);

    // decodifica json risposta
    if(!$response["ok"]) {
        return array($response,$arg);   // response in caso di errore={"ok":false,"error_code":4xx,"description":"error description"}
    } else {
        return true;
    }
}


// sendVideo
function sv($chatID, $video, $thumb='', $menu=false, $inline=false, $replyto=false, $caption='', $forcereply=false, $disnotify=false, $pm='HTML') {
    global $api;

    // argomenti richiesta
    if($menu) {
        if(is_string($menu)) {
        	if(stripos($menu,"rimuovi") === 0) {
            	$rm = array("remove_keyboard" => true);
        	}
        } elseif($inline) {
            $rm = array("inline_keyboard" => $menu);
        } else {
            $rm = array(
            "keyboard" => $menu,
            "resize_keyboard" => true,
            );
        }
        $rm = json_encode($rm);
    } elseif($forcereply) {
        $rm = array("force_reply" => true);
        $rm = json_encode($rm);
    } else $rm = false;


    // array principale delle imppostazioni
    $arg = array(
    "chat_id" => $chatID,
    "video" => $video,
    "caption" => $caption
    );
    if($thumb != '') $arg["thumb"] = $thumb;
    if($disnoti) $arg["disable_notification"] = $disnoti;
    if($replyto) $arg["reply_to_message_id"] = $replyto;
    if($menu xor $forcereply) $arg["reply_markup"] = $rm;

    // dati della richiesta
    $url = "https://api.telegram.org/$api/sendVideo";
    $args = json_encode($arg);

    // richiesta
    $response = http_post($url,$args); // http-post("url","argomenti passati per json_encode");
    $response = json_decode($response, true);

    // decodifica json risposta
    if(!$response["ok"]) {
        return array($response,$arg);   // response in caso di errore={"ok":false,"error_code":4xx,"description":"error description"}
    } else {
        return true;
    }
}


// sendAnimation aka gif
function sa($chatID, $animation, $thumb='', $menu=false, $inline=false, $replyto=false, $caption='', $forcereply=false, $disnotify=false, $pm='HTML') {
    global $api;

    // argomenti richiesta
    if($menu) {
        if(is_string($menu)) {
        	if(stripos($menu,"rimuovi") === 0) {
            	$rm = array("remove_keyboard" => true);
        	}
        } elseif($inline) {
            $rm = array("inline_keyboard" => $menu);
        } else {
            $rm = array(
            "keyboard" => $menu,
            "resize_keyboard" => true,
            );
        }
        $rm = json_encode($rm);
    } elseif($forcereply) {
        $rm = array("force_reply" => true);
        $rm = json_encode($rm);
    } else $rm = false;


    // array principale delle impostazioni
    $arg = array(
    "chat_id" => $chatID,
    "animation" => $animation,
    "caption" => $caption
    );
    if($thumb != '') $arg["thumb"] = $thumb;
    if($disnoti) $arg["disable_notification"] = $disnoti;
    if($replyto) $arg["reply_to_message_id"] = $replyto;
    if($menu xor $forcereply) $arg["reply_markup"] = $rm;

    // dati della richiesta
    $url = "https://api.telegram.org/$api/sendAnimation";
    $args = json_encode($arg);

    // richiesta
    $response = http_post($url,$args); // http-post("url","argomenti passati per json_encode");
    $response = json_decode($response, true);

    // decodifica json risposta
    if(!$response["ok"]) {
        return array($response,$arg);   // response in caso di errore={"ok":false,"error_code":4xx,"description":"error description"}
    } else {
        return true;
    }
}

// editMessageText
function etext($text,$menu,$chatID=false,$msgID=false,$imsgID=false,$disprev=false,$pm="HTML") {
    global $api;

    // argomenti richiesta
    if($menu) {
        $rm = array("inline_keyboard" => $menu);
        $rm = json_encode($rm);
    }

    // array principale delle imostazioni
    $arg = array(
    "text" => $text,
    "parse_mode" => $pm
    );
    if($disprev) $arg["disable_web_page_preview"] = $disprev;
    if($chatID && $msgID) {
        $arg["chat_id"] = $chatID;
        $arg["message_id"] = $msgID;
    } elseif($imsg) {
        $arg["inline_message_id"] = $imsgID;
    } else return false;
    if($menu) $arg["reply_markup"] = $rm;

    // dati della richiesta
    $url = "https://api.telegram.org/$api/editMessageText";
    $args = json_encode($arg);

    // richiesta
    $response = http_post($url,$args); // http-post("url","argomenti passati per json_encode");
    $response = json_decode($response, true);

    // decodifica json risposta
    if(!$response["ok"]) {
        return array($response,$arg);   // response in caso di errore={"ok":false,"error_code":4xx,"description":"error description"}
    } else {
        return true;
    }
}
/*
// editMessageCaption
function ecapt() {

}
*/

// editMessageReplyMarkup
function eimenu($menu,$chatID=false,$msgID=false,$imsgID=false) {
    global $api;

    $rm = array("inline_keyboard" => $menu);
    $rm = json_encode($rm);
    $arg["reply_markup"] = $rm;

    // argomenti richiesta
    if($chatID && $msgID) {
        $arg["chat_id"] = $chatID;
        $arg["message_id"] = $msgID;
    } elseif($imsg) {
        $arg["inline_message_id"] = $imsgID;
    } else return false;

    // dati della richiesta
    $url = "https://api.telegram.org/$api/editMessageReplyMarkup";
    $args = json_encode($arg);

    // richiesta
    $response = http_post($url,$args); // http-post("url","argomenti passati per json_encode");
    $response = json_decode($response, true);

    // decodifica json risposta
    if(!$response["ok"]) {
        return array($response,$arg);   // response in caso di errore={"ok":false,"error_code":4xx,"description":"error description"}
    } else {
        return true;
    }
}


// answerInlineQuery
function ai($id,$result,$time=30,$personal=false,$next_offset="",$pm_text="",$pm_param="") {
    global $api;

    $results = json_encode($result);

    $arg = array(
        "inline_query_id" => $id,
        "results" => $results
    );
    if($time != 0) $arg["cache_time"]=$time;
    if($personal) $arg["is_personal"]=$personal;
    if($next_offset != "") $arg["next_offset"]=$next_offset;
    if($pm_text != "") $arg["switch_pm_text"]=$pm_text;
    if($pm_param != "") $arg["switch_pm_parameter"]=$pm_param;

    // dati della richiesta
    $url = "https://api.telegram.org/$api/answerInlineQuery";
    $args = json_encode($arg);
    // richiesta
    $response = http_post($url,$args);  // http-post("url","argomenti passati per json_encode");
    $response = json_decode($response, true);

    // decodifica json risposta
    if(!$response["ok"]) {
        return array($response,$arg);   // response in caso di errore={"ok":false,"error_code":4xx,"description":"error description"}
    } else {
        return true;
    }
}


// deleteMessage
function dm($chat_id,$message_id) {
    global $api;
    $arg = array(
        "chat_id" => $chat_id,
        "message_id" => $message_id
    );

    // dati della richiesta
    $url = "https://api.telegram.org/$api/deleteMessage";
    $args = json_encode($arg);
    // richiesta
    $response = http_post($url,$args);  // http-post("url","argomenti passati per json_encode");
    $responses = json_decode($response, true);

    // decodifica json risposta
    if(!$responses["ok"]) {
        return array($response,$arg);   // response in caso di errore={"ok":false,"error_code":4xx,"description":"error description"}
    } else {
        return true;
    }
}


?>
