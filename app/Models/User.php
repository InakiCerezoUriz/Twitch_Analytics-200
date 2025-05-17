<?php

namespace App\Models;

class User
{
    private string $id;
    private string $login;
    private string $display_name;
    private string $type;
    private string $broadcaster_type;
    private string $description;
    private string $profile_image_url;
    private string $offline_image_url;
    private string $view_count;
    private string $created_at;

    public function __construct(array $data)
    {
        $this->id                = $data['id'];
        $this->login             = $data['login'];
        $this->display_name      = $data['display_name'];
        $this->type              = $data['type'];
        $this->broadcaster_type  = $data['broadcaster_type'];
        $this->description       = $data['description'];
        $this->profile_image_url = $data['profile_image_url'];
        $this->offline_image_url = $data['offline_image_url'];
        $this->view_count        = $data['view_count'];
        $this->created_at        = $data['created_at'];
    }

    public function getInfo(): array
    {
        return [
            'id'                => $this->id,
            'login'             => $this->login,
            'display_name'      => $this->display_name,
            'type'              => $this->type,
            'broadcaster_type'  => $this->broadcaster_type,
            'description'       => $this->description,
            'profile_image_url' => $this->profile_image_url,
            'offline_image_url' => $this->offline_image_url,
            'view_count'        => $this->view_count,
            'created_at'        => $this->created_at,
        ];
    }
}
