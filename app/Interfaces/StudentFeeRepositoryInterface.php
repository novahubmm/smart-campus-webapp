<?php

namespace App\Interfaces;

use App\DTOs\Finance\FeeFilterData;
use App\DTOs\Finance\FeeStructureData;
use App\DTOs\Finance\InvoiceData;
use App\DTOs\Finance\PaymentData;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\PaymentSystem\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface StudentFeeRepositoryInterface
{
    public function listInvoices(FeeFilterData $filter): LengthAwarePaginator;

    public function listPayments(FeeFilterData $filter): LengthAwarePaginator;

    public function listStructures(): Collection;

    public function createStructure(FeeStructureData $data): FeeStructure;

    public function updateStructure(FeeStructure $structure, FeeStructureData $data): FeeStructure;

    public function deleteStructure(FeeStructure $structure): void;

    public function createInvoice(InvoiceData $data, ?string $createdBy = null): Invoice;

    public function updateInvoice(Invoice $invoice, InvoiceData $data): Invoice;

    public function createPayment(PaymentData $data): Payment;
}
