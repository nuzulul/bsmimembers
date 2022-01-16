<?php
///////////////////////////////////////header///////////////////////////////////////////////////////////
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://wiki.bsmijatim.org/library/bootstrap/bootstrap-5.1.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://wiki.bsmijatim.org/library/bootstrap/bootstrap-icons-1.7.2/bootstrap-icons.css">
    
    <script src="https://wiki.bsmijatim.org/library/jquery/jquery-3.6.0.min.js"></script>

    <title>BSMI Members</title>
  </head>
  <body>
<?php
///////////////////////////////////////header///////////////////////////////////////////////////////////




///////////////////////////////////////session///////////////////////////////////////////////////////////
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
///////////////////////////////////////session///////////////////////////////////////////////////////////



///////////////////////////////////////validate class///////////////////////////////////////////////////////////
class  Input {
	static $errors = true;

	static function check($arr, $on = false) {
		if ($on === false) {
			$on = $_REQUEST;
		}
		foreach ($arr as $value) {	
			if (empty($on[$value])) {
				self::throwError('Data is missing', 900);
			}
		}
	}

	static function int($val) {
		$val = filter_var($val, FILTER_VALIDATE_INT);
		if ($val === false) {
			self::throwError('Invalid Integer', 901);
		}
		return $val;
	}

	static function str($val) {
		if (!is_string($val)) {
			self::throwError('Invalid String', 902);
		}
		$val = trim(htmlspecialchars($val));
		return $val;
	}

	static function data($val) {
		if (!is_string($val)) {
			self::throwError('Invalid Data', 902);
		}

    $val = trim($val);
    $val = stripslashes($val);
    $val = strip_tags($val);
    $val = htmlspecialchars($val);		
		return $val;
	}

	static function bool($val) {
		$val = filter_var($val, FILTER_VALIDATE_BOOLEAN);
		return $val;
	}

	static function email($val) {
		$val = filter_var($val, FILTER_VALIDATE_EMAIL);
		if ($val === false) {
			self::throwError('Invalid Email', 903);
		}
		return $val;
	}

	static function url($val) {
		$val = filter_var($val, FILTER_VALIDATE_URL);
		if ($val === false) {
			self::throwError('Invalid URL', 904);
		}
		return $val;
	}

	static function throwError($error = 'Error In Processing', $errorCode = 0) {
		if (self::$errors === true) {
			throw new Exception($error, $errorCode);
		}
	}
}

Input::$errors = false;
///////////////////////////////////////validate class///////////////////////////////////////////////////////////


///////////////////////////////////////api///////////////////////////////////////////////////////////
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
///////////////////////////////////////api///////////////////////////////////////////////////////////




///////////////////////////////////////message///////////////////////////////////////////////////////////

function showmessage($data){

echo '
<div class="position-relative"><div class="container position-absolute top-0 start-50 translate-middle-x">
<div class="alert alert-danger d-flex align-items-center alert-dismissible fade show" role="alert">
  <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#bi-exclamation-triangle-fill"/></svg>
  <div>
    '.$data.'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  
</div>
</div></div>
';

}

///////////////////////////////////////message///////////////////////////////////////////////////////////




