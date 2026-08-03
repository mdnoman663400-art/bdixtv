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

    $data = @file_get_contents($playlist["url"]);
    if (!$data) continue;

    $lines = preg_split("/\r\n|\n|\r/", $data);
    $total = count($lines);

    for ($i = 0; $i < $total; $i++) {

        if (strpos($lines[$i], "#EXTINF") !== 0) {
            continue;
        }

        $entry = [];
        $entry[] = $lines[$i];
        $extinf = $lines[$i];

        // Channel Name
        $parts = explode(",", $extinf, 2);
        $channelName = strtolower(trim(end($parts)));

        // Remove only Toffee News channels
        if (
            $playlist["type"] == "toffee" &&
            preg_match('/group-title="[^"]*news[^"]*"/i', $extinf)
        ) {
            while (++$i < $total) {
                if (strpos($lines[$i], "#EXTINF") === 0) {
                    $i--;
                    break;
                }
            }
            continue;
        }

        // Remove duplicate only from Ayna
        if ($playlist["type"] == "ayna" && isset($seen[$channelName])) {

            while (++$i < $total) {
                if (strpos($lines[$i], "#EXTINF") === 0) {
                    $i--;
                    break;
                }
            }
            continue;
        }

        $seen[$channelName] = true;

        // Copy complete entry
        while (++$i < $total) {

            if (strpos($lines[$i], "#EXTINF") === 0) {
                $i--;
                break;
            }

            $entry[] = $lines[$i];
        }

        $output[] = implode("\n", $entry);
    }
}

header("Content-Type: audio/x-mpegurl");
echo "#EXTM3U\n";
echo implode("\n", $output);

?>
