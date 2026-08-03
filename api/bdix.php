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

$seen = [];
$output = [];

foreach ($playlists as $playlist) {

    $data = file_get_contents($playlist["url"]);
    if (!$data) continue;

    $entries = preg_split('/(?=#EXTINF)/', $data);

    foreach ($entries as $entry) {

        $entry = trim($entry);
        if ($entry == '') continue;

        $lines = explode("\n", str_replace("\r", "", $entry));
        $extinf = trim($lines[0]);

        if (strpos($extinf, "#EXTINF") !== 0) continue;

        // Remove News category only from Toffee playlist
        if (
            $playlist["removeNews"] &&
            preg_match('/group-title="[^"]*news[^"]*"/i', $extinf)
        ) {
            continue;
        }

        // Channel Name
        $parts = explode(",", $extinf, 2);
        $name = strtolower(trim(end($parts)));

        // Skip duplicate only from Ayna
        if (!$playlist["removeNews"] && isset($seen[$name])) {
            continue;
        }

        $seen[$name] = true;
        $output[] = $entry;
    }
}

header("Content-Type: audio/x-mpegurl");
echo "#EXTM3U\n\n";
echo implode("\n\n", $output);
