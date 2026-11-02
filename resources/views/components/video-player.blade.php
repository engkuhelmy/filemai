@props([
    'videoType' => null,
    'videoUrl' => '',
    'youtubeId' => null,
    'poster' => '',
    'title' => 'Episod Drama',
    'dramaTitle' => 'Drama Siri',
    'aspectRatio' => '9:16',
    'nextEpisodeUrl' => null,
    'prevEpisodeUrl' => null,
    'nextEpisodeTitle' => null,
    'nextEpisodeNumber' => null,
    'isPremium' => false,
    'ad' => null,
    'adDuration' => 15,
    'autoplay' => true,
])

<x-universal-player
    :video-type="$videoType"
    :video-url="$videoUrl"
    :youtube-id="$youtubeId"
    :poster="$poster"
    :title="$title"
    :drama-title="$dramaTitle"
    :aspect-ratio="$aspectRatio"
    :next-episode-url="$nextEpisodeUrl"
    :prev-episode-url="$prevEpisodeUrl"
    :next-episode-title="$nextEpisodeTitle"
    :next-episode-number="$nextEpisodeNumber"
    :is-premium="$isPremium"
    :ad="$ad"
    :ad-duration="$adDuration"
    :autoplay="$autoplay"
/>
