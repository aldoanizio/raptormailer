<?php
namespace RaptorMailer;

use \mako\core\Config;
use \mako\view\View;

/**
 * Lightweight SMTP Mailer Class
 *
 * @author     Aldo Anizio Lugão Camacho
 * @copyright  (c)2003 Aldo Anizio Lugão Camacho
 * @license    http://www.makoframework.com/license
 */
class Mail
{
    //---------------------------------------------
    // Class properties
    //---------------------------------------------

    /**
     * Connection Settings stored in config.php file
     *
     * @var array
     */
    protected $connection = [];

    /**
     * Server address used to send in HELO
     *
     * @var string
     */
    protected $localhost;

    /**
     * Timeout time
     *
     * @var int
     */
    protected $timeout = 30;

    /**
     * Set class as Debug mode
     *
     * @var boolean
     */
    protected $debug = false;

    /**
     * STMP server address
     *
     * @var string
     */
    protected $host;

    /**
     * STMP server port
     *
     * @var string
     */
    protected $port;

    /**
     * Use security in connection
     *
     * @var string
     */
    protected $secure; // null, 'ssl', or 'tls'

    /**
     * Set true if authorization required
     *
     * @var string
     */
    protected $auth;

    /**
     * STMP username auth
     *
     * @var string
     */
    protected $user;

    /**
     * STMP password auth
     *
     * @var string
     */
    protected $pass;

    /**
     * Email receiver addresses
     *
     * @var array
     */
    protected $to = [];

    /**
     * Email Carbon Copy addresses
     *
     * @var array
     */
    protected $cc = [];

    /**
     * Email Blind Carbon Copy addresses
     *
     * @var array
     */
    protected $bcc = [];

    /**
     * Email From address / name
     *
     * @var string
     */
    protected $from;

    /**
     * Set where to reply address / name
     *
     * @var string
     */
    protected $reply;

    /**
     * Message body content in HTML format
     *
     * @var string
     */
    protected $html = '';

    /**
     * Message body content in plain text format
     *
     * @var string
     */
    protected $text = '';

    /**
     * Email Subject
     *
     * @var string
     */
    protected $subject;

    /**
     * Email Attachments
     *
     * @var string
     */
    protected $attachments = [];

    /**
     * Set Message charset
     *
     * @var string
     */
    protected $charset = 'UTF-8';

    /**
     * Set Message line break format
     *
     * @var string
     */
    protected $newline = "\r\n";

    /**
     * Set Message encoding type
     *
     * @var string
     */
    protected $encoding = '8bit';

    /**
     * Set Message wordwrap length
     *
     * @var string
     */
    protected $wordwrap = 70;

    //---------------------------------------------
    // Class constructor, destructor etc ...
    //---------------------------------------------

    /**
     * Constructor.
     *
     * @access  public
     * @param   string  $connection  Set an alternative connection to use
     */
    public function __construct($connection = null)
    {
        // Package config
        $config = Config::get('raptormailer::config');

        // Load connection
        $connection = $connection ? $config['connections'][$connection] : $config['connections'][$config['default']];

        // set connection vars
        $this->host   = $connection['host'];
        $this->port   = $connection['port'];
        $this->secure = $connection['secure'];
        $this->auth   = $connection['auth'];
        $this->user   = $connection['user'];
        $this->pass   = $connection['pass'];
        $this->debug  = $connection['debug'];

        // Set default "from" address
        $this->from($connection['from'], $connection['sender']);

        // Set default "reply to" address
        $this->reply($connection['reply'], $connection['sender']);

        // Set localhost
        $this->localhost = $config['localhost'] ? $config['localhost'] : $connection['host'];
    }

    /**
     * Factory method making method chaining possible right off the bat.
     *
     * @access  public
     * @param   string             $connection  (optional) Set an alternative connection to use
     * @return  RaptorMailer\Mail
     * @static
     */
    public static function factory($connection = null)
    {
        return new static($connection);
    }

    //---------------------------------------------
    // Class methods
    //---------------------------------------------

    /**
     * Set email "From" address / name
     *
     * @access  public
     * @param   string             $email  A valid email address
     * @param   string             $name  (optional) Set a sender name
     * @return  RaptorMailer\Mail
     */
    public function from($email, $name = null)
    {
        // if not array...
        if (!is_array($email))
        {
            // Set normal
            $this->from =
            [
                'email' => $email,
                'name'  => $name
            ];
        }
        else
        {
            // Set convention
            $this->from =
            [
                'email' => isset($email[0]) ? $email[0] : null,
                'name'  => isset($email[1]) ? $email[1] : null
            ];
        }

        return $this;
    }

