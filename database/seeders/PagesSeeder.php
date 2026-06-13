<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Pages\Models\Page;

class PagesSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug'    => 'faq',
                'title'   => 'Frequently Asked Questions',
                'content' => json_encode([
                    [
                        'question' => 'How do I book an appointment?',
                        'answer'   => 'Open the app, go to the home screen and tap <b>Book Appointment</b>. Choose your preferred date and available time slot, then confirm your booking.',
                    ],
                    [
                        'question' => 'How do I cancel or reschedule an appointment?',
                        'answer'   => 'Go to <b>My Appointments</b>, tap the appointment you wish to change, and select <b>Cancel</b> or <b>Reschedule</b>. Changes must be made at least 24 hours before the appointment.',
                    ],
                    [
                        'question' => 'How can I view my prescriptions?',
                        'answer'   => 'Navigate to <b>Treatment</b> from the home screen. You will find your active medications and the option to download your prescription as a PDF.',
                    ],
                    [
                        'question' => 'Can I send a message to my doctor?',
                        'answer'   => 'Yes. Use the <b>Chat</b> section to send text messages, images, or voice notes directly to your doctor or the clinic assistant.',
                    ],
                    [
                        'question' => 'How do I track my menstrual cycle?',
                        'answer'   => 'Go to the <b>Tracker</b> tab. You can log your cycle start date, symptoms, and view predictions for your next period and ovulation window.',
                    ],
                    [
                        'question' => 'Is my medical data secure?',
                        'answer'   => 'Absolutely. All your data is encrypted in transit and at rest. Only you and your authorized healthcare providers can access your records.',
                    ],
                    [
                        'question' => 'How do I update my profile information?',
                        'answer'   => 'Go to <b>Settings → Profile</b>. You can update your personal details, upload a profile photo, and change your password from there.',
                    ],
                    [
                        'question' => 'What should I do if I forget my password?',
                        'answer'   => 'On the login screen, tap <b>Forgot Password</b>. You will receive an OTP on your registered phone number to reset your password.',
                    ],
                ]),
            ],
            [
                'slug'    => 'contact_support',
                'title'   => 'Contact Support',
                'content' => json_encode([
                    'intro'   => 'Our support team is available Saturday through Thursday, 9 AM – 6 PM. We typically respond within a few hours.',
                    'email'   => 'support@clinicapp.com',
                    'phone'   => '+20 100 000 0000',
                    'whatsapp'=> '+20 100 000 0000',
                    'address' => '15 Al-Tahrir Street, Cairo, Egypt',
                    'hours'   => 'Sat – Thu: 9:00 AM – 6:00 PM',
                ]),
            ],
            [
                'slug'    => 'terms_conditions',
                'title'   => 'Terms & Conditions',
                'content' => <<<HTML
<h2>Terms &amp; Conditions</h2>
<p><em>Last updated: June 2026</em></p>

<p>Welcome to our clinic app. By downloading or using this application, you agree to be bound by the following terms and conditions. Please read them carefully before proceeding.</p>

<h3>1. Acceptance of Terms</h3>
<p>By accessing or using this application, you confirm that you are at least 18 years old and have read, understood, and agree to be bound by these Terms &amp; Conditions. If you do not agree, please do not use this app.</p>

<h3>2. Medical Disclaimer</h3>
<p>The information provided through this application is for general informational purposes only and does not constitute professional medical advice. Always seek the advice of your physician or qualified healthcare provider with any questions you may have regarding a medical condition.</p>

<h3>3. User Accounts</h3>
<p>You are responsible for maintaining the confidentiality of your account credentials. You agree to notify us immediately of any unauthorized use of your account. We are not liable for any loss resulting from unauthorized access to your account.</p>

<h3>4. Appointments</h3>
<p>Booking an appointment through the app does not guarantee immediate medical care. The clinic reserves the right to reschedule or cancel appointments based on availability. Cancellations must be made at least 24 hours in advance.</p>

<h3>5. Prohibited Conduct</h3>
<p>You agree not to use this application to:</p>
<ul>
  <li>Submit false or misleading information</li>
  <li>Violate any applicable laws or regulations</li>
  <li>Interfere with the operation of the app or its servers</li>
  <li>Attempt to gain unauthorized access to any part of the system</li>
</ul>

<h3>6. Intellectual Property</h3>
<p>All content, branding, and features within this application are the exclusive property of the clinic and are protected by applicable intellectual property laws. You may not copy, modify, or distribute any content without prior written consent.</p>

<h3>7. Limitation of Liability</h3>
<p>To the maximum extent permitted by law, we shall not be liable for any indirect, incidental, or consequential damages arising out of your use of or inability to use this application.</p>

<h3>8. Changes to Terms</h3>
<p>We reserve the right to modify these terms at any time. Continued use of the application after changes are posted constitutes your acceptance of the revised terms.</p>

<h3>9. Governing Law</h3>
<p>These Terms shall be governed by and construed in accordance with the laws of the Arab Republic of Egypt.</p>
HTML,
            ],
            [
                'slug'    => 'privacy_policy',
                'title'   => 'Privacy Policy',
                'content' => <<<HTML
<h2>Privacy Policy</h2>
<p><em>Last updated: June 2026</em></p>

<p>We are committed to protecting your personal and medical information. This Privacy Policy explains what data we collect, how we use it, and your rights regarding your information.</p>

<h3>1. Information We Collect</h3>
<ul>
  <li><b>Personal Information:</b> Name, phone number, date of birth, address, and emergency contact.</li>
  <li><b>Medical Information:</b> Medical history, allergies, medications, lab results, ultrasound findings, and clinical session notes.</li>
  <li><b>Usage Data:</b> App activity logs, device type, and operating system version for performance and diagnostic purposes.</li>
  <li><b>Communication Data:</b> Messages sent through the in-app chat feature.</li>
</ul>

<h3>2. How We Use Your Information</h3>
<ul>
  <li>To provide and manage your healthcare services and appointments</li>
  <li>To send you appointment reminders and health notifications</li>
  <li>To enable communication between you and your healthcare provider</li>
  <li>To improve app performance and user experience</li>
  <li>To comply with legal and regulatory requirements</li>
</ul>

<h3>3. Information Sharing</h3>
<p>We do not sell, trade, or rent your personal information to third parties. Your data may be shared only with:</p>
<ul>
  <li>Your treating physician and clinic staff</li>
  <li>Authorized third-party service providers under strict confidentiality agreements</li>
  <li>Legal authorities when required by law</li>
</ul>

<h3>4. Data Retention</h3>
<p>We retain your data for as long as necessary to provide our services and comply with legal obligations. You may request deletion of your account and associated data at any time through the app settings.</p>

<h3>5. Your Rights</h3>
<p>You have the right to access, correct, or delete your personal data. To exercise these rights, contact us at <b>support@clinicapp.com</b>.</p>

<h3>6. Contact</h3>
<p>For any privacy-related questions, please contact our Data Protection Officer at <b>privacy@clinicapp.com</b>.</p>
HTML,
            ],
            [
                'slug'    => 'privacy_security',
                'title'   => 'Privacy & Security',
                'content' => <<<HTML
<h2>Privacy &amp; Security</h2>
<p><em>Last updated: June 2026</em></p>

<p>Protecting your health data is our highest priority. We implement multiple layers of security to ensure your information remains confidential and secure at all times.</p>

<h3>Data Encryption</h3>
<p>All data transmitted between your device and our servers is encrypted using <b>TLS 1.3</b> (Transport Layer Security). Data stored in our databases is encrypted at rest using industry-standard AES-256 encryption.</p>

<h3>Authentication</h3>
<p>Your account is protected by:</p>
<ul>
  <li><b>OTP Verification:</b> One-time passwords sent to your registered phone number for login and sensitive actions.</li>
  <li><b>Biometric Login:</b> Optional fingerprint or Face ID authentication for quick and secure access.</li>
  <li><b>Session Tokens:</b> Secure, time-limited tokens are used to manage your login sessions.</li>
</ul>

<h3>Access Control</h3>
<p>Your medical records are only accessible to you and the healthcare providers directly involved in your care. Clinic staff access is role-based and strictly limited to what is necessary for your treatment.</p>

<h3>Infrastructure Security</h3>
<ul>
  <li>Our servers are hosted in secure, certified data centers</li>
  <li>Regular security audits and penetration testing are performed</li>
  <li>All staff with data access undergo security awareness training</li>
  <li>Automated monitoring systems detect and alert on suspicious activity</li>
</ul>

<h3>What You Can Do</h3>
<ul>
  <li>Never share your password or OTP with anyone</li>
  <li>Log out from shared or public devices after use</li>
  <li>Enable biometric authentication for added security</li>
  <li>Contact us immediately if you suspect unauthorized account access</li>
</ul>
HTML,
            ],
            [
                'slug'    => 'data_permissions',
                'title'   => 'Data Permissions',
                'content' => <<<HTML
<h2>Data Permissions</h2>
<p><em>Last updated: June 2026</em></p>

<p>To provide you with the best experience, our app requests certain permissions on your device. Below is a clear explanation of each permission and why it is needed.</p>

<h3>Camera</h3>
<p><b>Why we need it:</b> To allow you to take photos for your profile avatar and to capture images such as lab results or medical documents to share with your doctor through the chat feature.</p>
<p><b>When it is used:</b> Only when you actively choose to take or upload a photo.</p>

<h3>Photo Library / Storage</h3>
<p><b>Why we need it:</b> To allow you to select existing images from your gallery to upload as your profile photo or to share in the chat with your healthcare provider.</p>
<p><b>When it is used:</b> Only when you choose to pick a file or image from your device.</p>

<h3>Microphone</h3>
<p><b>Why we need it:</b> To allow you to record and send voice messages to your doctor through the in-app chat feature.</p>
<p><b>When it is used:</b> Only when you actively start a voice recording in the chat screen.</p>

<h3>Notifications</h3>
<p><b>Why we need it:</b> To send you appointment reminders, medication alerts, cycle tracking updates, and messages from your doctor.</p>
<p><b>When it is used:</b> Ongoing, based on your notification preferences which you can manage in the app settings.</p>

<h3>Internet Access</h3>
<p><b>Why we need it:</b> To communicate with our servers to load your appointments, medical records, and enable real-time chat with your healthcare provider.</p>
<p><b>When it is used:</b> Continuously while the app is in use.</p>

<h3>Managing Permissions</h3>
<p>You can review and change any of these permissions at any time through your device's <b>Settings → Apps → Clinic App → Permissions</b>. Revoking a permission may limit certain features of the app.</p>
HTML,
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], $page);
        }
    }
}
