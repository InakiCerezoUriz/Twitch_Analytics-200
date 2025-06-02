<?php

namespace TwitchAnalytics\Models;

class Stream
{
    private string $title;
    private string $user_name;

    public function __construct(string $title, string $user_name)
    {
        $this->title     = $title;
        $this->user_name = $user_name;
    }

    public function getStream(): array
    {
        return [
            'title'     => $this->title,
            'user_name' => $this->user_name,
        ];
    }
}
