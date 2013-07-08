<?php

$response = array(
		  'error' => array(
				   'code' => $code,
				   'messages' => $messages,
				   )
		  );

echo json_encode($response);