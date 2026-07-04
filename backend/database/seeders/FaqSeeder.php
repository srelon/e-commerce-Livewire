<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'title' => 'How long does shipping take?',
                'content' => 'Standard shipping takes 3–5 business days within the US. Expedited options are available at checkout for 1–2 day delivery.',
            ],
            [
                'title' => 'Do you ship internationally?',
                'content' => 'Yes, we ship to over 50 countries. International shipping typically takes 7–14 business days depending on your location.',
            ],
            [
                'title' => 'What is your return policy?',
                'content' => 'We accept returns within 30 days of purchase. Items must be in their original condition. Simply contact our support team to initiate a return.',
            ],
            [
                'title' => 'What payment methods do you accept?',
                'content' => 'We accept Visa, Mastercard, PayPal, and Apple Pay. All transactions are secured with SSL encryption.',
            ],
            [
                'title' => 'How can I track my order?',
                'content' => 'Once your order ships, you will receive a confirmation email with a tracking number. You can use it on our website or the carrier\'s site.',
            ],
            [
                'title' => 'How do I reach customer support?',
                'content' => 'You can reach us via email at hello@example.com or call 578-393-4937 during business hours (Mon–Sat 9AM–6PM).',
            ],
        ];

        foreach ($items as $sort => $item) {
            Faq::firstOrCreate(
                [
                    'title' => $item['title'],
                    'type' => 'contact',
                ],
                [
                    'content' => $item['content'],
                    'status' => 1,
                    'sort_order' => $sort + 1,
                ],
            );
        }
    }
}
