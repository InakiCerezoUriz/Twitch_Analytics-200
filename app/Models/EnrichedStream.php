<?php

namespace TwitchAnalytics\Models;

class EnrichedStream
{
    private string $stream_id;
    private string $user_id;
    private string $user_name;
    private int $viewer_count;
    private string $title;
    private string $user_display_name;
    private string $profile_image_url;

    public function setStreamInfo(array $streamInfo): void
    {
        $this->stream_id    = $streamInfo['id'];
        $this->user_id      = $streamInfo['user_id'];
        $this->user_name    = $streamInfo['user_name'];
        $this->viewer_count = $streamInfo['viewer_count'];
        $this->title        = $streamInfo['title'];
    }

    public function setUserInfo(array $userInfo): void
    {
        $this->user_display_name = $userInfo['display_name'];
        $this->profile_image_url = $userInfo['profile_image_url'];
    }

    public function getEnrichedStream(): array
    {
        return [
            'stream_id'         => $this->stream_id,
            'user_id'           => $this->user_id,
            'user_name'         => $this->user_name,
            'viewer_count'      => $this->viewer_count,
            'title'             => $this->title,
            'user_display_name' => $this->user_display_name,
            'profile_image_url' => $this->profile_image_url,
        ];
    }
}
