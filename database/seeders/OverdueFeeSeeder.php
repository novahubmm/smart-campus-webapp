<?php

namespace Database\Seeders;

use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\StudentProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OverdueFeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates an overdue fee record for a student that will show in the 2026 payment summary
     */
    public function run(): void
    {
        $this->command->info('Creating overdue fee record for 2026...');

        // Get a student (using student1 as example, or you can specify any student)
        $studentUser = User::where('email', 'student1@smartcampusedu.com')
            ->whereHas('studentProfile')
            ->first();

        if (!$studentUser) {
            $this->command->error('Student not found! Please ensure student1@smartcampusedu.com exists.');
            return;
        }

        $studentProfile = $studentUser->studentProfile;

        // Delete existing 2026 invoices for this student (force delete to handle soft deletes)
        Invoice::where('student_id', $studentProfile->id)
            ->where('invoice_number', 'LIKE', 'INV-2026-%')
            ->forceDelete();

        $this->command->info('Cleared existing 2026 invoices...');

        // Get or create fee types
        $tuitionFeeType = FeeType::firstOrCreate(
            ['code' => 'TUITION'],
            [
                'name' => 'Tuition Fee',
                'description' => 'Monthly tuition fee',
                'is_mandatory' => true,
                'status' => true,
            ]
        );

        $examFeeType = FeeType::firstOrCreate(
            ['code' => 'EXAM'],
            [
                'name' => 'Examination Fee',
                'description' => 'Examination and assessment fee',
                'is_mandatory' => true,
                'status' => true,
            ]
        );

        // Create 1 overdue invoice (MMK 150,000)
        $overdueInvoice = Invoice::create([
            'invoice_number' => 'INV-2026-01-' . strtoupper(substr($studentProfile->id, 0, 8)),
            'student_id' => $studentProfile->id,
            'invoice_date' => Carbon::create(2026, 1, 1),
            'due_date' => Carbon::create(2026, 1, 15), // Past due date
            'subtotal' => 150000.00,
            'discount' => 0.00,
            'total_amount' => 150000.00,
            'paid_amount' => 0.00,
            'balance' => 150000.00,
            'status' => 'overdue',
            'notes' => 'January 2026 school fees - OVERDUE',
            'created_by' => $studentUser->id,
        ]);

        InvoiceItem::create([
            'invoice_id' => $overdueInvoice->id,
            'fee_type_id' => $tuitionFeeType->id,
            'description' => 'Tuition Fee - January 2026',
            'quantity' => 1,
            'unit_price' => 100000.00,
            'amount' => 100000.00,
        ]);

        InvoiceItem::create([
            'invoice_id' => $overdueInvoice->id,
            'fee_type_id' => $examFeeType->id,
            'description' => 'Examination Fee - January 2026',
            'quantity' => 1,
            'unit_price' => 50000.00,
            'amount' => 50000.00,
        ]);

        // Create 3 pending invoices (total MMK 450,000)
        $pendingInvoices = [];
        
        // Pending Invoice 1 - February (MMK 150,000)
        $pendingInvoices[] = Invoice::create([
            'invoice_number' => 'INV-2026-02-' . strtoupper(substr($studentProfile->id, 0, 8)),
            'student_id' => $studentProfile->id,
            'invoice_date' => Carbon::create(2026, 2, 1),
            'due_date' => Carbon::create(2026, 2, 28), // Future due date
            'subtotal' => 150000.00,
            'discount' => 0.00,
            'total_amount' => 150000.00,
            'paid_amount' => 0.00,
            'balance' => 150000.00,
            'status' => 'sent', // Using 'sent' for pending invoices
            'notes' => 'February 2026 school fees',
            'created_by' => $studentUser->id,
        ]);

        InvoiceItem::create([
            'invoice_id' => $pendingInvoices[0]->id,
            'fee_type_id' => $tuitionFeeType->id,
            'description' => 'Tuition Fee - February 2026',
            'quantity' => 1,
            'unit_price' => 100000.00,
            'amount' => 100000.00,
        ]);

        InvoiceItem::create([
            'invoice_id' => $pendingInvoices[0]->id,
            'fee_type_id' => $examFeeType->id,
            'description' => 'Examination Fee - February 2026',
            'quantity' => 1,
            'unit_price' => 50000.00,
            'amount' => 50000.00,
        ]);

        // Pending Invoice 2 - March (MMK 150,000)
        $pendingInvoices[] = Invoice::create([
            'invoice_number' => 'INV-2026-03-' . strtoupper(substr($studentProfile->id, 0, 8)),
            'student_id' => $studentProfile->id,
            'invoice_date' => Carbon::create(2026, 3, 1),
            'due_date' => Carbon::create(2026, 3, 31),
            'subtotal' => 150000.00,
            'discount' => 0.00,
            'total_amount' => 150000.00,
            'paid_amount' => 0.00,
            'balance' => 150000.00,
            'status' => 'sent', // Using 'sent' for pending invoices
            'notes' => 'March 2026 school fees',
            'created_by' => $studentUser->id,
        ]);

        InvoiceItem::create([
            'invoice_id' => $pendingInvoices[1]->id,
            'fee_type_id' => $tuitionFeeType->id,
            'description' => 'Tuition Fee - March 2026',
            'quantity' => 1,
            'unit_price' => 100000.00,
            'amount' => 100000.00,
        ]);

        InvoiceItem::create([
            'invoice_id' => $pendingInvoices[1]->id,
            'fee_type_id' => $examFeeType->id,
            'description' => 'Examination Fee - March 2026',
            'quantity' => 1,
            'unit_price' => 50000.00,
            'amount' => 50000.00,
        ]);

        // Pending Invoice 3 - April (MMK 150,000)
        $pendingInvoices[] = Invoice::create([
            'invoice_number' => 'INV-2026-04-' . strtoupper(substr($studentProfile->id, 0, 8)),
            'student_id' => $studentProfile->id,
            'invoice_date' => Carbon::create(2026, 4, 1),
            'due_date' => Carbon::create(2026, 4, 30),
            'subtotal' => 150000.00,
            'discount' => 0.00,
            'total_amount' => 150000.00,
            'paid_amount' => 0.00,
            'balance' => 150000.00,
            'status' => 'sent', // Using 'sent' for pending invoices
            'notes' => 'April 2026 school fees',
            'created_by' => $studentUser->id,
        ]);

        InvoiceItem::create([
            'invoice_id' => $pendingInvoices[2]->id,
            'fee_type_id' => $tuitionFeeType->id,
            'description' => 'Tuition Fee - April 2026',
            'quantity' => 1,
            'unit_price' => 100000.00,
            'amount' => 100000.00,
        ]);

        InvoiceItem::create([
            'invoice_id' => $pendingInvoices[2]->id,
            'fee_type_id' => $examFeeType->id,
            'description' => 'Examination Fee - April 2026',
            'quantity' => 1,
            'unit_price' => 50000.00,
            'amount' => 50000.00,
        ]);

        $this->command->info('✅ Successfully created fee records for 2026');
        $this->command->info("   Student: {$studentUser->name} ({$studentUser->email})");
        $this->command->info("   Student ID: {$studentProfile->id}");
        $this->command->info('');
        $this->command->info('   📌 Overdue (1 invoice):');
        $this->command->info("      - {$overdueInvoice->invoice_number}: MMK 150,000 (Due: {$overdueInvoice->due_date->format('Y-m-d')})");
        $this->command->info('');
        $this->command->info('   📌 Pending (3 invoices):');
        foreach ($pendingInvoices as $invoice) {
            $this->command->info("      - {$invoice->invoice_number}: MMK 150,000 (Due: {$invoice->due_date->format('Y-m-d')})");
        }
        $this->command->info('');
        $this->command->info("   Total Overdue: MMK 150,000");
        $this->command->info("   Total Pending: MMK 450,000");
        $this->command->info('');
        $this->command->info('📝 Test the API with:');
        $this->command->info("   GET {{base_url}}/guardian/students/{$studentProfile->id}/fees/summary?year=2026");
    }
}
