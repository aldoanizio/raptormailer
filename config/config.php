<?php

//--------------------------------------------
// Configuration - Used on \raptormailer\Mail
//--------------------------------------------

return
[
    /**
     * Default connection to use.
     */
    'default' => 'primary',

    /**
     * You may want to change the origin of the HELO request.
     * Setting default value as "localhost" may cause email to be considered spam.
     * http://stackoverflow.com/questions/5294478/significance-of-localhost-in-helo-localhost
     */
    'localhost' => '',

    /**
     * You can define as many mailer connections as you want.
     */
    'connections' =>
    [
        'primary' =>
        [
            'host'   => 'smtp.host.com',
            'port'   => '465',
            'secure' => 'tls', // null, 'ssl', or 'tls'
            'auth'   => true, // true if authorization required
            'user'   => 'user@email.com',
            'pass'   => 'user_pass',
            'reply'  => 'another_user@domaing.tld', // default replyto
            'from'   => 'no_reply@domain.tld', // default email from
            'sender' => 'My Company', // default email sender name
            'debug'  => false, // Debug connection
        ],
    ],
];

/** -------------------- End of file --------------------**/