    /**
     * Set email "Reply To" address / name
     *
     * @access  public
     * @param   string             $email  A valid email address
     * @param   string             $name  (optional) Set a sender name
     * @return  RaptorMailer\Mail
     */
    public function reply($email, $name = null)
    {
        // if not array...
        if (!is_array($email))
        {
            // set normal
            $this->reply =
            [
                'email' => $email,
                'name'  => $name
            ];
        }
        else
        {
            // set convention
            $this->reply =
            [
                'email' => isset($email[0]) ? $email[0] : null,
                'name'  => isset($email[1]) ? $email[1] : null
            ];
        }

        return $this;
    }

    /**
     * Set email receiver addresses / names
     *
     * @access  public
     * @param   string             $email  A unique or array of valid emails addresses
     * @param   string             $name  (optional) Set a receiver name
     * @return  RaptorMailer\Mail
     */
    public function to($email, $name = null)
    {
        // if not array...
        if (!is_array($email))
        {
            // set normal
            $this->to[] =
            [
                'email' => $email,
                'name' => $name
            ];
        }
        else
        {
            // spin array...
            foreach ($email as $address)
            {
                // fix array
                if (!is_array($address))
                {
                    $address = array($address);
                }

                // set convention
                $this->to[] =
                [
                    'email' => isset($address[0]) ? $address[0] : null,
                    'name'  => isset($address[1]) ? $address[1] : null
                ];
            }
        }

        return $this;
    }

    /**
     * Set email carbon copy addresses / names
     *
     * @access  public
     * @param   string             $email  A unique or array of valid emails addresses
     * @param   string             $name  (optional) Set a receiver name
     * @return  RaptorMailer\Mail
     */
    public function cc($email, $name = null)
    {
        // if not array...
        if (!is_array($email))
        {
            // set normal
            $this->cc[] =
            [
                'email' => $email,
                'name'  => $name
            ];
        }
        else
        {
            // spin array...
            foreach ($email as $address)
            {
                // fix array
                if (!is_array($address))
                {
                    $address = array($address);
                }

                // set convention
                $this->cc[] =
                [
                    'email' => isset($address[0]) ? $address[0] : null,
                    'name'  => isset($address[1]) ? $address[1] : null
                ];
            }
        }

        return $this;
    }

    /**
     * Set email blind carbon copy addresses / names
     *
     * @access  public
     * @param   string             $email  A unique or array of valid emails addresses
     * @param   string             $name  (optional) Set a receiver name
     * @return  RaptorMailer\Mail
     */
    public function bcc($email, $name = null)
    {
        // if not array...
        if (!is_array($email))
        {
            // set normal
            $this->bcc[] =
            [
                'email' => $email,
                'name'  => $name
            ];
        }
        else
        {
            // spin array...
            foreach ($email as $address)
            {
                // fix array
                if (!is_array($address))
                {
                    $address = array($address);
                }

                // set convention
                $this->bcc[] =
                [
                    'email' => isset($address[0]) ? $address[0] : null,
                    'name'  => isset($address[1]) ? $address[1] : null
                ];
            }
        }

        return $this;
    }

    /**
     * Set email body content in html format
     *
     * @access  public
     * @param   string             $html  HTML message
     * @return  RaptorMailer\Mail
     */
    public function html($html)
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Set email body content in html format using mako templates
     *
     * @access  public
     * @param   string             $template  Mako template path
     * @param   string             $data      Template data array
     * @return  RaptorMailer\Mail
     */
    public function view($template, array $data)
    {
        // Email Body Content
        $this->html = new View($template, $data);

        return $this;
    }

    /**
     * Set email body content in plain text format
     *
     * @access  public
     * @param   string             $text  Text message
     * @return  RaptorMailer\Mail
     */
    public function text($text)
    {
        $this->text = wordwrap(strip_tags($text), $this->wordwrap);

        return $this;
    }

    /**
     * Set email subject
     *
     * @access  public
     * @param   string             $subject  Email subject
     * @return  RaptorMailer\Mail
     */
    public function subject($subject)
    {
        $this->subject = '=?' . $this->charset . '?B?' . base64_encode($subject) . '?=';

        return $this;
    }

