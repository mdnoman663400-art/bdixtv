<?php

$urls = [
    "https://raw.githubusercontent.com/incognitobrothers/AynaOTT-Auto-Update-Playlist/refs/heads/main/ayna_live.m3u",
    "https://raw.githubusercontent.com/abusaeeidx/Toffee-playlist/refs/heads/main/ott_navigator.m3u"
];

$seen = [];
$output = [];

foreach ($urls as $url) {

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) continue;

    $lines = preg_split("/\r\n|\n|\r/", trim($response));

    for ($i = 0; $i < count($lines); $i++) {

        if (strpos($lines[$i], "#EXTINF") === 0) {

            $extinf = $lines[$i];
            $stream = trim($lines[$i + 1] ?? "");

            // Channel name
            $parts = explode(",", $extinf, 2);
            $name = strtolower(trim(end($parts)));

            if (!isset($seen[$name])) {
                $seen[$name] = true;
                $output[] = $extinf;
                $output[] = $stream;
            }

            $i++;
        }
    }
}

header("Content-Type: audio/x-mpegurl");
echo "#EXTM3U\n";
echo implode("\n", $output);
?>
