<?php
function send_email($to, $from, $subject, $body) {
  if (send_maildev_email($to, $from, $subject, $body)) {
    return true;
  }

  $headers = "From: " . sanitize_email_header($from) . "\r\n";
  $headers .= "Reply-To: " . sanitize_email_header($from) . "\r\n";
  return @mail($to, $subject, $body, $headers);
}

function send_maildev_email($to, $from, $subject, $body) {
  $socket = @fsockopen('127.0.0.1', 1025, $errno, $errstr, 2);
  if (!$socket) {
    return false;
  }

  stream_set_timeout($socket, 2);
  $ok = smtp_expect($socket, 220)
    && smtp_command($socket, "EHLO localhost", 250)
    && smtp_command($socket, "MAIL FROM:<" . sanitize_email_address($from) . ">", 250)
    && smtp_command($socket, "RCPT TO:<" . sanitize_email_address($to) . ">", [250, 251])
    && smtp_command($socket, "DATA", 354);

  if ($ok) {
    $message = "From: " . sanitize_email_header($from) . "\r\n";
    $message .= "Reply-To: " . sanitize_email_header($from) . "\r\n";
    $message .= "To: " . sanitize_email_header($to) . "\r\n";
    $message .= "Subject: " . sanitize_email_header($subject) . "\r\n";
    $message .= "MIME-Version: 1.0\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $message .= "\r\n" . dot_stuff($body) . "\r\n.";
    fwrite($socket, $message . "\r\n");
    $ok = smtp_expect($socket, 250);
  }

  smtp_command($socket, "QUIT", 221);
  fclose($socket);
  return $ok;
}

function smtp_command($socket, $command, $expected) {
  fwrite($socket, $command . "\r\n");
  return smtp_expect($socket, $expected);
}

function smtp_expect($socket, $expected) {
  $expectedCodes = is_array($expected) ? $expected : [$expected];
  $line = '';

  do {
    $line = fgets($socket, 512);
    if ($line === false) {
      return false;
    }
  } while (isset($line[3]) && $line[3] === '-');

  $code = (int) substr($line, 0, 3);
  return in_array($code, $expectedCodes, true);
}

function sanitize_email_address($email) {
  $email = trim((string) $email);
  return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : 'website@localhost';
}

function sanitize_email_header($value) {
  return trim(str_replace(["\r", "\n"], '', (string) $value));
}

function dot_stuff($body) {
  $normalized = str_replace(["\r\n", "\r"], "\n", (string) $body);
  $lines = explode("\n", $normalized);
  foreach ($lines as &$line) {
    if (isset($line[0]) && $line[0] === '.') {
      $line = '.' . $line;
    }
  }
  unset($line);
  return implode("\r\n", $lines);
}
?>