    /**
     * Attach files to email
     *
     * @access  public
     * @param   string             $path  Unique or Array of Files Real Paths
     * @return  RaptorMailer\Mail
     */
    public function attach($path)
    {
        // if not array...
        if (!is_array($path))
        {
            // add
            $this->attachments[] = $path;
        }
        else
        {
            // spin array...
            foreach ($path as $p)
            {
                // add
                $this->attachments[] = $p;
            }
        }

        return $this;
    }

    /**
     * Send email after request connection
     *
     * @access  public
     * @return  boolean
     */
    public function send()
    {
        // connect to server
        if ($this->connect())
        {
            // deliver the email
            $result = $this->deliver() ? true : false;
        }
        else
        {
            $result = false;
        }

        // disconnect
        $this->disconnect();

        // return
        return $result;
    }

    /**
     * Send STMP server connection request
     *
     * @access  private
     * @return  boolean
     */
    private function connect()
    {
        // modify url, if needed
        if ($this->secure === 'ssl')
        {
            $this->host = 'ssl://' . $this->host;
        }

        // Open Connection
        $this->connection = fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);

        // Response
        if ($this->code() !== 220)
        {
            return false;
        }

        // Response
        $this->response();

        // if tls required...
        if ($this->secure === 'tls')
        {
            // Request
            $this->request('STARTTLS' . $this->newline);

            // Response
            if ($this->code() !== 220)
            {
                return false;
            }

            // Enable crypto
            stream_socket_enable_crypto($this->connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        }

        // if auth required...
        if ($this->auth)
        {
            // Request
            $this->request('EHLO ' . $this->localhost . $this->newline);

            // Response
            if ($this->code() !== 250)
            {
                return false;
            }

            // request
            $this->request('AUTH LOGIN' . $this->newline);

            // Response
            if ($this->code() !== 334)
            {
                return false;
            }

            // request
            $this->request(base64_encode($this->user) . $this->newline);

            // Response
            if ($this->code() !== 334)
            {
                return false;
            }

            // request
            $this->request(base64_encode($this->pass) . $this->newline);

            // Response
            if ($this->code() !== 235)
            {
                return false;
            }
        }
        else
        {
            // Request
            $this->request('HELO ' . $this->localhost . $this->newline);

            // Response
            if ($this->code() !== 250)
            {
                return false;
            }
        }

        // return
        return true;
    }

    /**
     * Construct email header and body parameters
     *
     * @access  private
     * @return  string
     */
    private function construct()
    {
        // Set unique boundary
        $boundary = md5(uniqid(time()));

        // Add "from" info
        $headers[] = 'From: ' . $this->format($this->from);
        $headers[] = 'Reply-To: ' . $this->format($this->reply ? $this->reply : $this->from);
        $headers[] = 'Subject: ' . $this->subject;
        $headers[] = 'Date: ' . date('r');

        // Add "to" receipients
        if (!empty($this->to))
        {
            $string = '';
            foreach ($this->to as $recipient)
            {
                $string .= $this->format($recipient) . ', ';
            }

            $string = substr($string, 0, -2);

            $headers[] = 'To: ' . $string;
        }

        // Add "cc" recipients
        if (!empty($this->cc))
        {
            $string = '';
            foreach ($this->cc as $recipient)
            {
                $string .= $this->format($recipient) . ', ';
            }

            $string = substr($string, 0, -2);

            $headers[] = 'CC: ' . $string;
        }

        // Build email contents
        if (empty($this->attachments))
        {
            if (empty($this->html))
            {
                // Add text
                $headers[] = 'Content-Type: text/plain; charset="' . $this->charset . '"';
                $headers[] = 'Content-Transfer-Encoding: ' . $this->encoding;
                $headers[] = '';
                $headers[] = $this->text;
            }
            else
            {
                // Add multipart
                $headers[] = 'MIME-Version: 1.0';
                $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
                $headers[] = '';
                $headers[] = 'This is a multi-part message in MIME format.';
                $headers[] = '--' . $boundary;

                // Add text
                $headers[] = 'Content-Type: text/plain; charset="' . $this->charset . '"';
                $headers[] = 'Content-Transfer-Encoding: ' . $this->encoding;
                $headers[] = '';
                $headers[] = $this->text;
                $headers[] = '--' . $boundary;

                // Add html
                $headers[] = 'Content-Type: text/html; charset="' . $this->charset . '"';
                $headers[] = 'Content-Transfer-Encoding: ' . $this->encoding;
                $headers[] = '';
                $headers[] = $this->html;
                $headers[] = '--' . $boundary . '--';
            }
        }
        else
        {
            // Add multipart
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';
            $headers[] = '';
            $headers[] = 'This is a multi-part message in MIME format.';
            $headers[] = '--' . $boundary;

            // Add text
            $headers[] = 'Content-Type: text/plain; charset="' . $this->charset . '"';
            $headers[] = 'Content-Transfer-Encoding: ' . $this->encoding;
            $headers[] = '';
            $headers[] = $this->text;
            $headers[] = '--' . $boundary;

            if (!empty($this->html))
            {
                // Add html
                $headers[] = 'Content-Type: text/html; charset="' . $this->charset . '"';
                $headers[] = 'Content-Transfer-Encoding: ' . $this->encoding;
                $headers[] = '';
                $headers[] = $this->html;
                $headers[] = '--' . $boundary;
            }

            // spin thru attachments...
            foreach ($this->attachments as $path)
            {
                // if file exists...
                if (file_exists($path))
                {
                    // open file
                    $contents = @file_get_contents($path);

                    // if accessible...
                    if ($contents)
                    {
                        // encode file contents
                        $contents = chunk_split(base64_encode($contents));

                        // add attachment
                        $headers[] = 'Content-Type: application/octet-stream; name="' . basename($path) . '"'; // use different content types here
                        $headers[] = 'Content-Transfer-Encoding: base64';
                        $headers[] = 'Content-Disposition: attachment';
                        $headers[] = '';
                        $headers[] = $contents;
                        $headers[] = '--' . $boundary;
                    }
                }
            }

            // add last "--"
            $headers[sizeof($headers) - 1] .= '--';
        }

        // Final period
        $headers[] = '.';

        // Build headers string
        $email = '';
        foreach ($headers as $header)
        {
            $email .= $header . $this->newline;
        }

        // Return
        return $email;
    }

