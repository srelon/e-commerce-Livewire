<?php

namespace App\Services;

use App\Models\NewsletterSubscriber;

class NewsletterService
{
    public function subscribe(array $data): NewsletterSubscriber
    {
        return NewsletterSubscriber::create([
            'email' => $data['email'],
            'status' => 1,
        ]);
    }
}
