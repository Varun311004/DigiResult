<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';


/*
|--------------------------------------------------------------------------
| HEADER SAFETY
|--------------------------------------------------------------------------
*/

function mailer_clean_header(string $value): string
{
    return str_replace(
        ["\r", "\n"],
        '',
        trim($value)
    );
}


/*
|--------------------------------------------------------------------------
| LAST MAIL ERROR
|--------------------------------------------------------------------------
*/

function mailer_last_error(): string
{
    return (string) (
        $_SESSION['_mailer_last_error'] ?? ''
    );
}


/*
|--------------------------------------------------------------------------
| LAST MESSAGE ID
|--------------------------------------------------------------------------
*/

function mailer_last_message_id(): string
{
    return (string) (
        $_SESSION['_mailer_last_message_id'] ?? ''
    );
}


/*
|--------------------------------------------------------------------------
| STORE MAIL RESULT
|--------------------------------------------------------------------------
*/

function mailer_set_result(
    ?string $error = null,
    ?string $messageId = null
): void {

    if ($error !== null) {

        $_SESSION['_mailer_last_error'] = mb_substr(
            $error,
            0,
            500
        );

    } else {

        unset(
            $_SESSION['_mailer_last_error']
        );
    }


    if ($messageId !== null) {

        $_SESSION['_mailer_last_message_id'] =
            mb_substr(
                $messageId,
                0,
                500
            );

    } else {

        unset(
            $_SESSION['_mailer_last_message_id']
        );
    }
}


/*
|--------------------------------------------------------------------------
| STUDENT OTP
|--------------------------------------------------------------------------
*/

function mailer_template(
    string $name,
    array $replacements = []
): string {
    $path = __DIR__ . '/templates/' . $name . '.html';
    $template = file_get_contents($path);

    if ($template === false) {
        throw new RuntimeException(
            'DigiResult email template could not be loaded: ' . $name
        );
    }

    return strtr($template, $replacements);
}


/*
|--------------------------------------------------------------------------
| STUDENT OTP
|--------------------------------------------------------------------------
*/

function send_otp_email(
    string $to,
    string $otp
): bool {
    $expiryMinutes = (int) cfg(
        'security.otp_ttl_minutes',
        10
    );

    try {
        $bodyHtml = mailer_template(
            'otp-email',
            [
                '{{OTP}}' => e($otp),
                '{{EXPIRY_MINUTES}}' => e((string) $expiryMinutes),
            ]
        );
    } catch (Throwable $e) {
        mailer_set_result(
            'Email template could not be loaded.'
        );

        error_log(
            'DigiResult OTP email template error: '
            . $e->getMessage()
        );

        return false;
    }

    return send_transactional_email(
        $to,
        'DigiResult verification code',
        $bodyHtml,
        'DigiResult OTP'
    );
}


/*
|--------------------------------------------------------------------------
| ADMIN PASSWORD RESET EMAIL
|--------------------------------------------------------------------------
*/

function send_admin_password_reset_email(
    string $to,
    string $resetUrl,
    int $ttlMinutes
): bool {
    $bodyText =
        "DigiResult Administrator Password Reset\n\n"
        . "A password reset was requested for your "
        . "DigiResult administrator account.\n\n"
        . "Open this link to create a new password:\n"
        . $resetUrl
        . "\n\n"
        . "This link expires in "
        . $ttlMinutes
        . " minutes and can only be used once.\n\n"
        . "If you did not request this reset, you can safely ignore this email.";

    try {
        $html = mailer_template(
            'password-reset-email',
            [
                '{{RESET_URL}}' => e($resetUrl),
                '{{TTL_MINUTES}}' => e((string) $ttlMinutes),
            ]
        );
    } catch (Throwable $e) {
        mailer_set_result(
            'Email template could not be loaded.'
        );

        error_log(
            'DigiResult password-reset email template error: '
            . $e->getMessage()
        );

        return false;
    }

    return send_transactional_email(
        $to,
        'DigiResult - Reset Administrator Password',
        $html,
        'DigiResult admin password reset',
        $bodyText,
        'text'
    );
}


/*
|--------------------------------------------------------------------------
| TEST EMAIL
|--------------------------------------------------------------------------
*/

function send_test_email(string $to): bool
{
    try {
        $html = mailer_template('test-email');
    } catch (Throwable $e) {
        mailer_set_result(
            'Email template could not be loaded.'
        );

        error_log(
            'DigiResult test-email template error: '
            . $e->getMessage()
        );

        return false;
    }

    return send_transactional_email(
        $to,
        'DigiResult email configuration test',
        $html,
        'DigiResult email test'
    );
}


/*
|--------------------------------------------------------------------------
| GENERIC TRANSACTIONAL EMAIL
|--------------------------------------------------------------------------
*/

