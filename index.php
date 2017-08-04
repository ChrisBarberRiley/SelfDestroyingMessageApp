<?php

$title = null;
$message = null;
$email = null;
$errors = [];
$success = false;

$db = new PDO("mysql:host=127.0.0.1;dbname=messageApp;", "root", "");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if(!empty($_POST)){
  if(isset($_POST['title'])){ $title = htmlentities($_POST['title']); } else { $errors['title'] = 'Title is a required field'; }
  if(isset($_POST['message'])){ $message = htmlentities($_POST['message']); } else { $errors['message'] = 'Message is a required field'; }
  if(isset($_POST['email'])){ $email = htmlentities($_POST['email']); } else { $errors['email'] = 'Email is a required field'; }

  if(!count($errors))
  {
    $bytes  = random_bytes(10);
    $hash   = bin2hex($bytes);

    $stmt = $db->prepare("INSERT INTO messages (title, message, email, hash) VALUES (:title, :message, :email, :hash)");
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':message', $message);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':hash', $hash);
    $stmt = $stmt->execute();

    if ($stmt) $success = true;

  } else {
    print_r($errors);
  }
} elseif(isset($_GET['id'])){

      $hash = htmlentities($_GET['id']);

      $stmt = $db->prepare("SELECT title, message FROM messages WHERE hash = :hash LIMIT 1");
      $stmt->bindParam(':hash', $hash);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      $count = $stmt->rowCount();
      if($count){

        ($stmt = $db->prepare("DELETE FROM messages WHERE hash = ?"))->execute([$hash]);
        $deleted = $stmt->rowCount();

        $title = $result['title'];
        $message = $result['message'];
      } else {
        $title = 'Sorry :(';
        $message = 'It appears that id does not exist or has already been removed';
      }
}

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Messaging app</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  </head>
  <body>


    <div class='container' style='padding:50px;'>
      <div class="row">
        <?php if(isset($_GET['id'])){ ?>
            <h1><?=$title?></h1>
            <p class='lead'><?=$message?></p>
            <?php if(isset($deleted)) {?><small>This message has been removed from the system</small><?php } ?>
        <?php }else{ ?>
          <?php if(!$success){?>
          <form action='' method='POST'>
            <input type="text" class="form-control" name="title" placeholder='Message title' value='<?=$title?>'><br/>

            <textarea class="form-control" rows="3" placeholder='Enter your message' name='message'><?=$message?></textarea><br/>

            <div class="input-group">
              <span class="input-group-addon" id="sizing-addon2">@</span>
              <input name='email' type="text" class="form-control" placeholder="Email" value='<?=$email?>'>
            </div>

            <button class="btn btn-default" type="submit">Submit</button>
          </form>
          <?php } else { ?>
            <h1>Success</h1>
            <p>You can find your message on the link below.</p>
            <a href='/?id=<?=$hash;?>'> View your one time message message</a>
          <?php } ?>
        <?php } ?>
      </div>
    </div>

  </body>
</html>