    /**
     * Deliver SMTP message
     *
     * @access  private
     */
    private function deliver()
    {
        // Request
        $this->request('MAIL FROM: <' . $this->from['email'] . '>' . $this->newline);

        // Response
        $this->response();

        // Merge recipients...
        $recipients = array_merge($this->to, $this->cc, $this->bcc);

        // Spin recipients...
        foreach ($recipients as $recipient)
        {
            // Request
            $this->request('RCPT TO: <' . $recipient['email'] . '>' . $this->newline);

            // Response
            $this->response();
        }

        // Request
        $this->request('DATA' . $this->newline);

        // Response
        $this->response();

        // Request
        $this->request($this->construct());

        // Response
        return ($this->code() === 250) ? true : false;
    }

    /**
     * Disconnect from SMTP server after request is done
     *
     * @access  private
     */
    private function disconnect()
    {
        // Request
        $this->request('QUIT' . $this->newline);

        // Response
        $this->response();

        // Close connection
        fclose($this->connection);
    }

    /**
     * Return response code
     *
     * @access  private
     * @return  string
     */
    private function code()
    {
        // Filter code from response
        return (int) substr($this->response(), 0, 3);
    }

    /**
     * Put a SMTP request parameter
     *
     * @access  private
     * @param   string    $string  SMTP Parameter
     */
    private function request($string)
    {
        // Report
        if ($this->debug)
        {
            echo '<code><strong>'.$string.'</strong></code><br/>';
        }

        // Send
        fputs($this->connection, $string);
    }

    /**
     * Return a SMTP response
     *
     * @access  private
     * @return  string
     */
    private function response()
    {
        // Get response
        $response = '';

        // Spin smtp response
        while ($str = fgets($this->connection, 4096))
        {
            $response .= $str;

            if (substr($str, 3, 1) === ' ')
            {
                break;
            }
        }

        // Report
        if ($this->debug)
        {
            echo '<code>'.$response.'</code><br/>';
        }

        // Return
        return $response;
    }

    /**
     * Format a recipient in "Name <email>" format
     *
     * @access  private
     * @param   array   $recipient  Array of email and name
     * @return  string
     */
    private function format($recipient)
    {
        // Format "name <email>"
        if ($recipient['name'])
        {
            return $recipient['name'] .' <'.$recipient['email'].'>';
        }
        else
        {
            return '<' . $recipient['email'] . '>';
        }
    }
}