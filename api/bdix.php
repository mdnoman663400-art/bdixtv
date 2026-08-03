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

    $ch = curl_init($playlist["url"]);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $data = curl_exec($ch);
    curl_close($ch);

    if (!$data) continue;

    $lines = preg_split("/\r\n|\n|\r/", trim($data));

    for ($i = 0; $i < count($lines); $i++) {

        if (strpos($lines[$i], "#EXTINF") !== 0) {
            continue;
        }

        $extinf = $lines[$i];
        $stream = trim($lines[$i + 1] ?? "");

        // Remove only News channels from Toffee playlist
        if ($playlist["removeNews"] &&
            preg_match('/group-title="[^"]*news[^"]*"/i', $extinf)) {
            $i++;
            continue;
        }

        $parts = explode(",", $extinf, 2);
        $name = strtolower(trim(end($parts)));

        if ($playlist["removeNews"]) {
            // Toffee playlist: always keep (except News)
            $seen[$name] = true;
            $output[] = $extinf;
            $output[] = $stream;
        } else {
            // Ayna playlist: skip only duplicates
            if (!isset($seen[$name])) {
                $seen[$name] = true;
                $output[] = $extinf;
                $output[] = $stream;
            }
        }

        $i++;
    }
}

header("Content-Type: audio/x-mpegurl");
echo "#EXTM3U\n";
echo implode("\n", $output);
?>
