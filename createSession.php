<?php
$DEBUG = '';
require_once '_sessions.php';
$msisdn = 0;
$errorMessage = '';

//print_r($_SESSION);
if (isset($_SESSION['user_profileID'])) {
    @session_unset();
    @session_destroy();
    session_regenerate_id(true);
}

if (isset($_POST['cmd_Login'])) {
    $msisdn = $_POST['tx_msisdn'];
    $password = $_POST['tx_password'];
    $network = 0 + $_POST['sl_network'];

    // testing get network
    //print_r(get_network($msisdn));
    //print_r($_POST);
    $valid_msisdn = isValidMobileNo($msisdn);

    $errorMessage = "Ok: MSISDN provided '$valid_msisdn'";
    if ($valid_msisdn != 0) {
        connectDB();
        $profiles_data = ORM::for_table('profiles')->where('MSISDN',$valid_msisdn)->find_one();
        $query = "select * from profiles where MSISDN = '$valid_msisdn'";
        //$profiles_data = $sqlite_connection->querySingle($query, true);
        //$result = selectSQL($query);
        //$profiles_data = mysql_fetch_assoc($result);
        //echo'<pre>';
        //print_r($profiles_data); die;

        if (isset($profiles_data['profile_id'])) {
            if ($profiles_data['password'] == $password or $profiles_data['password'] == null) {
                $subQuery = "";
                $_SESSION['user_profileID'] = $profiles_data['profile_id'];
                $_SESSION['user_MSISDN'] = $profiles_data['MSISDN'];
                $_SESSION['user_names'] = $profiles_data['names'];
                $_SESSION['user_network'] = $profiles_data['networkID'];
                
                // added logic to enable mobile number porting.
                if ($network != 0 and $network != $profiles_data['networkID']) {
                    $_SESSION['user_network'] = $network;
                    $subQuery = ", networkID='$network' ";
                }

                $DEBUG .= "Login as: " . print_r($profiles_data, true);
                $profile = ORM::for_table('profiles')->find_one($profiles_data['profile_id']);
                $profile->dateModified = date('Y-m-d H:i:s');
                $profile->save();
                //$query = "update profiles set dateModified = now() $subQuery where MSISDN = '$valid_msisdn'";
                //updateSQL($query);

                header("location:index.php");
                exit();
            } else {
                $DEBUG .= "Login failed wrong password: " . print_r($profiles_data, true);
                $errorMessage = "Error: Invalid Credentials for $valid_msisdn";
            }
        } elseif ($profiles_data == "true") { //never happens
            $DEBUG .= "Login failed Empty data: " . print_r($profiles_data, true);
            $errorMessage = "Error: No Credentials for $valid_msisdn";
        } else { // record does not exist
            $DEBUG .= "No profile data: " . print_r($profiles_data, true);

            $errorMessage = "Ok: Credentials for $valid_msisdn have been created";

            if ($password != '')
                $errorMessage .= " with password";

            if ($network == 0) {
                $network_data = get_network($valid_msisdn);
                $network = $network_data['networkID'];
            } else {
                $network_data = $network_list[$network];
            }

            //print_r($network_data);

            $query = "insert into profiles (MSISDN, password, names, network_id, profileStatus, dateCreated) values
			('$valid_msisdn', " . gSQLv($password) . ", 'new profile', " . gSQLv($network) . ", 1, now())";
            $profileID = insertSQL($query);

            $_SESSION['user_profileID'] = $profileID;
            $_SESSION['user_MSISDN'] = $valid_msisdn;
            $_SESSION['user_names'] = 'new profile';
            $_SESSION['user_network'] = $network;
            header("location:index.php");
            exit();
        }
    } else {
        $errorMessage = "Error: Invalid MSISDN provided $valid_msisdn";
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <title>USSD Emulator V2.0 July 2015</title>
        <style type="text/css">
            th {
                text-align: right;
            }
            input, option, select{
                padding:5px;

                font-weight: bold;
            }

        </style>
    </head>
    <body style="background:#111;">

        <div style="text-align:center; width:500px; background:#AAB; margin:auto; padding: 5px; border: 15px solid #FFFFFF;">
            <p>&nbsp;</p>
            <form action="createSession.php" method="post" name="action login">
                <pre style="text-align:center;color:#E00"><?php echo $errorMessage . "\n" . $DEBUG; ?></pre>
                <table border='0' align="center">
                    <tr>
                        <td colspan="2">
                            <h3>Please enter your Credentials</h3>
                        </td>
                    </tr>
                    <tr>
                        <th width='50%'>
                            Mobile Number:
                        </th>
                        <td>
                            <input type="text" name="tx_msisdn" value="<?php echo $msisdn; ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <th width='50%'>
                            Network:
                        </th>
                        <td>
                            <select name="sl_network">
                                <option value='63902' id='0' selected='selected'>Default</option>
<?php
foreach ($network_list as $networkName => $network_data) {
    echo " <option value='{$network_data['networkID']}'>{$network_data['networkName']}</option> ";
}
?></select>
                        </td>
                    </tr>
                    <tr>
                        <th width='50%'>
                            Password:
                        </th>
                        <td>
                            <input type="password" name="tx_password" value=""/>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align:right;">
                            <input type="submit" name="cmd_Login" value="Login / Register"/>
                        </td>
                    </tr>
                </table>
                <p>NB! Account will be created if it does not already exist</p>
                <p><?php
                    $query = "select DATE_FORMAT(NOW(),'%b %d %Y %h:%i %p') as today;";
                    $now_data = mysql_fetch_assoc(selectSQL($query));
                    if (isset($now_data['today'])) {
                        echo $now_data['today'];
                    } else {
                        echo "<span style='color:#990000'>Your working offline DB error:" . mysql_errno() . "</span>";
                    }
                    ?>&nbsp;</p>
                <p style="color:#DDD;">Samsung simulator 2.0.7264</p>
            </form>
        </div>
    </body>
</html>
<?php print_r($_SESSION); ?>