function send_transactional_email(
    string $to,
    string $subject,
    string $html,
    string $logContext = 'transactional email',
    ?string $plainText = null,
    string $bodyMode = 'html'
): bool {

    mailer_set_result(
        null,
        null
    );


    $mode = strtolower(
        (string) cfg(
            'mail.mode',
            'native'
        )
    );


    if ($mode === 'brevo_api') {

        return brevo_send(
            $to,
            $subject,
            $html,
            $plainText,
            $logContext,
            $bodyMode
        );
    }


    if ($mode === 'smtp') {

        return smtp_send(
            $to,
            $subject,
            $html
        );
    }


    /*
     * Native mail() fallback.
     */

    $from = mailer_clean_header(
        (string) cfg(
            'mail.from_email',
            ''
        )
    );


    $fromName = mailer_clean_header(
        (string) cfg(
            'mail.from_name',
            'DigiResult'
        )
    );


    if (
        !filter_var(
            $to,
            FILTER_VALIDATE_EMAIL
        )
        ||
        !filter_var(
            $from,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        mailer_set_result(
            'Invalid recipient or sender email address.'
        );

        return false;
    }


    $headers =
        "MIME-Version: 1.0\r\n"
        . "Content-Type: text/html; charset=UTF-8\r\n"
        . 'From: '
        . (
            $fromName !== ''
                ? $fromName . ' '
                : ''
        )
        . '<'
        . $from
        . ">\r\n";


    $ok = @mail(
        $to,
        $subject,
        $html,
        $headers
    );


    if (!$ok) {

        $msg =
            'PHP mail() failed. The host may not provide outbound mail.';

        mailer_set_result(
            $msg
        );

        error_log(
            'DigiResult '
            . $logContext
            . ' failed: '
            . $msg
        );

        return false;
    }


    return true;
}


/*
|--------------------------------------------------------------------------
| BREVO
|--------------------------------------------------------------------------
*/

function brevo_send(
    string $to,
    string $subject,
    string $html,
    ?string $plainText = null,
    string $logContext = 'transactional email',
    string $bodyMode = 'html'
): bool {

    $apiUrl = rtrim(
        (string) cfg(
            'mail.brevo_api_url',
            'https://api.brevo.com/v3/smtp/email'
        ),
        '/'
    );


    $apiKey = trim(
        (string) cfg(
            'mail.brevo_api_key',
            ''
        )
    );


    $from = mailer_clean_header(
        (string) cfg(
            'mail.from_email',
            ''
        )
    );


    $fromName = mailer_clean_header(
        (string) cfg(
            'mail.from_name',
            'DigiResult'
        )
    );


    $reply = mailer_clean_header(
        (string) cfg(
            'mail.reply_to',
            ''
        )
    );


    if ($apiKey === '') {

        mailer_set_result(
            'Brevo API key is missing from the server environment or .env.'
        );

        return false;
    }


    if (
        !filter_var(
            $to,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        mailer_set_result(
            'The recipient email address is invalid.'
        );

        return false;
    }


    if (
        !filter_var(
            $from,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        mailer_set_result(
            'The Brevo sender email is invalid or not configured.'
        );

        return false;
    }


    $bodyMode = strtolower(
        trim($bodyMode)
    );


    if ($bodyMode !== 'text') {
        $bodyMode = 'html';
    }


    if ($plainText === null) {
        $plainText = trim(
            strip_tags($html)
        );
    }


    /*
     * Brevo message bodies are sent in one mode per request.
     * OTP/test messages use HTML; password-reset messages use
     * plain text deliberately for maximum mailbox compatibility.
     */
    $payload = [

        'sender' => [
            'email' => $from,
            'name'  => $fromName,
        ],

        'to' => [
            [
                'email' => $to,
            ],
        ],

        'subject' => $subject,

    ];


    if ($bodyMode === 'text') {
        $payload['textContent'] = $plainText;
    } else {
        $payload['htmlContent'] = $html;
    }


    $tag = preg_replace(
        '/[^a-zA-Z0-9_-]+/',
        '-',
        trim($logContext)
    );


    if ($tag !== '') {

        $payload['tags'] = [
            substr($tag, 0, 80),
        ];
    }


    if (
        $reply !== ''
        &&
        filter_var(
            $reply,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $payload['replyTo'] = [
            'email' => $reply,
        ];
    }


    $json = json_encode(
        $payload,
        JSON_UNESCAPED_SLASHES
        |
        JSON_UNESCAPED_UNICODE
    );


    if ($json === false) {

        mailer_set_result(
            'Could not encode the Brevo request payload.'
        );

        return false;
    }


    $timeout = max(
        5,
        min(
            30,
            (int) cfg(
                'mail.brevo_timeout_seconds',
                15
            )
        )
    );


    /*
     * --------------------------------------------------------------
     * cURL
     * --------------------------------------------------------------
     */

    if (function_exists('curl_init')) {

        $ch = curl_init(
            $apiUrl
        );


        curl_setopt_array(
            $ch,
            [

                CURLOPT_POST => true,

                CURLOPT_POSTFIELDS => $json,

                CURLOPT_HTTPHEADER => [
                    'accept: application/json',
                    'api-key: ' . $apiKey,
                    'content-type: application/json',
                    'user-agent: DigiResult/1.0',
                ],

                CURLOPT_RETURNTRANSFER => true,

                CURLOPT_TIMEOUT => $timeout,

                CURLOPT_CONNECTTIMEOUT =>
                    min(
                        10,
                        $timeout
                    ),

                CURLOPT_SSL_VERIFYPEER => true,

                CURLOPT_SSL_VERIFYHOST => 2,

            ]
        );


        $response = false;
        $httpCode = 0;
        $curlError = '';


        for ($attempt = 1; $attempt <= 2; $attempt++) {

            $response = curl_exec(
                $ch
            );

            $httpCode = (int) curl_getinfo(
                $ch,
                CURLINFO_HTTP_CODE
            );

            $curlError = trim(
                (string) curl_error(
                    $ch
                )
            );

            $transient =
                $response === false
                || $httpCode === 429
                || $httpCode >= 500;

            if (!$transient || $attempt === 2) {
                break;
            }

            sleep(1);
        }


        curl_close(
            $ch
        );


        if (
            $response !== false
            &&
            $httpCode >= 200
            &&
            $httpCode < 300
        ) {

            $decoded = json_decode(
                (string) $response,
                true
            );


            $messageId = is_array(
                $decoded
            )
                ? (string) (
                    $decoded['messageId'] ?? ''
                )
                : '';


            mailer_set_result(
                null,
                $messageId
            );


            /*
             * Brevo accepted the email.
             */
            return true;
        }


        $detail = brevo_error_detail(
            $response,
            $httpCode,
            $curlError
        );


        mailer_set_result(
            $detail
        );


        error_log(
            'DigiResult Brevo API failed: '
            . $detail
        );


        return false;
    }


    /*
     * --------------------------------------------------------------
     * allow_url_fopen fallback
     * --------------------------------------------------------------
     */

    if (!ini_get('allow_url_fopen')) {

        mailer_set_result(
            'Brevo needs either the PHP cURL extension or allow_url_fopen=On.'
        );

        return false;
    }


    $headers =
        "accept: application/json\r\n"
        . "api-key: "
        . $apiKey
        . "\r\n"
        . "content-type: application/json\r\n"
        . "user-agent: DigiResult/1.0\r\n";


    $context = stream_context_create(
        [
            'http' => [
                'method' => 'POST',

                'header' => $headers,

                'content' => $json,

                'timeout' => $timeout,

                'ignore_errors' => true,
            ],
        ]
    );


    $response = @file_get_contents(
        $apiUrl,
        false,
        $context
    );


    $status = 0;


    if (
        !empty($http_response_header[0])
        &&
        preg_match(
            '/\s(\d{3})\s/',
            $http_response_header[0],
            $matches
        )
    ) {

        $status = (int) $matches[1];
    }


    if (
        $response !== false
        &&
        $status >= 200
        &&
        $status < 300
    ) {

        $decoded = json_decode(
            (string) $response,
            true
        );


        $messageId = is_array(
            $decoded
        )
            ? (string) (
                $decoded['messageId'] ?? ''
            )
            : '';


        mailer_set_result(
            null,
            $messageId
        );


        return true;
    }


    $detail = brevo_error_detail(
        $response,
        $status,
        'HTTP request failed'
    );


    mailer_set_result(
        $detail
    );


    error_log(
        'DigiResult Brevo API stream request failed: '
        . $detail
    );


    return false;
}


/*
|--------------------------------------------------------------------------
| BREVO ERROR
|--------------------------------------------------------------------------
*/

function brevo_error_detail(
    mixed $response,
    int $httpCode,
    string $transportError = ''
): string {

    $decoded = is_string(
        $response
    )
        ? json_decode(
            $response,
            true
        )
        : null;


    if (is_array($decoded)) {

        $message = trim(
            (string) (
                $decoded['message']
                ??
                $decoded['code']
                ??
                ''
            )
        );


        if ($message !== '') {

            return
                'Brevo HTTP '
                . $httpCode
                . ': '
                . $message;
        }
    }


    if ($transportError !== '') {

        return
            'Brevo transport error: '
            . $transportError;
    }


    return
        'Brevo HTTP '
        . $httpCode
        . ': the provider rejected or did not accept the request.';
}


/*
|--------------------------------------------------------------------------
| SMTP
|--------------------------------------------------------------------------
*/

function smtp_send(
    string $to,
    string $subject,
    string $html
): bool {

    /*
     * Keep your existing SMTP implementation here if you still
     * need SMTP in another environment.
     *
     * Brevo API should remain the preferred transport for DigiResult.
     */

    $smtp = (array) cfg(
        'mail.smtp',
        []
    );


    $host = (string) (
        $smtp['host'] ?? ''
    );


    $port = (int) (
        $smtp['port'] ?? 587
    );


    $enc = strtolower(
        (string) (
            $smtp['encryption'] ?? 'tls'
        )
    );


    $user = (string) (
        $smtp['username'] ?? ''
    );


    $pass = (string) (
        $smtp['password'] ?? ''
    );


    $from = (string) cfg(
        'mail.from_email',
        ''
    );


    $fromName = mailer_clean_header(
        (string) cfg(
            'mail.from_name',
            'DigiResult'
        )
    );


    if (
        $host === ''
        ||
        $user === ''
        ||
        $pass === ''
        ||
        !filter_var(
            $to,
            FILTER_VALIDATE_EMAIL
        )
        ||
        !filter_var(
            $from,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        mailer_set_result(
            'SMTP is not completely configured.'
        );

        return false;
    }


    $target =
        (
            $enc === 'ssl'
                ? 'ssl://'
                : ''
        )
        . $host
        . ':'
        . $port;


    $errno = 0;

    $errstr = '';


    $sock = @stream_socket_client(
        $target,
        $errno,
        $errstr,
        10
    );


    if (!$sock) {

        mailer_set_result(
            'SMTP connection failed: '
            . $errstr
        );

        return false;
    }


    stream_set_timeout(
        $sock,
        10
    );


    $expect = function (
        array $codes
    ) use ($sock): bool {

        $line = '';


        while (!feof($sock)) {

            $part = fgets(
                $sock,
                1024
            );


            if ($part === false) {
                break;
            }


            $line = $part;


            if (
                isset($part[3])
                &&
                $part[3] === ' '
            ) {

                break;
            }
        }


        $code = (int) substr(
            trim($line),
            0,
            3
        );


        return in_array(
            $code,
            $codes,
            true
        );
    };


    $cmd = function (
        string $command,
        array $codes
    ) use (
        $sock,
        $expect
    ): bool {

        fwrite(
            $sock,
            $command . "\r\n"
        );


        return $expect(
            $codes
        );
    };


    if (!$expect([220])) {

        fclose($sock);

        return false;
    }


    if (
        !$cmd(
            'EHLO localhost',
            [250]
        )
    ) {

        fclose($sock);

        return false;
    }


    if ($enc === 'tls') {

        if (
            !$cmd(
                'STARTTLS',
                [220]
            )
        ) {

            fclose($sock);

            return false;
        }


        if (
            !@stream_socket_enable_crypto(
                $sock,
                true,
                STREAM_CRYPTO_METHOD_TLS_CLIENT
            )
        ) {

            fclose($sock);

            return false;
        }


        if (
            !$cmd(
                'EHLO localhost',
                [250]
            )
        ) {

            fclose($sock);

            return false;
        }
    }


    if (
        !$cmd(
            'AUTH LOGIN',
            [334]
        )
    ) {

        fclose($sock);

        return false;
    }


    if (
        !$cmd(
            base64_encode($user),
            [334]
        )
    ) {

        fclose($sock);

        return false;
    }


    if (
        !$cmd(
            base64_encode($pass),
            [235]
        )
    ) {

        fclose($sock);

        return false;
    }


    if (
        !$cmd(
            'MAIL FROM:<'
            . $from
            . '>',
            [250]
        )
    ) {

        fclose($sock);

        return false;
    }


    if (
        !$cmd(
            'RCPT TO:<'
            . $to
            . '>',
            [250, 251]
        )
    ) {

        fclose($sock);

        return false;
    }


    if (
        !$cmd(
            'DATA',
            [354]
        )
    ) {

        fclose($sock);

        return false;
    }


    $headers =
        'From: '
        . $fromName
        . ' <'
        . $from
        . ">\r\n"
        . 'To: <'
        . mailer_clean_header($to)
        . ">\r\n"
        . 'Subject: '
        . mailer_clean_header($subject)
        . "\r\n"
        . "MIME-Version: 1.0\r\n"
        . "Content-Type: text/html; charset=UTF-8\r\n\r\n";


    fwrite(
        $sock,
        $headers
        . $html
        . "\r\n.\r\n"
    );


    $ok = $expect(
        [250]
    );


    fwrite(
        $sock,
        "QUIT\r\n"
    );


    fclose(
        $sock
    );


    return $ok;
}
