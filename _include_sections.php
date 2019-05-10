<?php

function render_homePage() {
    return "
<div id='home-screen' style='margin:0px; padding:0px; background-image: url(images/Samsung-home-screen-edited.png);' >
	<div id='home-spacer' style='width:100%; height:350px;'>&nbsp;</div>
	<div id='home-icons' style='width:100%; height:56px; padding:0px; margin:0px;'>
		<div class='screen-icons' style='margin-left:3px'>
			<input name='cmd_Call' type='image' src='images/blank.gif' style='height: 55px; width: 52px;' onclick='return go_dialer()' />
		</div>
		<div class='screen-icons' style=''>
			<input name='cmd_CallLog' type='image' src='images/blank.gif' style='height: 55px; width: 52px;' onclick='return go_calllog()' />
		</div>
		<div class='screen-icons' style=''>
			<input name='cmd_browser' type='image' src='images/blank.gif' style='height: 55px; width: 50px;' onclick='return go_browser()' />
		</div>
		<div class='screen-icons' style=''>
			<input name='cmd_SMS' type='image' src='images/blank.gif' style='height: 55px; width: 50px;' onclick='return go_messaging()' />
		</div>
		<div class='screen-icons' style=''>
			<input name='cmd_other' type='image' src='images/blank.gif' style='height: 55px; width: 50px;' onclick='return go_newmenus()' />
		</div>
	</div>
</div>";
}

function render_dialPage($dialNumber) {

    return "
    
		<div id='dial-pad-screen' style='margin:0px; padding:0px; background-image: url(images/android-dial-pad.png);' >
	<div id='dial-pad-options' style='width:100%; height:50px;'>
		<div class='screen-icons' style=''>
			<input name='cmd_dial-pad' type='image' src='images/blank.gif' style='height: 50px; width: 65px;' onclick='return go_dialer();' />
		</div>
		<div class='screen-icons' style=''>
			<input name='cmd_call-log' type='image' src='images/blank.gif' style='height: 50px; width: 65px;' onclick='return go_calllog();' />
		</div>
	</div>
	<div id='caller-text-box' style='width:100%; height:55px;'>
        
		<input id='txt_number' name='txt_number' type='text' class='typeNumber' value='$dialNumber' maxlength='32' style='text-align: center; font-size: 32px; height: 100%; width: 100%;background:none; color:#eee;' onkeypress='postAClick(event, \"cmd_Call\")' />
	</div>
	<div id='caller-dial-pad' style='width:100%; height:300px; padding:0px; margin:0px; margin-top:10px;'>
		<div class='dial-buttons'>
			<input name='cmd_no1' type='image' src='images/blank.gif' style='height: 50px; width: 77px;' onclick='return document.getElementById(\"txt_number\").value +=\"1\"' alt='1' />
		</div>
		<div class='dial-buttons'>
			<input name='cmd_no2' type='image' src='images/blank.gif' style='height: 50px; width: 77px;' onclick='return document.getElementById(\"txt_number\").value +=\"2\"' alt='2' />
		</div>
		<div class='dial-buttons'>
			<input name='cmd_no3' type='image' src='images/blank.gif' style='height: 50px; width: 77px;' onclick='return document.getElementById(\"txt_number\").value +=\"3\"' alt='3' />
		</div>
		<div class='dial-buttons'>
			<input name='cmd_no4' type='image' src='images/blank.gif' style='height: 50px; width: 77px;' onclick='return document.getElementById(\"txt_number\").value +=\"4\"' alt='4' />
		</div>
		<div class='dial-buttons'>
			<input name='cmd_no5' type='image' src='images/blank.gif' style='height: 50px; width: 77px;' onclick='return document.getElementById(\"txt_number\").value +=\"5\"' alt='5' />
		</div>
		<div class='dial-buttons'>
			<input name='cmd_no6' type='image' src='images/blank.gif' style='height: 50px; width: 77px;' onclick='return document.getElementById(\"txt_number\").value +=\"6\"' alt='6' />
		</div>
		<div class='dial-buttons'>
			<input name='cmd_no7' type='image' src='images/blank.gif' style='height: 50px; width: 77px;' onclick='return document.getElementById(\"txt_number\").value +=\"7\"' alt='7' />
		</div>
		<div class='dial-buttons'>
			<input name='cmd_no8' type='image' src='images/blank.gif' style='height: 50px; width: 77px;' onclick='return document.getElementById(\"txt_number\").value +=\"8\"' alt='8' />
		</div>
		<div class='dial-buttons'>
			<input name='cmd_no9' type='image' src='images/blank.gif' style='height: 50px; width: 77px;' onclick='return document.getElementById(\"txt_number\").value +=\"9\"' alt='9' />
		</div>
		<div class='dial-buttons'>
			<input name='cmd_star' type='image' src='images/blank.gif' style='height: 50px; width: 77px;' onclick='return document.getElementById(\"txt_number\").value +=\"*\"' alt='*' />
		</div>
		<div class='dial-buttons'>
			<input name='cmd_no0' type='image' src='images/blank.gif' style='height: 50px; width: 77px;' onclick='return document.getElementById(\"txt_number\").value +=\"0\"' alt='0' />
		</div>
		<div class='dial-buttons'>
			<input name='cmd_hash' type='image' src='images/blank.gif' style='height: 50px; width: 77px;' onclick='return document.getElementById(\"txt_number\").value +=\"#\"' alt='#' />
		</div>
		<div class='dial-buttons' style='margin: 1px; margin-top:9px; margin-left:20px;'>
			<input name='cmd_Back' type='image' src='images/blank.gif' style='height: 50px; width: 63px;' onclick='return go_home()' />
		</div>
		<div class='dial-buttons' style='margin: 1px; margin-top:9px'>
			<input name='cmd_Call' id ='cmd_Call' type='image' src='images/blank.gif' style='height: 50px; width: 95px;' onclick='return do_call(document.getElementById(\"txt_number\"));' />
		</div>
		<div class='dial-buttons' style='margin: 1px; margin-top:9px'>
			<input name='cmd_backspace' type='image' src='images/blank.gif' style='height: 50px; width: 62px;' onclick='return document.getElementById(\"txt_number\").value = backspace(document.getElementById(\"txt_number\").value);' />
		</div>
	</div>
</div>";
}

