<?php
$maxlifetime = 600;
$secure = false; // if you only want to receive the cookie over HTTPS
$httponly = true; // prevent JavaScript access to session cookie
$samesite = 'strict'; //none lax strict

if(PHP_VERSION_ID < 70300) {
    session_set_cookie_params($maxlifetime, '/; samesite='.$samesite, $_SERVER['HTTP_HOST'], $secure, $httponly);
} else {
    session_set_cookie_params([
        'lifetime' => $maxlifetime,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
}
    
session_start();

function dbapi($method,$apiurl,$payload = ""){
  $now = date("Y-m-d-H-i-s");
  $apikey =  getenv('XAPIKEY');
  if ($method === "read"){
    $context = stream_context_create([
      "http" => [
          "method" => "GET",
          "header" => "X-API-Key: $apikey\r\n"
      ]
    ]);
  }
  if ($method === "create"){
    $context = stream_context_create([
      "http" => [
          "method" => "POST",
          "header" => "Content-Type: application/json; charset=utf-8\r\n".
            "X-API-Key: $apikey\r\n",
          'content' => $payload,
          'timeout' => 60
      ]
    ]);
  }  
  //$dburl='https://bsmi.sourceforge.io/phpcrudapi/api.php'.$apiurl.'?cache='. $now;
  $dburl='https://bsmi.sourceforge.io/phpcrudapi/api.php'.$apiurl;
  $result = file_get_contents($dburl, false, $context);
  return $result;
}


if(isset($_POST['action'])){
  $action = isset($_POST['action'])?$_POST['action']:false;
  if ($action === "login"){
  
    //grab the posted email and password
    $post_email = $_POST['email'];
    $post_password = $_POST['password'];
    
    //secure the email and password
    $post_email = stripslashes($post_email);
    $post_password = stripslashes($post_password);
    
    //checks if the username or password fields are empty
    if (strlen($post_email) === 0) {
        echo "<p>The email field cannot be empty!</p>";
        echo "<a href='index.php'>Return</a>";
    }
    elseif (strlen($post_password) === 0) {
        echo "<p>The password field cannot be empty!</p>";
        echo "<a href='index.php'>Return</a>";
    }
    else {
      $apiurl = "/records/users?filter=email,eq,".$post_email;
      $data = dbapi("read",$apiurl);$data = json_decode(dbapi("read",$apiurl));
      //var_dump($data->records);
      if(empty($data->records)) {
        echo "<p>Invalid email or password.</p>";
      }
      else
      {
        $password = $data->records[0]->password;
        if (!password_verify($post_password, $password)) {
          echo "<p>Invalid email or password.</p>";
        }
        else
        {
          //if (password_verify($post_password, $password)) {
          $_SESSION['email'] = $post_email;
          $_SESSION['loggedin'] = true;  
        }
      
      }
    }    
  }
  if ($action === "register"){
    //grab the posted email and password
    $post_email = $_POST['email'];
    $post_password = $_POST['password'];
    
    //secure the email and password
    $post_email = stripslashes($post_email);
    $post_password = stripslashes($post_password);
    
    //checks if the username or password fields are empty
    if (strlen($post_email) === 0) {
        echo "<p>The email field cannot be empty!</p>";
        echo "<a href='index.php'>Return</a>";
    }
    elseif (strlen($post_password) === 0) {
        echo "<p>The password field cannot be empty!</p>";
        echo "<a href='index.php'>Return</a>";
    }
    else {
    
      $apiurl = "/records/users?filter=email,eq,".$post_email;
      $data = dbapi("read",$apiurl);$data = json_decode(dbapi("read",$apiurl));
      if(!empty($data->records)) {
        echo "<p>Error! Email already registered.</p>";
      }
      else
      {
        $apiurl = "/records/users";
        $fields = array(
            'email' => $post_email,
            'password' => password_hash($post_password, PASSWORD_DEFAULT),
            'username' => date("YmdHis"),
            'name' => "",
        );
        $payload = json_encode($fields);
        $data = dbapi("create",$apiurl,$payload);
        if (strlen($data) === 0){echo "<p>Register failed.</p>";}
        else{
          //echo "<p>Register success. Please login.</p>";  
          $_SESSION['email'] = $post_email;
          $_SESSION['loggedin'] = true;          
        }
      }    
    }  
  }
  if ($action === "logout"){
    $_SESSION['loggedin'] == false;
    session_destroy();
    echo "You are logged out.<br>";
    header('Location: index.php');
    exit;
  }
}


if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
  echo 'BSMI MEMBERS';
  echo '<form action="index.php" method="post" autocomplete="off"><input type="hidden" name="action" value="logout" required=""><input type="submit" value="Logout"></form>';
}
else
{
?>

<fieldset id="loginform" style="display: inline;">
  <legend>Login</legend>
    <form action="index.php" method="post" autocomplete="off">
      <input type="email" name="email" placeholder="Email" autofocus="" required=""></br>
      <input type="password" name="password" placeholder="Password" required=""></br>
      <input type="hidden" name="action" value="login" required="">
      <input type="submit">
    </form>
    <p>Not registred?</p>
    <a id="registerbutton" href="javascript:">Register</a>			
</fieldset>
<fieldset id="registerform" style="display: none;">
  <legend>Register</legend>
    <form action="index.php" method="post" autocomplete="off">
      <input type="email" name="email" placeholder="Email" autofocus="" required=""></br>
      <input type="password" name="password" placeholder="Password" required=""></br>
      <input type="hidden" name="action" value="register" required="">
      <input type="submit">
    </form>
    <p>Already registred?</p>
    <a id="loginbutton" href="javascript:">Login</a>	
</fieldset>	
<script>
document.getElementById("registerbutton").onclick = function() { document.getElementById("loginform").style.display = "none";document.getElementById("registerform").style.display = "inline"; };
document.getElementById("loginbutton").onclick = function() { document.getElementById("registerform").style.display = "none";document.getElementById("loginform").style.display = "inline"; };
</script>
<?php
}
?>