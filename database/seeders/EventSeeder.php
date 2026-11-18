<?php

namespace Database\Seeders;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = [
            [
                'event_name' => 'Tech Innovations Summit 2026',
                'event_date' => Carbon::now()->addDays(10)->toDateString(),
                'description' => 'A premier gathering of industry pioneers discussing breakthrough advancements in artificial intelligence, next-gen cloud architectures, and scalable web engineering.',
            ],
            [
                'event_name' => 'National Laravel Developers Conference',
                'event_date' => Carbon::now()->addDays(20)->toDateString(),
                'description' => 'Deep-dive technical sessions and hands-on workshops covering modern Laravel application architecture, high-performance database optimization, and modern Blade & Vite tooling.',
            ],
            [
                'event_name' => 'Cybersecurity & Cloud Defense Forum',
                'event_date' => Carbon::now()->addDays(35)->toDateString(),
                'description' => 'Explore the future of zero-trust architecture, automated vulnerability scanning, and resilient cloud defense systems with leading cybersecurity experts.',
            ],
            [
                'event_name' => 'Full-Stack Web Innovation Expo',
                'event_date' => Carbon::now()->addDays(50)->toDateString(),
                'description' => 'An engaging full-day conference featuring interactive demos on PHP 8.3+, RESTful microservices, reactive frontend components, and modern DevOps practices.',
            ],
            [
                'event_name' => 'AI & Distributed Systems Workshop',
                'event_date' => Carbon::now()->addDays(65)->toDateString(),
                'description' => 'A masterclass designed for engineers looking to integrate intelligent workflows, vector databases, and real-time asynchronous event streaming into production software.',
            ],
        ];

        foreach ($events as $event) {
            Event::updateOrCreate(
                ['event_name' => $event['event_name']],
                [
                    'event_date' => $event['event_date'],
                    'description' => $event['description'],
                ]
            );
        }
    }
}

