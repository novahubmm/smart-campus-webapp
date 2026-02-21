<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeds initial payment methods with bilingual names.
     * 
     * Validates: Requirement 6.1
     */
    public function run(): void
    {
        $paymentMethods = [
            // Cash (for in-person payments at school)
            [
                'name' => 'Cash',
                'name_mm' => 'ငွေသား',
                'type' => 'bank', // Use 'bank' type since enum doesn't have 'cash'
                'account_number' => 'N/A',
                'account_name' => 'Smart Campus School',
                'account_name_mm' => 'စမတ်ကမ်းပတ်စ်ကျောင်း',
                'instructions' => 'Pay cash at school office',
                'instructions_mm' => 'ကျောင်းရုံးခန်းတွင် ငွေသားဖြင့်ပေးချေပါ',
                'is_active' => true,
                'logo_url' => '/images/payment-methods/cash.png',
                'sort_order' => 0, // Show first
            ],
            
            // Banks
            [
                'name' => 'KBZ Bank',
                'name_mm' => 'ကေဘီဇက်ဘဏ်',
                'type' => 'bank',
                'account_number' => '1234567890',
                'account_name' => 'Smart Campus School',
                'account_name_mm' => 'စမတ်ကမ်းပတ်စ်ကျောင်း',
                'instructions' => 'Transfer to this account and upload receipt',
                'instructions_mm' => 'ဤအကောင့်သို့ လွှဲပြောင်းပြီး ပြေစာတင်ပါ',
                'is_active' => true,
                'logo_url' => '/images/payment-methods/kbz.png',
                'sort_order' => 1,
            ],
            [
                'name' => 'CB Bank',
                'name_mm' => 'စီဘီဘဏ်',
                'type' => 'bank',
                'account_number' => '0987654321',
                'account_name' => 'Smart Campus School',
                'account_name_mm' => 'စမတ်ကမ်းပတ်စ်ကျောင်း',
                'instructions' => 'Transfer to this account and upload receipt',
                'instructions_mm' => 'ဤအကောင့်သို့ လွှဲပြောင်းပြီး ပြေစာတင်ပါ',
                'is_active' => true,
                'logo_url' => '/images/payment-methods/cb.png',
                'sort_order' => 2,
            ],
            [
                'name' => 'AYA Bank',
                'name_mm' => 'အေရာဘဏ်',
                'type' => 'bank',
                'account_number' => '1122334455',
                'account_name' => 'Smart Campus School',
                'account_name_mm' => 'စမတ်ကမ်းပတ်စ်ကျောင်း',
                'instructions' => 'Transfer to this account and upload receipt',
                'instructions_mm' => 'ဤအကောင့်သို့ လွှဲပြောင်းပြီး ပြေစာတင်ပါ',
                'is_active' => true,
                'logo_url' => '/images/payment-methods/aya.png',
                'sort_order' => 3,
            ],
            
            // Mobile Wallets
            [
                'name' => 'Wave Money',
                'name_mm' => 'ဝေ့ဗ်မနီ',
                'type' => 'mobile_wallet',
                'account_number' => '09123456789',
                'account_name' => 'Smart Campus School',
                'account_name_mm' => 'စမတ်ကမ်းပတ်စ်ကျောင်း',
                'instructions' => 'Send money to this number and upload receipt',
                'instructions_mm' => 'ဤနံပါတ်သို့ ငွေပို့ပြီး ပြေစာတင်ပါ',
                'is_active' => true,
                'logo_url' => '/images/payment-methods/wavemoney.png',
                'sort_order' => 4,
            ],
            [
                'name' => 'KBZ Pay',
                'name_mm' => 'ကေဘီဇက်ပေး',
                'type' => 'mobile_wallet',
                'account_number' => '09987654321',
                'account_name' => 'Smart Campus School',
                'account_name_mm' => 'စမတ်ကမ်းပတ်စ်ကျောင်း',
                'instructions' => 'Send money to this number and upload receipt',
                'instructions_mm' => 'ဤနံပါတ်သို့ ငွေပို့ပြီး ပြေစာတင်ပါ',
                'is_active' => true,
                'logo_url' => '/images/payment-methods/kbzpay.png',
                'sort_order' => 5,
            ],
            [
                'name' => 'CB Pay',
                'name_mm' => 'စီဘီပေး',
                'type' => 'mobile_wallet',
                'account_number' => '09111222333',
                'account_name' => 'Smart Campus School',
                'account_name_mm' => 'စမတ်ကမ်းပတ်စ်ကျောင်း',
                'instructions' => 'Send money to this number and upload receipt',
                'instructions_mm' => 'ဤနံပါတ်သို့ ငွေပို့ပြီး ပြေစာတင်ပါ',
                'is_active' => true,
                'logo_url' => '/images/payment-methods/cbpay.png',
                'sort_order' => 6,
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::updateOrCreate(
                ['name' => $method['name']],
                $method
            );
        }
    }
}