///////////////////////////////////////request///////////////////////////////////////////////////////////
if(isset($_POST['action'])){
  $action = isset($_POST['action'])?$_POST['action']:false;
  if ($action === "login"){
    
 
    
    //validate
    Input::check(['email', 'password'], $_POST);
    $post_email = Input::email($_POST['email']);
    $post_password = Input::str($_POST['password']);    
    if (!$post_email){showmessage('Email tidak valid');} 
    elseif (!$post_password){showmessage('Password tidak valid');}
    
    //checks if the username or password fields are empty
    elseif (strlen($post_email) === 0) {
        showmessage('Email tidak boleh kosong');
    }
    elseif (strlen($post_password) === 0) {
        showmessage('Password tidak boleh kosong');
    }
    else {
      $apiurl = "/records/users?filter=email,eq,".$post_email;
      $data = dbapi("read",$apiurl);$data = json_decode(dbapi("read",$apiurl));
      //var_dump($data->records);
      if(empty($data->records)) {
        showmessage('Email atau password salah');
      }
      else
      {
        $password = $data->records[0]->password;
        if (!password_verify($post_password, $password)) {
          showmessage('Email atau password salah');
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

    //validate
    Input::check(['email', 'password'], $_POST);
    $post_email = Input::email($_POST['email']);
    $post_password = Input::str($_POST['password']);    
    if (!$post_email){showmessage('Email tidak valid');} 
    elseif (!$post_password){showmessage('Password tidak valid');}
    
    //checks if the username or password fields are empty
    elseif (strlen($post_email) === 0) {
        showmessage('Email tidak boleh kosong');
    }
    elseif (strlen($post_password) === 0) {
        showmessage('Password tidak boleh kosong');
    }
    else {
    
      $apiurl = "/records/users?filter=email,eq,".$post_email;
      $data = dbapi("read",$apiurl);$data = json_decode(dbapi("read",$apiurl));
      if(!empty($data->records)) {
        showmessage('Email sudah terdaftar');
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
        if (strlen($data) === 0){showmessage('Registrasi gagal');}
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
    showmessage('Anda telah keluar');
    header('Location: index.php');
    exit;
  }
}

///////////////////////////////////////request///////////////////////////////////////////////////////////



///////////////////////////////////////main///////////////////////////////////////////////////////////
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
  echo 'BSMI MEMBERS';
  echo '<form action="index.php" method="post" autocomplete="off"><input type="hidden" name="action" value="logout" required=""><input type="submit" value="Logout"></form>';
}
///////////////////////////////////////main///////////////////////////////////////////////////////////






///////////////////////////////////////front///////////////////////////////////////////////////////////
else
{
?>
<div class="container-fluid vh-100" style="background-color: #508bfc;"><div class="row d-flex justify-content-center align-items-center h-100"><div class="col-12 col-md-8 col-lg-6 col-xl-5"><div class="card shadow-2-strong" style="border-radius: 1rem;">

<!-- main front -->
<section id="mainfront">
<div class="container p-5">
<h1 class="h3 mb-3 font-weight-normal">Assalamualaikum</h1>
<a class="loginbutton" href="javascript:"><button class="btn btn-lg btn-primary btn-block">Masuk</button></a>
<a class="registerbutton" href="javascript:"><button class="btn btn-lg btn-primary btn-block">Daftar</button></a>
</div>
</section>
<!-- main front -->

<!-- login form -->
<section id="loginform" style="display: none;">
    <form action="" method="post" autocomplete="off" class="form-signin">
      <h1 class="h3 mb-3 font-weight-normal">Masuk</h1>
      <label for="inputEmail" class="sr-only">Alamat email</label>
      <input type="email" id="inputEmail1" class="form-control" name="email" placeholder="Email" autofocus="" required=""></br>
      <label for="inputPassword" class="sr-only">Password</label>
      <input type="password" id="inputPassword1" class="form-control" name="password" placeholder="Password" required=""></br>
      <input type="hidden" name="action" value="login" required="">
      <button class="btn btn-lg btn-primary btn-block" type="submit">Masuk</button>
      <p class="mt-3">Belum punya akun? <a class="registerbutton" href="javascript:" class="text-black-50 fw-bold">Daftar</a></p>
      <p class="mt-3"><a class="frontbutton" href="javascript:" class="text-black-50 fw-bold">Kembali</a></p>
    </form>
</section>
<!-- login form -->

<!-- register form -->
<section id="registerform" style="display: none;"> 
    <form action="" method="post" autocomplete="off" class="form-signin">
      <h1 class="h3 mb-3 font-weight-normal">Daftar</h1>
      <label for="inputEmail" class="sr-only">Alamat email</label>
      <input type="email" id="inputEmail2" class="form-control" name="email" placeholder="Email" autofocus="" required=""></br>
      <label for="inputPassword" class="sr-only">Password</label>
      <input type="password" id="inputPassword2" class="form-control" name="password" placeholder="Password" required=""></br>
      <input type="hidden" name="action" value="register" required="">
      <button class="btn btn-lg btn-primary btn-block" type="submit">Daftar</button>
      <p class="mt-3">Sudah punya akun? <a class="loginbutton" href="javascript:" class="text-black-50 fw-bold">Masuk</a></p>
      <p class="mt-3"><a class="frontbutton" href="javascript:" class="text-black-50 fw-bold">Kembali</a></p>
    </form>
</section>	
<!-- register form -->




</div></div></div></div>
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
//document.getElementById("registerbutton").onclick = function() { document.getElementById("loginform").style.display = "none";document.getElementById("registerform").style.display = "inline"; };
//document.getElementById("loginbutton").onclick = function() { document.getElementById("registerform").style.display = "none";document.getElementById("loginform").style.display = "inline"; };


$(document).ready(function() {
  $('.loginbutton').click(function() {
    $('#loginform').slideDown("slow");
    $('#mainfront').hide("slow");
    $('#registerform').hide("slow");
  });
  $('.registerbutton').click(function() {
    $('#registerform').slideDown("slow");
    $('#mainfront').hide("slow");
    $('#loginform').hide("slow");
  });
  $('.frontbutton').click(function() {
    $('#mainfront').slideDown("slow");
    $('#registerform').hide("slow");
    $('#loginform').hide("slow");
  });
});
</script>
<?php
}
///////////////////////////////////////front///////////////////////////////////////////////////////////


///////////////////////////////////////footer///////////////////////////////////////////////////////////
?>
<script src="https://wiki.bsmijatim.org/library/bootstrap/bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
<?php
///////////////////////////////////////main///////////////////////////////////////////////////////////
?>