function render_callLogPage() {
    $result = selectSQL("select * from call_logs where profile_id = " . gSQLv($_SESSION['user_profileID']) . " order by dateModified desc limit 20");
    $log_data = mysql_fetch_assoc($result);

    $callLog = null;
    do {
        $callLog[] = "
		<a href='#' onclick='document.getElementById(\"txt_number\").value=\"{$log_data['dialNumber']}\"; return do_call(document.getElementById(\"txt_number\"));'>
		<table width='99%' border='0' cellpadding='0' cellmargin='0' style='border-bottom:solid 1px #999;background:#222;'>
			<tr>
				<td rowspan='2' width='20px'><div style='padding-top:3px;'><img src='images/android-outgoing-call.png' width='20px' height='20px' alt='Outgoing' /></div></td>
				<td><div style='text-align:left;font-size:20px;color:#EEE;'>{$log_data['dialNumber']}</div>
				
				</td>
				<td rowspan='2' width='20px'><div style=''><img src='images/android-call-icon.png' width='35' height='35' alt='Call' /></div></td>
			</tr>
			<tr>
				<td><div style='text-align:right;font-size:10px;color:#AAA;'>{$log_data['dateModified']}</div></div></td>
			</tr>
		</table></a>
		";
    } while ($log_data = mysql_fetch_assoc($result));

    $return = "
	<div id='call-log-screen' style='margin:0px; padding:0px; background-image: url(images/android-call-log.png);' >
	<div id='dial-pad-options' style='width:100%; height:50px;'>
		<div class='screen-icons' style=''>
			<input name='cmd_dial-pad' type='image' src='images/blank.gif' style='height: 50px; width: 65px;' onclick='return go_dialer();' />
		</div>
		<div class='screen-icons' style=''>
			<input name='cmd_call-log' type='image' src='images/blank.gif' style='height: 50px; width: 65px;' onclick='return go_calllog();' />
		</div>
	</div>
		<div id='contact-box' style='width:100%;height:40px;padding:2px;padding-top:5px;padding-bottom:9px;'>
		<input id='txt_number' name='txt_number' type='hidden' class='typeNumber' value='' />
	" . join("\n", $callLog) . "
		&nbsp;
	</div>
</div>";

    return $return;
}

