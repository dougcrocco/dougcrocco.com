<?php
// Contact endpoint for dougcrocco.com on IONOS shared hosting.
// The From address MUST be a real mailbox on this domain or IONOS rejects the send.
$to      = 'doug@dougcrocco.com';
$from    = 'doug@dougcrocco.com';
$fromName = 'dougcrocco.com';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'method']);
  exit;
}

$clean = function ($v) {
  return trim(str_replace(["\r", "\n", "%0a", "%0d"], ' ', (string) $v));
};

$email   = $clean($_POST['email']   ?? '');
$work    = $clean($_POST['work']    ?? '');
$subject = $clean($_POST['subject'] ?? '') ?: 'Website enquiry';
$message = trim((string) ($_POST['message'] ?? ''));

// honeypot: bots fill hidden fields, people don't
if (!empty($_POST['website'])) { echo json_encode(['ok' => true]); exit; }

if ($message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(422);
  echo json_encode(['ok' => false, 'error' => 'invalid']);
  exit;
}

$body = $message . "\n\n";
if ($work !== '') { $body .= "Re: " . $work . "\n"; }
$body .= "From: " . $email . "\n";
$body .= "Sent: " . date('Y-m-d H:i') . "\n";

$headers  = 'From: ' . $fromName . ' <' . $from . '>' . "\r\n";
$headers .= 'Reply-To: ' . $email . "\r\n";
$headers .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";

$sent = @mail($to, $subject, $body, $headers, '-f' . $from);

if (!$sent) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'send']);
  exit;
}

echo json_encode(['ok' => true]);
