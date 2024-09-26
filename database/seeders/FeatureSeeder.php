<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            ['name' => 'Unlimited Users'],
            ['name' => 'Priority Support'],
            ['name' => 'Custom Domain'],
            ['name' => 'Advanced Analytics'],
            ['name' => 'Daily Backups'],
            ['name' => 'API Access'],
            ['name' => 'Multiple Integrations'],
            ['name' => '24/7 Customer Support'],
            ['name' => 'Access to Beta Features'],
            ['name' => 'Email Notifications'],
            ['name' => 'Mobile App Access'],
            ['name' => 'Multi-language Support'],
            ['name' => 'Team Collaboration'],
            ['name' => 'File Sharing'],
            ['name' => 'Single Sign-On (SSO)'],
            ['name' => 'Real-time Data Sync'],
            ['name' => 'Customizable Reports'],
            ['name' => 'Two-factor Authentication'],
            ['name' => 'Automated Workflows'],
            ['name' => 'Custom Branding'],
            ['name' => 'Data Encryption'],
            ['name' => 'Audit Logs'],
            ['name' => 'Multiple Payment Methods'],
            ['name' => 'Resource Scheduling'],
            ['name' => 'Role-based Permissions'],
            ['name' => 'Task Management'],
            ['name' => 'Multi-location Support'],
            ['name' => 'Cloud Storage'],
            ['name' => 'Push Notifications'],
            ['name' => 'Dedicated Account Manager'],
            ['name' => 'SSL Encryption'],
            ['name' => 'Customizable Dashboard'],
            ['name' => 'GDPR Compliance'],
            ['name' => 'Time Tracking'],
            ['name' => 'Multi-currency Support'],
            ['name' => 'Project Management Tools'],
            ['name' => 'Automatic Updates'],
            ['name' => 'White Label Option'],
            ['name' => 'Live Chat Support'],
            ['name' => 'Custom User Roles'],
            ['name' => 'Interactive Tutorials'],
            ['name' => 'Multi-step Approvals'],
            ['name' => 'Expense Tracking'],
            ['name' => 'Zapier Integration'],
            ['name' => 'Slack Integration'],
            ['name' => 'Salesforce Integration'],
            ['name' => 'Webhooks Support'],
            ['name' => 'AI-powered Insights'],
            ['name' => 'Marketplace Integrations'],
            ['name' => 'Uptime Monitoring'],
            ['name' => 'VIP Customer Support']
        ];


        foreach ($features as $feature) {
            Feature::create($feature);
        }
    }
}
