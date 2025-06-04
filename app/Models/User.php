<?php

namespace TwitchAnalytics\Models;

class User
{
    private string $id;
    private string $login;
    private string $displayName;
    private string $type;
    private string $broadcasterType;
    private string $description;
    private string $profileImageUrl;
    private string $offlineImageUrl;
    private string $viewCount;
    private string $createdAt;

    public function __construct(array $data)
    {
        $this->id              = $data['id'];
        $this->login           = $data['login'];
        $this->displayName     = $data['display_name'];
        $this->type            = $data['type'];
        $this->broadcasterType = $data['broadcaster_type'];
        $this->description     = $data['description'];
        $this->profileImageUrl = $data['profile_image_url'];
        $this->offlineImageUrl = $data['offline_image_url'];
        $this->viewCount       = $data['view_count'];
        $this->createdAt       = $data['created_at'];
    }

    public function getInfo(): array
    {
        return [
            'id'                => $this->id,
            'login'             => $this->login,
            'display_name'      => $this->displayName,
            'type'              => $this->type,
            'broadcaster_type'  => $this->broadcasterType,
            'description'       => $this->description,
            'profile_image_url' => $this->profileImageUrl,
            'offline_image_url' => $this->offlineImageUrl,
            'view_count'        => $this->viewCount,
            'created_at'        => $this->createdAt,
        ];
    }
}
