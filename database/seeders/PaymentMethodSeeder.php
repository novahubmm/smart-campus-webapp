<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $paymentMethods = [
            [
                'id' => Str::uuid(),
                'name' => 'KBZ Bank',
                'name_mm' => 'KBZ ဘဏ်',
                'type' => 'bank',
                'account_number' => '01234567890123456',
                'account_name' => 'SmartCampus School',
                'account_name_mm' => 'SmartCampus ကျောင်း',
                'logo_url' => '/images/payment-methods/kbz.png',
                'is_active' => true,
                'instructions' => 'Transfer to this account and upload receipt',
                'instructions_mm' => 'ဒီ account ကို လွှဲပြီး ပြေစာ upload လုပ်ပါ',
                'sort_order' => 1,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'AYA Bank',
                'name_mm' => 'AYA ဘဏ်',
                'type' => 'bank',
                'account_number' => '98765432109876543',
                'account_name' => 'SmartCampus School',
                'account_name_mm' => 'SmartCampus ကျောင်း',
                'logo_url' => '/images/payment-methods/aya.png',
                'is_active' => true,
                'instructions' => 'Transfer to this account and upload receipt',
                'instructions_mm' => 'ဒီ account ကို လွှဲပြီး ပြေစာ upload လုပ်ပါ',
                'sort_order' => 2,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'CB Bank',
                'name_mm' => 'CB ဘဏ်',
                'type' => 'bank',
                'account_number' => '11223344556677889',
                'account_name' => 'SmartCampus School',
                'account_name_mm' => 'SmartCampus ကျောင်း',
                'logo_url' => '/images/payment-methods/cb.png',
                'is_active' => true,
                'instructions' => 'Transfer to this account and upload receipt',
                'instructions_mm' => 'ဒီ account ကို လွှဲပြီး ပြေစာ upload လုပ်ပါ',
                'sort_order' => 3,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'KBZPay',
                'name_mm' => 'KBZPay',
                'type' => 'mobile_wallet',
                'account_number' => '09-123-456-789',
                'account_name' => 'SmartCampus School',
                'account_name_mm' => 'SmartCampus ကျောင်း',
                'logo_url' => '/images/payment-methods/kbzpay.png',
                'is_active' => true,
                'instructions' => 'Send money to this number and upload screenshot',
                'instructions_mm' => 'ဒီနံပါတ်ကို ပိုက်ဆံပို့ပြီး screenshot upload လုပ်ပါ',
                'sort_order' => 4,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Wave Pay',
                'name_mm' => 'Wave Pay',
                'type' => 'mobile_wallet',
                'account_number' => '09-987-654-321',
                'account_name' => 'SmartCampus School',
                'account_name_mm' => 'SmartCampus ကျောင်း',
                'logo_url' => '/images/payment-methods/wavepay.png',
                'is_active' => true,
                'instructions' => 'Send money to this number and upload screenshot',
                'instructions_mm' => 'ဒီနံပါတ်ကို ပိုက်ဆံပို့ပြီး screenshot upload လုပ်ပါ',
                'sort_order' => 5,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'AYA Pay',
                'name_mm' => 'AYA Pay',
                'type' => 'mobile_wallet',
                'account_number' => '09-111-222-333',
                'account_name' => 'SmartCampus School',
                'account_name_mm' => 'SmartCampus ကျောင်း',
                'logo_url' => '/images/payment-methods/ayapay.png',
                'is_active' => true,
                'instructions' => 'Send money to this number and upload screenshot',
                'instructions_mm' => 'ဒီနံပါတ်ကို ပိုက်ဆံပို့ပြီး screenshot upload လုပ်ပါ',
                'sort_order' => 6,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'CB Pay',
                'name_mm' => 'CB Pay',
                'type' => 'mobile_wallet',
                'account_number' => '09-444-555-666',
                'account_name' => 'SmartCampus School',
                'account_name_mm' => 'SmartCampus ကျောင်း',
                'logo_url' => '/images/payment-methods/cbpay.png',
                'is_active' => true,
                'instructions' => 'Send money to this number and upload screenshot',
                'instructions_mm' => 'ဒီနံပါတ်ကို ပိုက်ဆံပို့ပြီး screenshot upload လုပ်ပါ',
                'sort_order' => 7,
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::create($method);
        }

        $this->command->info('Payment methods seeded successfully!');
    }
}