function render_ussdSession($message, $dialNumber, $continue = false) {


    if ($continue) {
        $buttons = "
		<div id='ussd-buttons' style='width:100%;height:73px;padding:0px;padding-top:5px;text-align:center;'>
                    <input id='txt_reply' name='txt_reply' type='text' class='typeText' value='' maxlength='32' style='font-size:21px;height:27px; width: 230px;letter-spacing:1px;border:#FFF solid 1px;background:#EEE;color:#000;padding-left:5px;margin-bottom: 3px;' onkeypress='postAClick(event, \"cmd_reply\")' />
                    <input id='cmd_reply' name='cmd_reply' type='submit' value='Send' style='text-align:center;font-size: 12px;height:36px;width:49%;' onclick='return ussd_do_reply(document.getElementById(\"txt_reply\"))' />
                    <input id='cmd_cancel' name='cmd_cancel' type='button' value='Cancel' style='text-align:center;font-size:12px;height:36px;width: 49%' onclick='return ussd_do_exit()'/>
		</div>";
    } else {
        $buttons = "
		<div id='ussd-buttons' style='width:100%;height:73px;padding:0px;padding-top:5px;text-align:center;'>
                    <div style='font-size:21px;height:27px; width: 22px;margin-bottom:3px;'>&nbsp;</div>
                    <input id='cmd_exit' name='cmd_exit' type='button' value='Exit' style='text-align:center;font-size:12px;height:36px;width: 49%' onclick='return ussd_do_exit()'/>
		</div>
		";
    }
    $return = "
<div id='ussd-response-screen' style='width:100%;height:100%;margin:0px; padding:0px; background-image: url(images/ussd-session-screen.png);' >
	<div style='padding:2px;padding-bottom:5px;color:#EEF'>" . htmlentities($dialNumber) . "&nbsp;</div>
	<div id='ussd-response-box' style='margin:auto;width:94%;background:#1D2E3C;opacity:1;overflow:auto;border:#777 solid 1px;height:364px;letter-spacing:0.6px;word-spacing:1px;'>
		<div id='ussd-text' style='padding:5px;font-size:16px;overflow:auto;height:275px;color:#FFF;'>\n";
    if (strlen($message) >= 160) {
        $return .= str_replace("\n", '<br>', htmlentities(substr($message, 0, 160)));
        $return .= str_replace("\n", '<br>', "<span style='color:#C00;'>" . htmlentities(substr($message, 160)) . "</span>");
    } else {
        $return .= str_replace("\n", '<br>', htmlentities($message));
    }
    $return .= "\n</div>		
		$buttons
	</div>";
    if((strlen($message) >= 160)) {
        $return .= "\n<div style='text-align:center;width:100%;color:#C00;'>Len: " . number_format(strlen($message)) . " char (".number_format(strlen($message) - 160)." Over 160)</div>";
    } else {
        $return .= "\n<div style='text-align:center;width:100%;'>Len: " . number_format(strlen($message)) . " char</div>";
    }
    
    $return .= "\n</div> &nbsp;";
    return $return;
}

function render_browser($url) {
    $url = htmlentities($url);
    $return = "<div id='browser-screen' style='width:100%;height:100%;margin:0px; padding:0px; background:#fff;' >
          <div id='browser-bar' style='width:100%;height:32px;padding:3px;text-align:left;background-image:url(images/android-browser-address-bar.png)'>                                        
            <input id='txt_address' name='txt_reply' type='text' class='typeText' value='$url' maxlength='255' style='font-size:18px;height:24px;width:220px;background:#fff;font-weight:normal;' onreturn='return browser_location(document.getElementById(\"txt_address\").value);' />
            <input id='cmd_go' name='cmd_go' type='button' value='Go' style='text-align:center;font-size:18px;height:32px;width:35px;' onclick='return browser_location(document.getElementById(\"txt_address\").value);'/>                                                       
          </div>
			<iframe id='ibrowser' height='100%' width='100%' style='border:0px;background:#fff;' src='$url'></iframe>                                                                                                                                                                      
        </div>";

    return $return;
}

