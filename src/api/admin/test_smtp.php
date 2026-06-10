<?php
// SMTP 연결 테스트 — 1회용, 사용 후 삭제
header('Content-Type: text/plain; charset=UTF-8');
require_once __DIR__ . '/../../lib/jwt.php';
$payload = jwt_from_request();
if (!$payload || ($payload['role'] ?? '') !== 's') { http_response_code(403); echo '권한 없음'; exit; }

require_once __DIR__ . '/../../lib/mailer.php';

echo "=== SMTP 연결 테스트 ===\n\n";

// 1. TCP 연결
echo "[1] tcp://smtp.gmail.com:587 연결 중...\n";
$sock = @stream_socket_client('tcp://smtp.gmail.com:587', $errno, $errstr, 10);
if (!$sock) { echo "실패: $errstr ($errno)\n"; exit; }
echo "성공\n";
echo "서버 응답: " . fgets($sock, 512);

// 2. EHLO
fwrite($sock, "EHLO windows.pyeongmok.com\r\n");
echo "\n[2] EHLO 응답:\n";
while (true) { $r = fgets($sock, 512); echo $r; if (substr($r,3,1)===' ') break; }

// 3. STARTTLS
fwrite($sock, "STARTTLS\r\n");
echo "\n[3] STARTTLS: " . fgets($sock, 512);

// 4. TLS 업그레이드
echo "[4] TLS 업그레이드...\n";
$ok = stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
echo $ok ? "성공\n" : "실패\n";
if (!$ok) { fclose($sock); exit; }

// 5. EHLO after TLS
fwrite($sock, "EHLO windows.pyeongmok.com\r\n");
echo "\n[5] EHLO (TLS 후):\n";
while (true) { $r = fgets($sock, 512); echo $r; if (substr($r,3,1)===' ') break; }

// 6. AUTH
fwrite($sock, "AUTH LOGIN\r\n");
echo "\n[6] AUTH LOGIN: " . fgets($sock, 512);
fwrite($sock, base64_encode(SMTP_USER) . "\r\n");
echo "[7] 유저명: " . fgets($sock, 512);
fwrite($sock, base64_encode(SMTP_PASS) . "\r\n");
$auth = fgets($sock, 512);
echo "[8] 인증 결과: " . $auth;

fwrite($sock, "QUIT\r\n");
fclose($sock);

echo "\n" . (substr(trim($auth),0,3)==='235' ? "✓ 인증 성공" : "✗ 인증 실패") . "\n";
