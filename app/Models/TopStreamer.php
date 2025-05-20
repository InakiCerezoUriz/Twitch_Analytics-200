<?php

namespace App\Models;

class TopStreamer
{
    private string $game_id;
    private string $game_name;
    private string $user_name;
    private string $total_videos;
    private string $total_views;
    private string $most_viewed_title;
    private string $most_viewed_views;
    private string $most_viewed_duration;
    private string $most_viewed_created_at;

    public function __construct(array $game, array $streams, int $topStreamerStreams, int $totalViews)
    {
        $this->game_id                = $game['id'];
        $this->game_name              = $game['name'];
        $this->user_name              = $streams['user_name'];
        $this->total_videos           = $topStreamerStreams;
        $this->total_views            = $totalViews;
        $this->most_viewed_title      = $streams['title'];
        $this->most_viewed_views      = $streams['view_count'];
        $this->most_viewed_duration   = $streams['duration'];
        $this->most_viewed_created_at = $streams['created_at'];
    }

    public function getTopStreamer(): array
    {
        return [
            'game_id'                => $this->game_id,
            'game_name'              => $this->game_name,
            'user_name'              => $this->user_name,
            'total_videos'           => $this->total_videos,
            'total_views'            => $this->total_views,
            'most_viewed_title'      => $this->most_viewed_title,
            'most_viewed_views'      => $this->most_viewed_views,
            'most_viewed_duration'   => $this->most_viewed_duration,
            'most_viewed_created_at' => $this->most_viewed_created_at,
        ];
    }
}