function render_messaging_home() {
        $result = selectSQL("select * from
                (select * from (select `profile_id`, `message_id` , `sourceaddr` , `destaddr` , `dateCreated` , `messageContent` , `messageType`, `messageRead` from messages where profile_id = " . gSQLv($_SESSION['user_profileID']) . " union
                select `profile_id`, `message_id`  , `destaddr` , `sourceaddr`, `dateCreated` , `messageContent` , `messageType`, `messageRead` from messages where destaddr = " . gSQLv($_SESSION['user_MSISDN']) . ")
                as zx order by dateCreated desc limit 20) as xz group by destaddr order by dateCreated asc");
        $message_data = mysql_fetch_assoc($result);

        $messages = $theMessage = null;
        do {
                if (strlen($message_data['messageContent']) > 55)
                {
                        $theMessage = htmlentities(substr($message_data['messageContent'], 0, 52) . '...');
                }
                else
                {
                        $theMessage = htmlentities($message_data['messageContent']);
                }

                $class = "sms-list-new";
                if ($message_data['messageRead'] == 1)
                {
                        $class = "sms-list-read";
                }

                $style = "style='border-right: #ccc solid 9px;'";
                if ($message_data['profile_id'] == $_SESSION['user_profileID'])
                {
                        $style = "style='border-left: #ccc solid 9px;'";
                }

                $messages[] = "
                <a onclick='return do_read_message({$message_data['message_id']})' title='Read Message'>
                        <div class='$class' $style>
                                <img src='images/contact-icon.png' alt='{$message_data['destaddr']}' style='float:left;width:32px;height:32px;' />
                                <div style='font-size:16px;font-weight:bold;float:left;width:200px;padding-left:5px;'>{$message_data['destaddr']}</div>
                                <div style='float:left;width:200px;padding-left:5px;'>{$message_data['dateCreated']}</div>
                                <div style='clear:both;width:100%;text-wrap:normal;overflow-wrap:break-word;'>$theMessage</div>
                        </div>
                </a>\n";
        }
        while ($message_data = mysql_fetch_assoc($result));

        $return = "<div id='sms-start-screen' style='width:100%;height:100%;margin:0px; padding:0px; background-image: url(images/android-sms-start-page.png);' >
        <div id='sms-title' style='padding:2px;padding-left:3px;height:18px;'>
                Messaging
        </div>
        <div id='sms-messages'>
                <a onclick='return do_new_message()' title='Compose New Message'>
                        <div class='sms-list-read' style='background:#FFF;'>
                        <div style='font-size:18px;font-weight:bold;'>New Message</div>
                        <div style='font-size:14px;font-weight:normal'>Compose New message</div>
                        </div>
                </a>
            <div id='sms-messages' style='overflow:auto;height:340px;width:100%'>
        " . join("\n",
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 $messages) . "
                </div>
        </div>";

        return $return;

}

function render_message_new() {
        $return = "<div id='sms-read-screen' style='width:100%;height:100%;margin:0px; padding:0px; background-image: url(images/android-sms-read-page.png);' >
  <div id='sms-title' style='padding:2px;padding-left:3px;height:18px;'>
    New Message
  </div>
  <div id='sms-contact' style='height:340px;width:100%;font-size:20px;font-weight:bold;color:#000'>To:
    <input id='txt_contact' name='txt_contact' type='text' class='typeText' value='' maxlength='32' style='font-size:16px;font-weight:normal;height:27px;width:210px;background:#fff;border: solid #FC0 3px;margin:3px;' />
  </div>
  <div id='sms-title' style='overflow:none;height:40px;width:100%;'>
    <textarea rows='1' id='txt_message' name='txt_message' type='text' class='typeText' value='' maxlength='306' style='font-size:16px;font-weight:bold;height:27px;width:200px;background:#fff;border: solid #FC0 3px;margin:3px;' /></textarea>
    <input id='cmd_send' name='cmd_send' type='submit' value='Send' style='float:right; text-align:center;font-size:12px;height:34px;width:50px;' onclick='return sms_do_send(document.getElementById(\"txt_contact\"), document.getElementById(\"txt_message\"));'/>
  </div>
</div>";

        return $return;

}



