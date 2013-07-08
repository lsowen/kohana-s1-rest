<?php

$x = new SimpleXMLElement('<Response/>');

$xError = $x->addChild('error');

$xError->code = $code;

$xMessages = $xError->addChild('messages');
foreach($messages as $message)
  {
    $xMessages->addChild('message', $message);
  }


echo $x->asXML();