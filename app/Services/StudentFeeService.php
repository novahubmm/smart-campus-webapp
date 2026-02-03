<?php

namespace App\Services;

use App\DTOs\Finance\FeeFilterData;
use App\DTOs\Finance\FeeStructureData;
use App\DTOs\Finance\InvoiceData;
use App\DTOs\Finance\PaymentData;
use App\Interfaces\StudentFeeRepositoryInterface;
use App\Models\FeeStructure;
use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StudentFeeService
{
    public function __construct(private readonly StudentFeeRepositoryInterface $repository) {}

    public function invoices(FeeFilterData $filter): LengthAwarePaginator
    {
        return $this->repository->listInvoices($filter);
    }

    public function payments(FeeFilterData $filter): LengthAwarePaginator
    {
        return $this->repository->listPayments($filter);
    }

    public function structures(): Collection
    {
        return $this->repository->listStructures();
    }

    public function createStructure(FeeStructureData $data)
    {
        return $this->repository->createStructure($data);
    }

    public function updateStructure(FeeStructure $structure, FeeStructureData $data)
    {
        return $this->repository->updateStructure($structure, $data);
    }

    public function deleteStructure(FeeStructure $structure): void
    {
        $this->repository->deleteStructure($structure);
    }

    public function createInvoice(InvoiceData $data, ?string $createdBy = null)
    {
        return $this->repository->createInvoice($data, $createdBy);
    }

    public function updateInvoice(Invoice $invoice, InvoiceData $data)
    {
        return $this->repository->updateInvoice($invoice, $data);
    }

    public function createPayment(PaymentData $data)
    {
        return $this->repository->createPayment($data);
    }
}