function render_message_read($messageID) {
        $contact = '5555';
        $result = selectSQL("select * from messages where message_id = " . gSQLv($messageID));
        $message_intro = mysql_fetch_assoc($result);
        if ($message_intro['sourceaddr'] == $_SESSION['user_MSISDN'])
        {
                // use profileId and destaddr
                $contact = $message_intro['destaddr'];
        }
        elseif ($message_intro['destaddr'] == $_SESSION['user_MSISDN'])
        {
                $contact = $message_intro['sourceaddr'];
        }

        $query = "select * from (select * from (select * from messages where profile_id = " . gSQLv($_SESSION['user_profileID']) . " and destaddr = '$contact' union
        select * from messages where sourceaddr = '$contact' and destaddr = " . gSQLv($_SESSION['user_MSISDN']) . ") as zx order by dateCreated desc limit 20) as xz order by dateCreated asc";

        $result = selectSQL($query);
        $message_data = mysql_fetch_assoc($result);

        $messages = $theMessage = null;
        do {

                $theMessage = htmlentities($message_data['messageContent']);

                if ($message_data['profile_id'] == $_SESSION['user_profileID'])
                {
                        // my message
                        $messages[] = "
                <div class='sms-list-read' style='border-right: #ccc solid 9px;'>
                        <img src='images/contact-icon.png' alt='{$message_data['destaddr']}' style='float:right;width:32px;height:32px;' />
                        <div style='font-size:16px;font-weight:bold;float:right;width:180px;padding-right:5px;text-align:right;'>Me.</div>
                        <div style='float:right;width:180px;text-align:right;'>{$message_data['dateCreated']}</div>
                        <div style='clear:both;width:100%;text-wrap:normal;overflow-wrap:break-word;'>$theMessage</div>
                </div>";
                }
                else
                {
                        // received message

                        $messages[] = "
                <div class='sms-list-read' style='border-left: #ccc solid 9px;'>
                        <img src='images/contact-icon.png' alt='{$message_data['sourceaddr']}' style='float:left;width:32px;height:32px;' />
                        <div style='font-size:16px;font-weight:bold;float:left;width:180px;padding-left:5px;'>{$message_data['sourceaddr']}</div>
                        <div style='float:left;width:180px;'>{$message_data['dateCreated']}</div>
                        <div style='clear:both;width:100%;text-wrap:normal;overflow-wrap:break-word;'>$theMessage</div>
                </div>";
                }
        }
        while ($message_data = mysql_fetch_assoc($result));


        $return = "<div id='sms-read-screen' style='width:100%;height:100%;margin:0px; padding:0px; background-image: url(images/android-sms-read-page.png);' >
    <div id='sms-title' style='padding:2px;padding-left:3px;height:18px;'>
            $contact
    </div>
    <div id='sms-messages' style='overflow:auto;height:340px;width:100%'>
        " . join("\n",
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 $messages) . "
    </div>
        <div id='sms-title' style='overflow:none;height:40px;width:100%;'>
                <input id='txt_contact' name='txt_contact' type='hidden' class='typeText' value='$contact' maxlength='32' />
                <textarea rows='1' id='txt_message' name='txt_message' type='text' class='typeText' value='' maxlength='306' style='font-size:16px;font-weight:bold;height:27px;width:200px;background:#fff;border: solid #FC0 3px;margin:3px;' /></textarea>
                <input id='cmd_send' name='cmd_send' type='submit' value='Send' style='float:right; text-align:center;font-size:12px;height:34px;width:50px;' onclick='return sms_do_send(document.getElementById(\"txt_contact\"), document.getElementById(\"txt_message\"));'/>
        </div>
</div>";

        $result = updateSQL("update messages set messageRead = 1 where message_id = " . gSQLv($messageID));
        return $return;

}

function render_profile_page($action) {
    $return = "<div id='profile-base' style='width:100%;height:100%;margin:0px; padding:0px; background-image: url(images/android-blank-screen-pattern.png);' >
  <div id='profile-title' style='padding:2px;padding-left:3px;height:18px;'>
    Profile
  </div>
  <div id='sms-messages'>
    <a href='samsung-sms-create.html' onclick='return null' title='Compose New Message'>
    <div class='sms-list' style='background:#FFF;'>
    <div style='font-size:18px;font-weight:bold;'>View Profile</div>
    <div style='font-size:14px;font-weight:normal'>View and update your proile</div>
  </div></a>
    <a href='samsung-sms-read.html' onclick='return null' title='Read Message'>
  <div class='sms-list'>
    <img src='images/contact-icon.png' alt='Contacts Full Names' style='float:left;' />
    <div style='font-size:19px;font-weight:bold;float:left;width:207px;padding-left:5px;'>Contact Names</div>
    <div style='float:left;width:160px'>short message from cont...</div>
    <div style='float:right;width:50px'>3:57pm</div>
    <div style='clear:both;'> </div>
  </div></a>
</div>
";
    return $return;
}


