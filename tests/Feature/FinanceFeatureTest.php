<?php

namespace Tests\Feature;

use App\Models\ExpenseCategory;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function enableFinance(): void
    {
        Setting::create([
            'setup_completed_finance' => true,
            'setup_completed_school_info' => true,
        ]);
    }

    public function test_income_and_expense_flow_is_accessible_and_lists_entries(): void
    {
        $this->enableFinance();

        $user = User::factory()->create([
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $category = ExpenseCategory::create([
            'name' => 'Supplies',
            'code' => 'SUPPLIES',
            'status' => true,
        ]);

        $this->actingAs($user)
            ->post(route('finance.income.store'), [
                'title' => 'Donation',
                'category' => 'Donations',
                'amount' => 150,
                'income_date' => '2025-01-15',
                'payment_method' => 'cash',
                'reference_number' => 'REF-1',
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('finance.expense.store'), [
                'title' => 'Supplies Purchase',
                'expense_category_id' => $category->id,
                'amount' => 50,
                'expense_date' => '2025-01-16',
                'payment_method' => 'cash',
            ])
            ->assertRedirect();

        $this->get(route('finance.index', ['period' => '2025-01']))
            ->assertOk()
            ->assertSee('Donation')
            ->assertSee('Supplies Purchase')
            ->assertSee('150 MMK')
            ->assertSee('50 MMK')
            ->assertSee('100 MMK'); // Net = 150 - 50
    }
}
