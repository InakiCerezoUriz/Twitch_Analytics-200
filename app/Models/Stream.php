<?php

namespace TwitchAnalytics\Models;

class Stream
{
    private string $title;
    private string $userName;

    public function __construct(string $title, string $userName)
    {
        $this->title    = $title;
        $this->userName = $userName;
    }

    public function getStream(): array
    {
        return [
            'title'     => $this->title,
            'user_name' => $this->userName,
        ];
    }
}