function render_emulator_menus($action) {
    $return = "<div id='profile-base' style='width:100%;height:100%;margin:0px; padding:0px; background-image: url(images/android-blank-screen-pattern.png);' >
  <div id='profile-title' style='padding:2px;padding-left:3px;height:18px;'>
    Menus
  </div>
  <div id='sms-messages'>
   <!-- <a href='samsung-sms-create.html' onclick='return null' title='Top up Airtime'>
    <div class='sms-list' style='background:#FFF;'>
    <div style='font-size:18px;font-weight:bold;'>View Profile</div>
    <div style='font-size:14px;font-weight:normal'>View and update your proile</div>
  </div>
  <a href='samsung-sms-read.html' onclick='return null' title='Pay Bills'></a>
  <div class='sms-list'>
    <img src='images/topup-image.png' width='46px' height='51px' alt='topup' style='float:left;' />
    <div style='font-size:19px;font-weight:bold;float:left;width:207px;padding-left:5px;'><a onclick='return go_topup()'> Top up Airtime</a></div>
    <div style='float:left;width:180px;padding-left:5px;'>Top-up own mobile Number...</div>
    <div style='clear:both;'> </div>
  </div>-->
  <a onclick='return go_checkBalance()'>
  <div class='sms-list'>
    <img src='images/cash-balance.png' width='46px' height='51px' alt='Check Balance' style='float:left;' />
    <div style='font-size:19px;font-weight:bold;float:left;width:207px;padding-left:5px;'> Check Balance</div>
    <div style='float:left;width:160px;padding-left:5px;'>Check money balance...</div>
    <div style='clear:both;'> </div>
  </div></a>
    <br>
  <a onclick='return go_paybill()'><div class='sms-list'>
    <img src='images/pay-bills.png' width='46px' height='51px' alt='Pay Bills' style='float:left;' />
    <div style='font-size:19px;font-weight:bold;float:left;width:207px;padding-left:5px;'> Pay Bills</div>
    <div style='float:left;width:160px;padding-left:5px;'>Pay bill...</div>
    <div style='clear:both;'> </div>
  </div></a>
  <!--<div class='sms-list'>
    <img src='images/fund-transfer-m-b.png' width='46px' height='51px'  alt='funds transfer' style='float:left;' />
    <div style='font-size:19px;font-weight:bold;float:left;width:207px;padding-left:5px;'><a onclick='return go_b2ctransfer()'> B2C Transfer</a></div>
    <div style='float:left;width:160px;padding-left:5px;'>Bank to Customer transfer...</div>
    <div style='clear:both;'> </div>
  </div>-->
</div>

";
    return $return;
}

function render_alerts($message, $error=false) {
    $return = "<div id='profile-base' style='width:100%;height:100%;margin:0px; padding:0px;padding-top: 40px; background-image: url(images/android-blank-screen-pattern.png);' >
  <div id='profile-title' style='padding:2px;padding-left:3px;height:18px;'>
  </div>
  	<div id='ussd-response-box' style='margin:auto;width:90%;background:#000;opacity:0.8;overflow:auto;border:#AAA solid 2px;'>
		<div id='ussd-text' style='padding:5px;font-size:16px;'>$message</div>
    </div>
    <br>
    <div id='ussd-buttons' style='width:100%;height:37px;padding:0px;text-align:center;'>";
            if ($error) {
            $return .= "<input id='cmd_exit' name='cmd_exit' type='button' value='Back' style='text-align:center;font-size:12px;height:36px;width: 49%' onclick='return go_paybill()'/>";
            } else {
             $return .= "<input id='cmd_exit' name='cmd_exit' type='button' value='Exit' style='text-align:center;font-size:12px;height:36px;width: 49%' onclick='return go_newmenus()'/>";
            }
    $return .= "
		</div>
</div>
";
    return $return;
}

