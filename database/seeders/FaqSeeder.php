<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


use App\Models\Faq;

class FaqSeeder extends Seeder
{
    public function run()
    {
        $faqs = [
            [
                'question' => 'How can I track my order?',
                'answer' => 'Once your order has been shipped, you will receive an email with a tracking link. You can also track your order from your account dashboard.',
            ],
            [
                'question' => 'What is your return policy?',
                'answer' => 'We accept returns within 7 days of delivery. Items must be unused and in original packaging. Read our full return policy on the Returns page.',
            ],
            [
                'question' => 'Do you offer international shipping?',
                'answer' => 'Currently, we ship only within Nigeria. We are working on expanding our shipping options in the near future.',
            ],
            [
                'question' => 'Can I change or cancel my order?',
                'answer' => 'You can cancel or modify your order within 12 hours of placing it by contacting our support team.',
            ],
            [
                'question' => 'What payment methods are accepted?',
                'answer' => 'We accept all major debit/credit cards, Paystack, and bank transfers.',
            ],
            [
                'question' => 'Do you restock sold-out items?',
                'answer' => 'Some popular items may be restocked. You can subscribe to restock notifications on the product page.',
            ],
            [
                'question' => 'How do I contact customer service?',
                'answer' => 'You can reach us via our contact page, email us at support@venzy.com, or call our hotline at +2348012345678.',
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::create([
                'question' => $faq['question'],
                'answer'   => $faq['answer'],
                'is_active' => true,
            ]);
        }
    }
}