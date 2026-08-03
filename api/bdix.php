<?php

$url = "https://raw.githubusercontent.com/incognitobrothers/AynaOTT-Auto-Update-Playlist/refs/heads/main/ayna_live.m3u";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    die("cURL Error: " . curl_error($ch));
}
curl_close($ch);

$lines = preg_split("/\r\n|\n|\r/", trim($response));

$output = [];
$seen = [];

for ($i = 0; $i < count($lines); $i++) {

    if (strpos($lines[$i], "#EXTM3U") === 0) {
        continue;
    }

    if (strpos($lines[$i], "#EXTINF") === 0) {

        $extinf = $lines[$i];
        $urlLine = $lines[$i + 1] ?? "";

        // Channel name
        $parts = explode(",", $extinf, 2);
        $channelName = strtolower(trim(end($parts)));

        // Keep only first occurrence
        if (!isset($seen[$channelName])) {
            $seen[$channelName] = true;
            $output[] = $extinf;
            $output[] = trim($urlLine);
        }

        $i++;
    }
}

header("Content-Type: audio/x-mpegurl");
echo "#EXTM3U\n";
echo implode("\n", $output);
?>