function render_new_alerts($message, $error=false) {
    $return = "<div id='profile-base' style='width:100%;height:100%;margin:0px; padding:0px;padding-top: 40px; background-image: url(images/android-blank-screen-pattern.png);' >
  <div id='profile-title' style='padding:2px;padding-left:3px;height:18px;'>
  </div>
  	<div id='ussd-response-box' style='margin:auto;width:90%;background:#000;opacity:0.8;overflow:auto;border:#AAA solid 2px;'>
		<div id='ussd-text' style='padding:5px;font-size:16px;'>$message</div>
    </div>
    <br>
    <div id='ussd-buttons' style='width:100%;height:37px;padding:0px;text-align:center;'>";
            $return .= "<input id='cmd_exit' name='cmd_exit' type='button' value='Exit' style='text-align:center;font-size:12px;height:36px;width: 49%' onclick='return ussd_do_exit()'/>";
            $return .= "
		</div>
</div>
";
    return $return;
}

function render_get_payments($message, $dialNumber, $continue = false) {
    if ($continue) {
        $buttons = "

		<div id='ussd-buttons' style='width:100%;height:250px;padding:0px;padding-left:1px;text-align:left;background-image: url(images/android-blank-screen-pattern.png);'>
		    <label for='newlabel'>Enter Pay Bill Number</label>
            <input id='paybill_number' name='paybill_number' type='text' class='typeText' value='' maxlength='32' style='font-size:21px;height:27px; width: 230px;background:#fff;margin-bottom: 3px;' />
            <label for='newlabel'>Account Number</label>
            <input id='account_number' name='account_number' type='text' class='typeText' value='' maxlength='32' style='font-size:21px;height:27px; width: 230px;background:#fff;margin-bottom: 3px;' />
            <label for='amount'>Enter amount</label>
            <input id='bill_amount' name='amount' type='text' class='typeText' value='' maxlength='32' style='font-size:21px;height:27px; width: 230px;background:#fff;margin-bottom: 3px;' />
            <input id='cmd_reply' name='cmd_reply' type='submit' value='Send' style='text-align:center;font-size: 12px;height:36px;width:49%;' onclick='return ussd_do_post_BillRequest(document.getElementById(\"paybill_number\"), document.getElementById(\"bill_amount\"), document.getElementById(\"account_number\"))' />
            <input id='cmd_cancel' name='cmd_cancel' type='button' value='Cancel' style='text-align:center;font-size:12px;height:36px;width: 49%' onclick='return go_newmenus()'/>
		</div>";
    } else {
        $buttons = "
		<div id='ussd-buttons' style='width:100%;height:37px;padding:0px;text-align:center;'>
            <input id='cmd_exit' name='cmd_exit' type='button' value='Exit' style='text-align:center;font-size:12px;height:36px;width: 49%' onclick='return ussd_do_exit()'/>
		</div>
		";
    }
    $return = "
<div id='ussd-response-screen' style='width:100%;height:100%;margin:0px; padding:0px; background-image: url(images/ussd-session-screen.png);' >
	<div style='padding:0px;color:#AAA'>" . htmlentities($dialNumber) . "</div>&nbsp;
	<div id='ussd-response-box' style='margin:auto;width:90%;background:#000;opacity:0.8;overflow:auto;border:#AAA solid 2px;'>
		<div id='ussd-text' style='padding:5px;font-size:14px;'>\n";
    if (strlen($message) >= 160) {
        $return .= str_replace("\n", '<br>', htmlentities(substr($message, 0, 160)));
        $return .= str_replace("\n", '<br>', "<span style='color:#933;'>" . htmlentities(substr($message, 160)) . "</span>");
    } else {
        $return .= str_replace("\n", '<br>', htmlentities($message));
    }
    $return .= "
		</div>
		$buttons
	</div>
	<div style='text-align:center;width:100%;'>Len: " . strlen($message) . " char</div>
</div> &nbsp;";
    return $return;
}


