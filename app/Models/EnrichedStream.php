<?php

namespace TwitchAnalytics\Models;

class EnrichedStream
{
    private string $streamId;
    private string $userId;
    private string $userName;
    private int $viewerCount;
    private string $title;
    private string $userDisplayName;
    private string $profileImageUrl;

    public function setStreamInfo(array $streamInfo): void
    {
        $this->streamId    = $streamInfo['id'];
        $this->userId      = $streamInfo['user_id'];
        $this->userName    = $streamInfo['user_name'];
        $this->viewerCount = $streamInfo['viewer_count'];
        $this->title       = $streamInfo['title'];
    }

    public function setUserInfo(array $userInfo): void
    {
        $this->userDisplayName = $userInfo['display_name'];
        $this->profileImageUrl = $userInfo['profile_image_url'];
    }

    public function getEnrichedStream(): array
    {
        return [
            'stream_id'         => $this->streamId,
            'user_id'           => $this->userId,
            'user_name'         => $this->userName,
            'viewer_count'      => $this->viewerCount,
            'title'             => $this->title,
            'user_display_name' => $this->userDisplayName,
            'profile_image_url' => $this->profileImageUrl,
        ];
    }
}
