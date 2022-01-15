<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://wiki.bsmijatim.org/library/bootstrap/bootstrap-5.1.3-dist/css/bootstrap.min.css" rel="stylesheet">

    <title>BSMI Members</title>
  </head>
  <body>
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

<div class="container-fluid vh-100" style="background-color: #508bfc;"><div class="row d-flex justify-content-center align-items-center h-100"><div class="col-12 col-md-8 col-lg-6 col-xl-5"><div class="card shadow-2-strong" style="border-radius: 1rem;">

<!-- login form -->
<section id="loginform" style="display: inline;">

    <form action="" method="post" autocomplete="off" class="form-signin">
      <h1 class="h3 mb-3 font-weight-normal">Please sign in</h1>
      <label for="inputEmail" class="sr-only">Email address</label>
      <input type="email" id="inputEmail1" class="form-control" name="email" placeholder="Email" autofocus="" required=""></br>
      <label for="inputPassword" class="sr-only">Password</label>
      <input type="password" id="inputPassword1" class="form-control" name="password" placeholder="Password" required=""></br>
      <input type="hidden" name="action" value="login" required="">
      <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
      <p class="mt-3">Don't have an account? <a id="registerbutton" href="javascript:" class="text-black-50 fw-bold">Sign Up</a></p>
    </form>
    
   	
</section>

<!-- login form -->

<!-- register form -->
<section id="registerform" style="display: none;">

    
    <form action="" method="post" autocomplete="off" class="form-signin">
      <h1 class="h3 mb-3 font-weight-normal">Please sign up</h1>
      <label for="inputEmail" class="sr-only">Email address</label>
      <input type="email" id="inputEmail2" class="form-control" name="email" placeholder="Email" autofocus="" required=""></br>
      <label for="inputPassword" class="sr-only">Password</label>
      <input type="password" id="inputPassword2" class="form-control" name="password" placeholder="Password" required=""></br>
      <input type="hidden" name="action" value="register" required="">
      <button class="btn btn-lg btn-primary btn-block" type="submit">Sign up</button>
      <p class="mt-3">Have an account? <a id="loginbutton" href="javascript:" class="text-black-50 fw-bold">Sign in</a></p>
    </form>
        
    
</section>	
<!-- register form -->

</div></div></div></div>
<?php
}
?>
<style>
.form-signin {
  width: 100%;
  max-width: 330px;
  padding: 15px;
  margin: 0 auto;
}
.form-signin .checkbox {
  font-weight: 400;
}
.form-signin .form-control {
  position: relative;
  box-sizing: border-box;
  height: auto;
  padding: 10px;
  font-size: 16px;
}
.form-signin .form-control:focus {
  z-index: 2;
}
.form-signin input[type="email"] {
  margin-bottom: -1px;
  border-bottom-right-radius: 0;
  border-bottom-left-radius: 0;
}
.form-signin input[type="password"] {
  margin-bottom: 10px;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}
</style>
<script>
document.getElementById("registerbutton").onclick = function() { document.getElementById("loginform").style.display = "none";document.getElementById("registerform").style.display = "inline"; };
document.getElementById("loginbutton").onclick = function() { document.getElementById("registerform").style.display = "none";document.getElementById("loginform").style.display = "inline"; };
</script>
<script src="https://wiki.bsmijatim.org/library/jquery/jquery-3.6.0.min.js"></script>
<script src="https://wiki.bsmijatim.org/library/bootstrap/bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>