<?php
return array(
    'uri' => 'http://localhost/testapi',
    'header' => array(
        'Content-Type' => 'application/json'
    ),
    'method' => 'POST',
    'body' => json_encode(array(
        'test' => array(
            'passwordCredentials' => array(
                'username' => $params['foo'],
                'password' => $params['bar']
            )
        )
    )),
    'response' => array(
        'valid_codes' => array('200')
    )
);

