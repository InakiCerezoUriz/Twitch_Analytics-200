<?php

namespace TwitchAnalytics\Models;

class TopStreamer
{
    private string $gameId;
    private string $gameName;
    private string $userName;
    private string $totalVideos;
    private string $totalViews;
    private string $mostViewedTitle;
    private string $mostViewedViews;
    private string $mostViewedDuration;
    private string $mostViewedCreatedAt;

    public function __construct(array $game, array $streams, int $topStreamerStreams, int $totalViews)
    {
        $this->gameId              = $game['id'];
        $this->gameName            = $game['name'];
        $this->userName            = $streams['user_name'];
        $this->totalVideos         = $topStreamerStreams;
        $this->totalViews          = $totalViews;
        $this->mostViewedTitle     = $streams['title'];
        $this->mostViewedViews     = $streams['view_count'];
        $this->mostViewedDuration  = $streams['duration'];
        $this->mostViewedCreatedAt = $streams['created_at'];
    }

    public function getTopStreamer(): array
    {
        return [
            'game_id'                => $this->gameId,
            'game_name'              => $this->gameName,
            'user_name'              => $this->userName,
            'total_videos'           => $this->totalVideos,
            'total_views'            => $this->totalViews,
            'most_viewed_title'      => $this->mostViewedTitle,
            'most_viewed_views'      => $this->mostViewedViews,
            'most_viewed_duration'   => $this->mostViewedDuration,
            'most_viewed_created_at' => $this->mostViewedCreatedAt,
        ];
    }
}
