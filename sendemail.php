<?php

    session_cache_limiter( 'nocache' );
    header( 'Expires: ' . gmdate( 'r', 0 ) );
    header( 'Content-type: application/json' );


    $to         = 'kornosk130@gmail.com';  // Email here

    $email_template = 'simple.html';

    $subject    = strip_tags($_POST['subject']);
    $email      = strip_tags($_POST['email']);
    $name       = strip_tags($_POST['name']);
    $message    = nl2br( htmlspecialchars($_POST['message'], ENT_QUOTES) );
    $result     = array();


    if(empty($name)){

        $result = array( 'response' => 'error', 'empty'=>'name', 'message'=>'<strong>Error!</strong>&nbsp; Name is empty.' );
        echo json_encode($result );
        die;
    } 

    if(empty($email)){

        $result = array( 'response' => 'error', 'empty'=>'email', 'message'=>'<strong>Error!</strong>&nbsp; Email is empty.' );
        echo json_encode($result );
        die;
    } 

    if(empty($message)){

         $result = array( 'response' => 'error', 'empty'=>'message', 'message'=>'<strong>Error!</strong>&nbsp; Message body is empty.' );
         echo json_encode($result );
         die;
    }
    


    $headers  = "From: " . $name . ' <' . $email . '>' . "\r\n";
    $headers .= "Reply-To: ". $email . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";


    $templateTags =  array(
        '{{subject}}' => $subject,
        '{{email}}'=>$email,
        '{{message}}'=>$message,
        '{{name}}'=>$name
        );


    $templateContents = file_get_contents( dirname(__FILE__) . '/email-templates/'.$email_template);

    $contents =  strtr($templateContents, $templateTags);

    // sendGrid
    require 'assets/sendgrid-php/vendor/autoload.php';
    $sendgrid = new SendGrid("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");
    $gemail    = new SendGrid\Email();

    $gemail->addTo($to)
           ->setFrom($email)
           ->setSubject($subject)
           ->setHtml($contents);
          

    try {
        $sendgrid->send($gemail);

        $result = array( 'response' => 'success', 'message'=>'<strong>Thank You!</strong>&nbsp; Your email has been sent.' );
    } catch(\SendGrid\Exception $e) {
        echo json_encode(array('response' => 'Error'));
        $e->getCode();
        
        foreach($e->getErrors() as $er) {
            $result = array( 'response' => 'error', 'message'=>'<strong>Error!</strong>&nbsp;' . $er  );
        }
    }


    // Old method
    // if ( mail( $to, $subject, $contents, $headers ) ) {
    //     $result = array( 'response' => 'success', 'message'=>'<strong>Thank You!</strong>&nbsp; Your email has been delivered.' );
    // } else {
    //     $result = array( 'response' => 'error', 'message'=>'<strong>Error!</strong>&nbsp; Cann\'t Send Mail.'  );
    // }

    echo json_encode( $result );

    die;

?>