function render_topup_airtime($message, $dialNumber =null, $continue = false) {
    if ($continue) {
        $buttons = "
		<div id='ussd-buttons' style='width:100%;height:200px;padding:0px;text-align:center;'>
            <label for='amount'>Enter amount</label>
            <input id='airtime_amount' name='amount' type='text' class='typeText' value='' maxlength='32' style='font-size:21px;height:27px; width: 230px;background:#fff;margin-bottom: 3px;' />
            <input id='cmd_reply' name='cmd_reply' type='submit' value='Send' style='text-align:center;font-size: 12px;height:36px;width:49%;' onclick='return ussd_do_post_airtime(document.getElementById(\"airtime_amount\"))' />
            <input id='cmd_cancel' name='cmd_cancel' type='button' value='Cancel' style='text-align:center;font-size:12px;height:36px;width: 49%' onclick='return ussd_do_exit()'/>
		</div>";
    } else {
        $buttons = "
		<div id='ussd-buttons' style='width:100%;height:37px;padding:0px;text-align:center;'>
            <input id='cmd_exit' name='cmd_exit' type='button' value='Exit' style='text-align:center;font-size:12px;height:36px;width: 49%' onclick='return ussd_do_exit()'/>
		</div>
		";
    }
    $return = "
<div id='ussd-response-screen' style='width:100%;height:100%;margin:0px; padding:0px; background-image: url(images/ussd-session-screen.png);' >
	<div style='padding:0px;color:#AAA'>" . htmlentities($dialNumber) . "</div>&nbsp;
	<div id='ussd-response-box' style='margin:auto;width:90%;background:#000;opacity:0.8;overflow:auto;border:#AAA solid 2px;'>
		<div id='ussd-text' style='padding:5px;font-size:14px;'>\n";
    if (strlen($message) >= 160) {
        $return .= str_replace("\n", '<br>', htmlentities(substr($message, 0, 160)));
        $return .= str_replace("\n", '<br>', "<span style='color:#933;'>" . htmlentities(substr($message, 160)) . "</span>");
    } else {
        $return .= str_replace("\n", '<br>', htmlentities($message));
    }
    $return .= "
		</div>
		$buttons
	</div>
	<div style='text-align:center;width:100%;'>Len: " . strlen($message) . " char</div>
</div> &nbsp;";
    return $return;
}

function render_b2ctransfer($message, $dialNumber =null, $continue = false) {
    if ($continue) {
        $buttons = "
		<div id='ussd-buttons' style='width:100%;height:200px;padding:0px;text-align:center;'>
            <label for='amount'>Enter amount</label>
            <input id='b2c_amount' name='amount' type='text' class='typeText' value='' maxlength='32' style='font-size:21px;height:27px; width: 230px;background:#fff;margin-bottom: 3px;' />
            <input id='cmd_reply' name='cmd_reply' type='submit' value='Send' style='text-align:center;font-size: 12px;height:36px;width:49%;' onclick='return ussd_do_post_b2c(document.getElementById(\"b2c_amount\"))' />
            <input id='cmd_cancel' name='cmd_cancel' type='button' value='Cancel' style='text-align:center;font-size:12px;height:36px;width: 49%' onclick='return ussd_do_exit()'/>
		</div>";
    } else {
        $buttons = "
		<div id='ussd-buttons' style='width:100%;height:37px;padding:0px;text-align:center;'>
            <input id='cmd_exit' name='cmd_exit' type='button' value='Exit' style='text-align:center;font-size:12px;height:36px;width: 49%' onclick='return ussd_do_exit()'/>
		</div>
		";
    }
    $return = "
<div id='ussd-response-screen' style='width:100%;height:100%;margin:0px; padding:0px; background-image: url(images/ussd-session-screen.png);' >
	<div style='padding:0px;color:#AAA'>" . htmlentities($dialNumber) . "</div>&nbsp;
	<div id='ussd-response-box' style='margin:auto;width:90%;background:#000;opacity:0.8;overflow:auto;border:#AAA solid 2px;'>
		<div id='ussd-text' style='padding:5px;font-size:14px;'>\n";
    if (strlen($message) >= 160) {
        $return .= str_replace("\n", '<br>', htmlentities(substr($message, 0, 160)));
        $return .= str_replace("\n", '<br>', "<span style='color:#933;'>" . htmlentities(substr($message, 160)) . "</span>");
    } else {
        $return .= str_replace("\n", '<br>', htmlentities($message));
    }
    $return .= "
		</div>
		$buttons
	</div>
	<div style='text-align:center;width:100%;'>Len: " . strlen($message) . " char</div>
</div> &nbsp;";
    return $return;
}
?>

