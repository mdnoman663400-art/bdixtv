<?php

$playlists = [
    [
        "url" => "https://raw.githubusercontent.com/abusaeeidx/Toffee-playlist/refs/heads/main/ott_navigator.m3u",
        "filterBanglaCategory" => true
    ],
    [
        "url" => "https://raw.githubusercontent.com/sm-monirulislam/AynaOTT-auto-update-playlist/refs/heads/main/AynaOTT.m3u",
        "filterBanglaCategory" => false
    ]
];

$output = [];
$seen = [];

foreach ($playlists as $playlist) {

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $playlist["url"],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => "Mozilla/5.0"
    ]);

    $data = curl_exec($ch);
    curl_close($ch);

    if (!$data) {
        continue;
    }

    $lines = preg_split("/\r\n|\n|\r/", trim($data));

    for ($i = 0; $i < count($lines); $i++) {

        if (strpos($lines[$i], "#EXTINF") !== 0) {
            continue;
        }

        $entry = [];
        $extinf = $lines[$i];
        $entry[] = $extinf;

        // Remove only "বাংলাদেশি চ্যানেল" category from Toffee playlist
        if (
            $playlist["filterBanglaCategory"] &&
            preg_match('/group-title\s*=\s*"[^"]*বাংলাদেশি চ্যানেল[^"]*"/ui', $extinf)
        ) {
            while (++$i < count($lines)) {
                if (preg_match('/^(https?|rtmp|rtsp|udp):/i', trim($lines[$i]))) {
                    break;
                }
            }
            continue;
        }

        // Channel name
        $parts = explode(",", $extinf, 2);
        $channelName = strtolower(trim(end($parts)));

        // Skip duplicate only from Ayna playlist
        if (!$playlist["filterBanglaCategory"] && isset($seen[$channelName])) {

            while (++$i < count($lines)) {
                if (preg_match('/^(https?|rtmp|rtsp|udp):/i', trim($lines[$i]))) {
                    break;
                }
            }
            continue;
        }

        $seen[$channelName] = true;

        // Copy metadata + stream URL
        while (++$i < count($lines)) {

            $line = trim($lines[$i]);
            $entry[] = $line;

            if (preg_match('/^(https?|rtmp|rtsp|udp):/i', $line)) {
                break;
            }
        }

        $output[] = implode("\n", $entry);
    }
}

header("Content-Type: audio/x-mpegurl; charset=UTF-8");
echo "#EXTM3U\n\n";
echo implode("\n\n", $output);

?>
