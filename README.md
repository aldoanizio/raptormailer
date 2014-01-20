#Raptor Mailer Package

##Description

Lightweight SMTP Mailer Class for Mako Framework 4.0 (www.makoframework.com).

##Methods

###factory([string $connection)

Factory method making method chaining possible right off the bat.

    $mail = Mail::factory('my_smtp_conn_settings'); // Set an alternative package config to use.

###from(string $email [, string $name)

Set email "From" address / name

    $mail->from('emailfrom@domain.tld', 'My Name From'); // No need to specify the "from" data if you want to use default data stored in config

###reply(string $email [, string $name)

Set email "Reply To" address / name

    $mail->reply('emailreplyto@domain.tld', 'My Name To Replay'); // No need to specify the "reply" data if you want to use default data stored in config

###to(string $email [, string $name)

Set email receiver addresses / names. You can define multiples receivers

    // Receiver 1
    $mail->to('receiver1@domain.tld', 'Receiver 1');

    // Receiver 2
    $mail->to('receiver2@domain.tld', 'Receiver 2');

###cc(string $email [, string $name)

Set email carbon copy addresses / names. You can define multiples receivers

    // Copy Receiver 1
    $mail->cc('carboncopy1@domain.tld', 'Copy Receiver 1');

    // Copy Receiver 2
    $mail->cc('carboncopy2@domain.tld', 'Copy Receiver 2');

###bcc(string $email [, string $name)

Set email blind carbon copy addresses / names. You can define multiples receivers

    // Blind Copy Receiver 1
    $mail->bcc('blindcarboncopy1@domain.tld', 'Blind Copy Receiver 1');

    // Blind Copy Receiver 2
    $mail->bcc('blindcarboncopy2@domain.tld', 'Blind Copy Receiver 2');

###html(string $html)

Set email body content in html format

    // Use string as html
    $mail->html('<html>My HTML Content</html>');

    // Use a variable containing html
    $mail->html($my_html_var);

###view($template [, array $data)

Set email body content in html format using mako templates

    // Example array
    $emailData =
    [
        "one_var"     => 'one value content',
        'another_var' => 'another value content'
    ];

    // Use variable
    $mail->view('my.mako.template.file', $myEmailData);

###text(string $text)

Set email body content in plain text format

    // Use string as plain text
    $mail->text('My Plain Text Content');

    // Use a variable containing plain text
    $mail->text($my_text_var);

###subject(string $subject)

Set email subject

    $mail->subject('Email Subject');

###attach(string $path)

Attach files to email

    // Rreal file path
    $mail->attach('real/path/to/file.ext');

###send(string $path)

Perform email Send

    // Rreal file path
    $mail->send();


##Examples

In the example below we send an message using an alternative package config with attachments.

    $mail = Mail::factory('my_smtp_conn_settings');

    $mail->to('email@domain.tld');

    $mail->subject('Recover My Pass');

    $mail->view('emails.user.login.forgot', ['email' => 'email@domain.tld', 'pass' => 'new_pass']);

    $mail->attach(MAKO_APPLICATION_PATH . '/storage/file.ext');

    $mail->send();

The mailer class also allows method chaining.

    Mail::factory('my_smtp_conn_settings');
    ->to('useremail@domain.tld')
    ->subject('Recover My Pass')
    ->view('emails.user.login.forgot', ['email' => 'email@domain.tld', 'pass' => 'new_pass'])
    ->attach(MAKO_APPLICATION_PATH . '/storage/file.ext')
    ->send();


## Credits

This class is based on Laravel SMTP class by swt83 - (https://github.com/swt83/php-laravel-smtp)