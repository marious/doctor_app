<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Users\Models\User;
use Modules\Videos\Models\Video;

class VideoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $doctor = User::first();

        $videos = [
            // all
            [
                'target_audience'   => 'all',
                'title'             => 'How to Maintain a Healthy Lifestyle',
                'short_description' => 'Simple daily habits that improve your physical and mental well-being.',
                'video_url'         => 'https://www.youtube.com/watch?v=sTANio_2E0Q',
            ],
            [
                'target_audience'   => 'all',
                'title'             => 'Understanding Your Body: A Complete Guide',
                'short_description' => 'An easy-to-follow overview of how the human body works and how to keep it healthy.',
                'video_url'         => 'https://www.youtube.com/watch?v=Ae4MadKPJhg',
            ],

            // pregnancy (general)
            [
                'target_audience'   => 'pregnancy',
                'title'             => 'Your Pregnancy Week by Week',
                'short_description' => 'A full walkthrough of fetal development and what to expect at each stage of pregnancy.',
                'video_url'         => 'https://www.youtube.com/watch?v=DEbNETpSCbA',
            ],
            [
                'target_audience'   => 'pregnancy',
                'title'             => 'Nutrition During Pregnancy',
                'short_description' => 'What to eat and what to avoid to support a healthy pregnancy.',
                'video_url'         => 'https://www.youtube.com/watch?v=2LgMQT_UHzY',
            ],

            // pregnancy_1st
            [
                'target_audience'   => 'pregnancy_1st',
                'title'             => 'First Trimester: What to Expect',
                'short_description' => 'Everything you need to know about the first 12 weeks of pregnancy.',
                'video_url'         => 'https://www.youtube.com/watch?v=ap_lkRxcFJ4',
            ],
            [
                'target_audience'   => 'pregnancy_1st',
                'title'             => 'Managing Morning Sickness Naturally',
                'short_description' => 'Practical tips and remedies to ease nausea during early pregnancy.',
                'video_url'         => 'https://www.youtube.com/watch?v=fqSpQYMXMZo',
            ],

            // pregnancy_2nd
            [
                'target_audience'   => 'pregnancy_2nd',
                'title'             => 'Second Trimester Changes and Milestones',
                'short_description' => 'What happens to your body and your baby between weeks 13 and 26.',
                'video_url'         => 'https://www.youtube.com/watch?v=xZgOFgW27bE',
            ],
            [
                'target_audience'   => 'pregnancy_2nd',
                'title'             => 'Safe Exercises During the Second Trimester',
                'short_description' => 'Gentle workouts to stay active and comfortable as your bump grows.',
                'video_url'         => 'https://www.youtube.com/watch?v=U4Qj6PvzOKA',
            ],

            // pregnancy_3rd
            [
                'target_audience'   => 'pregnancy_3rd',
                'title'             => 'Preparing for Labor and Delivery',
                'short_description' => 'A step-by-step guide to getting ready for childbirth in the final weeks.',
                'video_url'         => 'https://www.youtube.com/watch?v=7lhMRKPSmOY',
            ],
            [
                'target_audience'   => 'pregnancy_3rd',
                'title'             => 'Breathing Techniques for Labor',
                'short_description' => 'Learn controlled breathing exercises that help manage contractions during labor.',
                'video_url'         => 'https://www.youtube.com/watch?v=acUZdGd_3Dg',
            ],

            // gynecology
            [
                'target_audience'   => 'gynecology',
                'title'             => 'Understanding PCOS: Symptoms and Management',
                'short_description' => 'A clear explanation of polycystic ovary syndrome and how to manage it effectively.',
                'video_url'         => 'https://www.youtube.com/watch?v=oC5zalXNSUw',
            ],
            [
                'target_audience'   => 'gynecology',
                'title'             => 'Why Regular Cervical Screening Saves Lives',
                'short_description' => 'The importance of Pap smears and HPV testing in preventing cervical cancer.',
                'video_url'         => 'https://www.youtube.com/watch?v=EC1P0DflWHk',
            ],
        ];

        foreach ($videos as $data) {
            Video::create(array_merge($data, ['created_by' => $doctor->id]));
        }
    }
}
