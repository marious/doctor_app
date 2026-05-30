<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Articles\Models\Article;
use Modules\Users\Models\User;

class ArticleSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $doctor = User::first();

        $articles = [
            [
                'target_audience'   => 'all',
                'title'             => 'Understanding Your Annual Health Checkup',
                'short_description' => 'A complete guide to what happens during a routine annual physical exam and why it matters for everyone.',
                'full_text'         => 'Annual health checkups are a cornerstone of preventive medicine. They allow your doctor to monitor key health indicators, catch potential issues early, and keep your vaccinations and screenings up to date. During a typical visit you can expect blood pressure measurement, blood tests, body mass index assessment, and a discussion of your lifestyle habits.',
            ],
            [
                'target_audience'   => 'all',
                'title'             => 'Healthy Eating Habits for a Better Life',
                'short_description' => 'Simple dietary changes that can have a significant impact on your long-term health and energy levels.',
                'full_text'         => 'A balanced diet rich in vegetables, fruits, whole grains, and lean proteins provides the nutrients your body needs to function optimally. Reducing processed foods, added sugars, and excess salt can lower the risk of chronic diseases such as diabetes, hypertension, and heart disease.',
            ],
            [
                'target_audience'   => 'pregnancy',
                'title'             => 'Your Complete Pregnancy Nutrition Guide',
                'short_description' => 'Essential nutrients and dietary tips to support a healthy pregnancy from conception to delivery.',
                'full_text'         => 'During pregnancy your body requires increased amounts of folic acid, iron, calcium, and omega-3 fatty acids. Folic acid helps prevent neural tube defects, iron supports expanded blood volume, calcium builds your baby\'s bones and teeth, and omega-3s support brain development. Aim for a varied diet and discuss any supplements with your obstetrician.',
            ],
            [
                'target_audience'   => 'pregnancy',
                'title'             => 'Exercise During Pregnancy: What Is Safe?',
                'short_description' => 'Guidelines on staying active and fit throughout your pregnancy while keeping both you and your baby safe.',
                'full_text'         => 'Moderate exercise during pregnancy offers many benefits including reduced back pain, improved mood, better sleep, and potentially easier labor. Walking, swimming, and prenatal yoga are generally safe for most pregnancies. Always consult your doctor before starting or continuing any exercise routine, especially if you have complications.',
            ],
            [
                'target_audience'   => 'pregnancy_1st',
                'title'             => 'First Trimester: What to Expect',
                'short_description' => 'A week-by-week overview of fetal development and common symptoms during the first three months of pregnancy.',
                'full_text'         => 'The first trimester spans weeks 1–12 and is a period of rapid development. Your baby\'s heart begins beating around week 6, and by week 12 all major organs have formed. Common symptoms include nausea, fatigue, breast tenderness, and frequent urination. Your first prenatal visit typically occurs around week 8–10 and will include an ultrasound and blood tests.',
            ],
            [
                'target_audience'   => 'pregnancy_1st',
                'title'             => 'Managing Morning Sickness in the First Trimester',
                'short_description' => 'Practical tips and remedies to help you cope with nausea and vomiting during early pregnancy.',
                'full_text'         => 'Morning sickness affects up to 80% of pregnant women and can occur at any time of day. Eating small, frequent meals, staying hydrated, avoiding triggers such as strong smells, and keeping crackers on your nightstand can provide relief. If vomiting is severe or you cannot keep fluids down, contact your healthcare provider as you may need additional support.',
            ],
            [
                'target_audience'   => 'pregnancy_2nd',
                'title'             => 'Second Trimester Milestones and Tests',
                'short_description' => 'Key developmental milestones and recommended screenings during weeks 13–26 of pregnancy.',
                'full_text'         => 'The second trimester is often called the "golden period" as many women feel their energy return and nausea subside. Important screenings include the anatomy scan ultrasound around week 20, the glucose challenge test, and optional genetic screening. You will likely feel your baby move (quickening) for the first time between weeks 16 and 25.',
            ],
            [
                'target_audience'   => 'pregnancy_2nd',
                'title'             => 'Staying Comfortable as Your Bump Grows',
                'short_description' => 'Advice on posture, sleep positions, and clothing to stay comfortable during the second trimester.',
                'full_text'         => 'As your belly grows, you may experience back pain, round ligament pain, and difficulty finding a comfortable sleep position. Sleeping on your left side improves blood flow to the placenta. Supportive maternity wear, a pregnancy pillow, and gentle stretching can alleviate discomfort. Avoid lying flat on your back for extended periods after week 20.',
            ],
            [
                'target_audience'   => 'pregnancy_3rd',
                'title'             => 'Preparing for Labor and Delivery',
                'short_description' => 'Everything you need to know to get ready for the big day, from birth plans to hospital bag essentials.',
                'full_text'         => 'The third trimester is the time to finalize your birth plan, tour the labor and delivery unit, and pack your hospital bag. Your bag should include comfortable clothing, toiletries, your insurance card, a phone charger, snacks, and items for your newborn. Discuss pain management options, labor positions, and your preferences with your healthcare team in advance.',
            ],
            [
                'target_audience'   => 'pregnancy_3rd',
                'title'             => 'Recognizing Signs of Labor',
                'short_description' => 'How to distinguish true labor from Braxton Hicks contractions and when to head to the hospital.',
                'full_text'         => 'True labor contractions become progressively longer, stronger, and closer together regardless of position or activity. Other signs include your water breaking, a bloody show, and persistent lower back pain. Braxton Hicks contractions are irregular and typically ease with movement or hydration. Call your provider if contractions are 5 minutes apart for 1 hour, your water breaks, or you notice decreased fetal movement.',
            ],
            [
                'target_audience'   => 'gynecology',
                'title'             => 'Understanding Polycystic Ovary Syndrome (PCOS)',
                'short_description' => 'An overview of PCOS symptoms, causes, diagnosis, and management strategies for women of reproductive age.',
                'full_text'         => 'PCOS is a hormonal disorder affecting up to 10% of women of reproductive age. It is characterized by irregular periods, elevated androgen levels, and polycystic ovaries. Symptoms include irregular or absent periods, acne, excess hair growth, and weight gain. Management includes lifestyle changes, hormonal contraceptives, and medications such as metformin for insulin resistance.',
            ],
            [
                'target_audience'   => 'gynecology',
                'title'             => 'The Importance of Regular Cervical Screening',
                'short_description' => 'Why Pap smears and HPV testing are essential tools in preventing cervical cancer.',
                'full_text'         => 'Cervical cancer is largely preventable through regular screening and HPV vaccination. The Pap smear detects abnormal cervical cells before they become cancerous. Current guidelines recommend Pap tests every 3 years for women aged 21–65, or every 5 years when combined with HPV co-testing. Early detection dramatically improves outcomes.',
            ],
        ];

        foreach ($articles as $data) {
            Article::create(array_merge($data, ['created_by' => $doctor->id]));
        }
    }
}
