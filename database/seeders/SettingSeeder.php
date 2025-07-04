<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SettingGroup;
use App\Models\Setting;



class SettingSeeder extends Seeder
{
    public function run()
    {
        $groups = [
            'general' => 'General',
            'currency_tax' => 'Currency & Tax',
            'shipping' => 'Shipping',
            'contact' => 'Contact Info',
            'social' => 'Social Links',
            'seo' => 'SEO',
            'maintenance' => 'Maintenance',
            'pages' => 'Pages (FAQ, Terms, Policy)'
        ];

        foreach ($groups as $slug => $name) {
            $group = SettingGroup::firstOrCreate([
                'slug' => $slug,
            ], ['name' => $name]);

            match ($slug) {
                'general' => [

                    Setting::updateOrCreate(['setting_group_id' => $group->id, 'key' => 'store_name'], ['value' => 'VENZY']),
                    Setting::updateOrCreate(['setting_group_id' => $group->id, 'key' => 'store_email'], ['value' => 'support@venzy.com']),
                    Setting::updateOrCreate(['setting_group_id' => $group->id, 'key' => 'store_phone_1'], ['value' => '+2348012345678']),
                    Setting::updateOrCreate(['setting_group_id' => $group->id, 'key' => 'store_address'], ['value' => 'Lagos, Nigeria']),
                    Setting::updateOrCreate(['setting_group_id' => $group->id, 'key' => 'currency_code'], ['value' => 'NGN']),
                    Setting::updateOrCreate(['setting_group_id' => $group->id, 'key' => 'currency_symbol'], ['value' => '₦']),
                ],

                // 'tax' => [

                //     Setting::updateOrCreate(['setting_group_id' => $group->id, 'key' => 'tax_rate'], ['value' => '0']),
                // ],

                'shipping' => [
                    Setting::updateOrCreate(['setting_group_id' => $group->id, 'key' => 'shipping_type'], ['value' => 'flat']),
                    Setting::updateOrCreate(['setting_group_id' => $group->id, 'key' => 'flat_rate'], ['value' => '1500']),
                ],



                'social' => [
                    Setting::updateOrCreate(['setting_group_id' => $group->id, 'key' => 'facebook_link'], ['value' => 'https://facebook.com/yourstore']),
                    Setting::updateOrCreate(['setting_group_id' => $group->id, 'key' => 'instagram_link'], ['value' => 'https://instagram.com/yourstore']),
                ],

                'seo' => [
                    Setting::updateOrCreate(['setting_group_id' => $group->id, 'key' => 'default_meta_title'], ['value' => 'VENZY - Shop Smart']),
                    Setting::updateOrCreate(['setting_group_id' => $group->id, 'key' => 'default_meta_description'], ['value' => 'Your trusted ecommerce platform']),
                ],



                'pages' => [
                    Setting::updateOrCreate(['setting_group_id' => $group->id, 'key' => 'return_policy_content'], ['value' => 'Return & refund policy goes here...']),
                    Setting::updateOrCreate(['setting_group_id' => $group->id, 'key' => 'terms_content'], ['value' => 'Terms and conditions go here...']),
                    Setting::updateOrCreate(['setting_group_id' => $group->id, 'key' => 'policy_content'], ['value' => 'Privacy policy here...']),
                ],
                default => null
            };
        }
    }
}