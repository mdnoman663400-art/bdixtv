<?php

$playlists = [
    [
        "url" => "https://raw.githubusercontent.com/abusaeeidx/Toffee-playlist/refs/heads/main/ott_navigator.m3u",
        "type" => "toffee"
    ],
    [
        "url" => "https://raw.githubusercontent.com/incognitobrothers/AynaOTT-Auto-Update-Playlist/refs/heads/main/ayna_live.m3u",
        "type" => "ayna"
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

    if (!$data) continue;

    $lines = preg_split("/\r\n|\n|\r/", $data);
    $count = count($lines);

    for ($i = 0; $i < $count; $i++) {

        if (strpos($lines[$i], "#EXTINF") !== 0) {
            continue;
        }

        $extinf = $lines[$i];

        // Remove News & Bangladeshi categories only from Toffee playlist
        if (
            $playlist["type"] == "toffee" &&
            preg_match('/group-title="[^"]*(news|bangladesh|bangladeshi)[^"]*"/i', $extinf)
        ) {
            while (++$i < $count) {
                if (strpos($lines[$i], "#EXTINF") === 0) {
                    $i--;
                    break;
                }
            }
            continue;
        }

        $parts = explode(",", $extinf, 2);
        $channelName = strtolower(trim(end($parts)));

        // Skip duplicate only from Ayna playlist
        if ($playlist["type"] == "ayna" && isset($seen[$channelName])) {
            while (++$i < $count) {
                if (strpos($lines[$i], "#EXTINF") === 0) {
                    $i--;
                    break;
                }
            }
            continue;
        }

        $seen[$channelName] = true;

        $output[] = $extinf;

        // Copy all metadata lines + stream URL
        while (++$i < $count) {

            if (strpos($lines[$i], "#EXTINF") === 0) {
                $i--;
                break;
            }

            $output[] = $lines[$i];
        }
    }
}

header("Content-Type: audio/x-mpegurl; charset=UTF-8");
echo "#EXTM3U\n";
echo implode("\n", $output);

?>
