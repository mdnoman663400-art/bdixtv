<?php

$playlists = [
    [
        "url" => "https://raw.githubusercontent.com/abusaeeidx/Toffee-playlist/refs/heads/main/ott_navigator.m3u",
        "removeNews" => true
    ],
    [
        "url" => "https://raw.githubusercontent.com/incognitobrothers/AynaOTT-Auto-Update-Playlist/refs/heads/main/ayna_live.m3u",
        "removeNews" => false
    ]
];

$output = [];
$seen = [];

foreach ($playlists as $playlist) {

    $data = file_get_contents($playlist["url"]);
    if (!$data) continue;

    $lines = preg_split("/\r\n|\n|\r/", trim($data));

    for ($i = 0; $i < count($lines); $i++) {

        if (strpos($lines[$i], "#EXTINF") !== 0) {
            continue;
        }

        $extinf = $lines[$i];

        // Remove only News channels from Toffee
        if ($playlist["removeNews"] &&
            preg_match('/group-title="[^"]*news[^"]*"/i', $extinf)) {

            while (++$i < count($lines) && !preg_match('/^(https?|rtmp|rtsp|udp):/i', trim($lines[$i])));
            continue;
        }

        $parts = explode(",", $extinf, 2);
        $name = strtolower(trim(end($parts)));

        // Skip duplicate only from Ayna playlist
        if (!$playlist["removeNews"] && isset($seen[$name])) {

            while (++$i < count($lines) && !preg_match('/^(https?|rtmp|rtsp|udp):/i', trim($lines[$i])));
            continue;
        }

        $seen[$name] = true;

        $output[] = $extinf;

        // Copy all metadata lines + stream URL
        while (++$i < count($lines)) {

            $line = trim($lines[$i]);
            $output[] = $line;

            if (preg_match('/^(https?|rtmp|rtsp|udp):/i', $line)) {
                break;
            }
        }
    }
}

header("Content-Type: audio/x-mpegurl");
echo "#EXTM3U\n";
echo implode("\n", $output);
