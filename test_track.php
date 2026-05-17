<?php
$ch = curl_init('http://localhost:8081/kbuilder/public/api/analytics/track');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'event_type' => 'pageview',
    'target_url' => 'http://localhost/test',
    'session_id' => 'test-session-123'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$result = curl_exec($ch);
echo $result;
if(curl_errno($ch)){
    echo 'Curl error: ' . curl_error($ch);
}
curl_close($ch);
