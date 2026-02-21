# Requirements Document

## Introduction

This document specifies the requirements for a payment system feature within the Smart Campus school management platform. The system enables parents and guardians to view invoices, make full or partial payments with flexible payment periods, upload payment receipts, and track payment history. The system includes a payment verification workflow where administrators review and approve submitted payments.

## Glossary

- **Payment_System**: The backend Laravel application that processes payment transactions, manages invoices, and handles payment verification
- **Invoice**: A billing document containing one or more fees that a student must pay
- **Invoice_Fee**: An individual fee item within an invoice (e.g., tuition, transportation)
- **Payment**: A transaction record representing money submitted by a parent/guardian
- **Payment_Method**: A bank account or mobile wallet where payments can be sent
- **Payment_Period**: The number of months (1, 3, 6, or 12) for which a payment is made
- **Payment_Proof**: A receipt image uploaded by the guardian as evidence of payment
- **Partial_Payment**: A payment that covers less than the full invoice amount
- **Full_Payment**: A payment that covers the complete invoice amount
- **Payment_Verification**: The administrative process of reviewing and approving/rejecting submitted payments
- **Fee_Structure**: Master definition of a fee type with its base amount and properties
- **Fee_Category**: A type of fee defined by the school (e.g., tuition, transportation, course materials, sports)
- **Fee_Frequency**: How often a fee is charged - either "one_time" or "monthly"
- **One_Time_Fee**: A fee charged once during a specific month (e.g., special sports fee for March), generates a separate invoice
- **Monthly_Fee**: A fee charged every month (e.g., tuition, transportation), auto-generated at month start
- **Discount_Rate**: Percentage reduction applied based on payment period (3 months = 5%, 6 months = 10%, 12 months = 15%), only applies to monthly fees
- **Guardian**: The authenticated user (parent or guardian) making payments for a student
- **Admin**: The administrative user who verifies payment submissions and manages fee structures
- **Grade**: A student's academic level (e.g., Grade 1, Grade 2)
- **Batch**: The academic year or cohort (e.g., 2024-2025)
- **Academic_Year**: The school year period (e.g., 2024-2025)

## Requirements

### Requirement 1: Fee Category Management

**User Story:** As a school admin, I want to create fee categories with different frequencies for specific grades and batches, so that I can define the fees students must pay.

#### Acceptance Criteria

1. WHEN an admin creates a fee category, THE Payment_System SHALL accept name, description, amount, frequency, fee type, grade, batch, and target month (for one-time fees)
2. WHEN creating a fee category, THE Payment_System SHALL validate that frequency is either "one_time" or "monthly"
3. WHEN creating a fee category, THE Payment_System SHALL validate that fee type is one of: tuition, transportation, library, lab, sports, course_materials, or other
4. WHEN creating a monthly fee category, THE Payment_System SHALL allow specifying whether the fee supports payment periods
5. WHEN creating a one-time fee category, THE Payment_System SHALL not allow payment period support
6. WHEN creating a monthly fee category, THE Payment_System SHALL not generate invoices immediately
7. WHEN creating a one-time fee category, THE Payment_System SHALL immediately generate invoices for the target month
8. THE Payment_System SHALL support bilingual names and descriptions for fee categories
9. THE Payment_System SHALL allow multiple fee categories with the same name for different grades or batches

### Requirement 2: Automatic Invoice Generation for One-Time Fees

**User Story:** As a school admin, I want one-time fee invoices to be automatically created as separate invoices for all students in the specified grade when I create the fee, so that guardians can see and pay them independently.

#### Acceptance Criteria

1. WHEN an admin creates a one-time fee category for a specific month and grade, THE Payment_System SHALL automatically generate separate invoices for all students in that grade
2. WHEN generating invoices for one-time fees, THE Payment_System SHALL create a new invoice containing only that one-time fee
3. WHEN an admin creates multiple one-time fees for the same month and grade, THE Payment_System SHALL create separate invoices for each one-time fee
4. WHEN generating one-time fee invoices, THE Payment_System SHALL create invoice_fees records linking the invoice to the fee
5. WHEN generating one-time fee invoices, THE Payment_System SHALL set the invoice due date based on school configuration
6. WHEN generating one-time fee invoices, THE Payment_System SHALL set the initial status to pending

### Requirement 3: Automatic Monthly Invoice Generation

**User Story:** As a school admin, I want monthly fees to be automatically combined into one invoice per student at the start of each month, so that guardians receive a single consolidated monthly invoice.

#### Acceptance Criteria

1. WHEN a new month begins, THE Payment_System SHALL automatically generate monthly invoices for all active students
2. WHEN generating monthly invoices, THE Payment_System SHALL combine all monthly fee categories applicable to each student's grade into a single invoice
3. WHEN generating monthly invoices, THE Payment_System SHALL only include monthly fee categories, not one-time fees
4. WHEN generating monthly invoices, THE Payment_System SHALL create one invoice_fees record for each monthly fee category
5. WHEN generating monthly invoices, THE Payment_System SHALL calculate the total invoice amount as the sum of all included monthly fees
6. WHEN generating monthly invoices, THE Payment_System SHALL set the due date to a configured number of days from the invoice creation date
7. IF a student already has a monthly invoice for the current month, THEN THE Payment_System SHALL not create a duplicate invoice
8. WHEN generating monthly invoices, THE Payment_System SHALL set the initial status to pending
9. WHEN a new monthly fee category is created, THE Payment_System SHALL include it in the next month's invoice generation, not the current month

### Requirement 4: Invoice Consolidation and Separation

**User Story:** As a guardian, I want to see separate invoices for monthly fees and one-time fees, so that I can understand which fees are recurring and which are special charges.

#### Acceptance Criteria

1. WHEN a guardian views invoices, THE Payment_System SHALL display monthly fee invoices separately from one-time fee invoices
2. WHEN displaying a monthly invoice, THE Payment_System SHALL show all monthly fee types with their names and amounts
3. WHEN displaying a one-time fee invoice, THE Payment_System SHALL show only the specific one-time fee
4. THE Payment_System SHALL clearly indicate whether an invoice contains monthly fees or a one-time fee
5. WHEN a guardian has multiple invoices for the same month, THE Payment_System SHALL display them as separate line items

### Requirement 5: Invoice Retrieval

**User Story:** As a guardian, I want to view all invoices for my student with detailed fee breakdowns, so that I understand what payments are due.

#### Acceptance Criteria

1. WHEN a guardian requests invoices for a student, THE Payment_System SHALL return all invoices with their associated fees
2. WHEN displaying an invoice, THE Payment_System SHALL include the invoice number, total amount, paid amount, remaining amount, due date, status, and invoice type (monthly or one-time)
3. WHEN displaying invoice fees, THE Payment_System SHALL include the fee name (in English and Myanmar), amount, paid amount, remaining amount, payment period support flag, and status
4. WHERE a status filter is provided, THE Payment_System SHALL return only invoices matching that status
5. WHERE an academic year filter is provided, THE Payment_System SHALL return only invoices for that academic year
6. THE Payment_System SHALL calculate and return the total count of invoices, pending count, and overdue count
7. WHEN an invoice due date has passed and the invoice is not fully paid, THE Payment_System SHALL mark the invoice status as overdue

### Requirement 6: Payment Method Management

**User Story:** As a guardian, I want to see all available payment methods with their details, so that I can choose how to send my payment.

#### Acceptance Criteria

1. WHEN a guardian requests payment methods, THE Payment_System SHALL return all active payment methods
2. WHEN displaying a payment method, THE Payment_System SHALL include the name (in English and Myanmar), type, account number, account name, logo URL, and instructions
3. WHERE a type filter is provided, THE Payment_System SHALL return only payment methods of that type
4. THE Payment_System SHALL order payment methods by their sort_order field
5. THE Payment_System SHALL only return payment methods where is_active is true

### Requirement 7: Payment Period Options

**User Story:** As a guardian, I want to see available payment period options with discount rates, so that I can choose the most beneficial payment plan for monthly fees.

#### Acceptance Criteria

1. WHEN a guardian requests payment options, THE Payment_System SHALL return all active payment period options
2. WHEN displaying a payment option, THE Payment_System SHALL include the number of months, discount percentage, label (in English and Myanmar), badge text, and default flag
3. THE Payment_System SHALL order payment options by their sort_order field
4. THE Payment_System SHALL indicate which payment option is the default
5. THE Payment_System SHALL return the maximum available payment period in months
6. THE Payment_System SHALL indicate that payment periods only apply to monthly fees

### Requirement 8: Payment Period Calculation

**User Story:** As a guardian, I want monthly fees that support payment periods to be multiplied by the selected months with appropriate discounts, so that I can pay for multiple months at once.

#### Acceptance Criteria

1. WHEN a fee has supports_payment_period set to true and frequency is "monthly", THE Payment_System SHALL multiply the fee amount by the selected payment period months
2. WHEN a fee has supports_payment_period set to false or frequency is "one_time", THE Payment_System SHALL use the base fee amount regardless of selected payment period
3. WHEN a payment period of 3 months is selected for monthly fees, THE Payment_System SHALL apply a 5% discount
4. WHEN a payment period of 6 months is selected for monthly fees, THE Payment_System SHALL apply a 10% discount
5. WHEN a payment period of 12 months is selected for monthly fees, THE Payment_System SHALL apply a 15% discount
6. WHEN a payment period of 1 month is selected, THE Payment_System SHALL apply no discount
7. THE Payment_System SHALL calculate the final fee amount as: (base_amount × months) - (base_amount × months × discount_rate)
8. THE Payment_System SHALL only apply discount rates to monthly fees, not one-time fees

### Requirement 9: Payment Submission

**User Story:** As a guardian, I want to submit payment proof with a receipt image, so that the school can verify my payment.

#### Acceptance Criteria

1. WHEN a guardian submits a payment, THE Payment_System SHALL accept invoice IDs, payment method ID, payment amount, payment type, payment months, payment date, receipt image, and fee payment details
2. WHEN a payment is submitted, THE Payment_System SHALL validate that the receipt image is in JPEG or PNG format
3. WHEN a payment is submitted, THE Payment_System SHALL validate that the receipt image size does not exceed 5MB
4. WHEN a payment is submitted, THE Payment_System SHALL upload the receipt image to cloud storage and generate a URL
5. WHEN a payment is submitted, THE Payment_System SHALL create a payment record with status pending_verification
6. WHEN a payment is submitted, THE Payment_System SHALL generate a unique payment number
7. WHEN a payment is submitted, THE Payment_System SHALL create payment_fee_details records for each fee in the payment
8. WHEN a payment is submitted, THE Payment_System SHALL return the payment ID, payment number, status, submission timestamp, verification ETA message, receipt URL, and payment details
9. WHEN a guardian submits payment for a monthly invoice with payment period greater than 1, THE Payment_System SHALL allow advance payment for multiple months

### Requirement 10: Partial Payment Validation

**User Story:** As a guardian, I want to make partial payments on my invoices, so that I can pay what I can afford now and pay the rest later.

#### Acceptance Criteria

1. WHEN a guardian submits a partial payment, THE Payment_System SHALL validate that each fee payment is at least 5,000 MMK
2. WHEN a guardian submits a partial payment, THE Payment_System SHALL validate that the total payment is at least 10,000 MMK
3. WHEN a guardian submits a partial payment, THE Payment_System SHALL validate that no fee payment exceeds the fee's remaining amount
4. WHEN a guardian submits a partial payment, THE Payment_System SHALL validate that at least one fee is included in the payment
5. IF any validation fails, THEN THE Payment_System SHALL return a 422 error with specific validation messages

### Requirement 11: Invoice Update After Payment

**User Story:** As a guardian, I want invoice amounts to be updated after I submit a payment, so that I can see my remaining balance.

#### Acceptance Criteria

1. WHEN a payment is submitted, THE Payment_System SHALL update each invoice_fee's paid_amount by adding the payment amount for that fee
2. WHEN a payment is submitted, THE Payment_System SHALL recalculate each invoice_fee's remaining_amount as amount minus paid_amount
3. WHEN an invoice_fee's remaining_amount reaches zero, THE Payment_System SHALL update the invoice_fee status to paid
4. WHEN an invoice_fee's paid_amount is greater than zero but less than the full amount, THE Payment_System SHALL update the invoice_fee status to partial
5. WHEN a payment is submitted, THE Payment_System SHALL recalculate the invoice's paid_amount as the sum of all invoice_fees paid_amounts
6. WHEN a payment is submitted, THE Payment_System SHALL recalculate the invoice's remaining_amount as total_amount minus paid_amount
7. WHEN an invoice's remaining_amount reaches zero, THE Payment_System SHALL update the invoice status to paid
8. WHEN an invoice's paid_amount is greater than zero but less than the total amount, THE Payment_System SHALL update the invoice status to partial
9. THE Payment_System SHALL perform all payment submission operations within a database transaction

### Requirement 12: Payment History

**User Story:** As a guardian, I want to view my payment history, so that I can track all payments I have made.

#### Acceptance Criteria

1. WHEN a guardian requests payment history, THE Payment_System SHALL return all payments for the specified student
2. WHEN displaying payment history, THE Payment_System SHALL include payment number, payment date, payment amount, payment type, payment months, payment method name, status, submission timestamp, verification timestamp, receipt URL, notes, and rejection reason
3. WHERE a status filter is provided, THE Payment_System SHALL return only payments matching that status
4. THE Payment_System SHALL paginate payment history results with configurable page size
5. THE Payment_System SHALL order payment history by payment date in descending order
6. WHEN displaying each payment, THE Payment_System SHALL include the fee breakdown showing fee name, full amount, paid amount, remaining amount, and partial flag

### Requirement 13: Payment Verification Workflow

**User Story:** As an admin, I want to verify or reject submitted payments with notifications sent to guardians, so that guardians are informed of payment status.

#### Acceptance Criteria

1. WHEN a payment is initially submitted, THE Payment_System SHALL set the payment status to pending_verification
2. WHEN an admin verifies a payment, THE Payment_System SHALL update the payment status to verified
3. WHEN an admin verifies a payment, THE Payment_System SHALL record the verification timestamp and the admin user ID
4. WHEN an admin verifies a payment, THE Payment_System SHALL send a notification to the guardian's mobile app
5. WHEN an admin rejects a payment, THE Payment_System SHALL update the payment status to rejected
6. WHEN an admin rejects a payment, THE Payment_System SHALL store the rejection reason
7. WHEN an admin rejects a payment, THE Payment_System SHALL send a notification to the guardian's mobile app including the rejection reason
8. WHEN an admin rejects a payment, THE Payment_System SHALL rollback the invoice_fees paid_amount updates
9. WHEN an admin rejects a payment, THE Payment_System SHALL rollback the invoice paid_amount updates
10. WHEN an admin rejects a payment, THE Payment_System SHALL recalculate invoice and invoice_fee statuses

### Requirement 14: Automatic Remaining Balance Invoice Generation

**User Story:** As a guardian, I want remaining balances from partial payments to automatically create new invoices, so that I can track and pay what I still owe.

#### Acceptance Criteria

1. WHEN a guardian makes a partial payment on an invoice, THE Payment_System SHALL check if there is a remaining balance
2. WHEN a remaining balance exists after a partial payment, THE Payment_System SHALL automatically create a new invoice for the remaining amount
3. WHEN creating a remaining balance invoice, THE Payment_System SHALL include only the fees with remaining amounts
4. WHEN creating a remaining balance invoice, THE Payment_System SHALL set the due date based on school configuration
5. WHEN creating a remaining balance invoice, THE Payment_System SHALL link it to the original invoice for tracking
6. WHEN creating a remaining balance invoice, THE Payment_System SHALL set the status to pending
7. THE Payment_System SHALL mark the original invoice as paid once the partial payment is verified

### Requirement 15: Fee Category Due Date Management

**User Story:** As a school admin, I want to set due dates for each fee category, so that guardians know when payments must be completed.

#### Acceptance Criteria

1. WHEN an admin creates a fee category, THE Payment_System SHALL accept a due_date field
2. WHEN generating invoices from fee categories, THE Payment_System SHALL use the fee category's due date as the invoice due date
3. THE Payment_System SHALL store the due date for each invoice_fee based on its fee category
4. WHEN displaying invoices, THE Payment_System SHALL show the due date for each fee
5. THE Payment_System SHALL allow different due dates for different fee categories within the same invoice

### Requirement 16: Partial Payment Restrictions Based on Due Date

**User Story:** As a school admin, I want to prevent partial payments on or after the due date, so that guardians pay in full when fees are due.

#### Acceptance Criteria

1. WHEN a guardian attempts a partial payment, THE Payment_System SHALL check the current date against each fee's due date
2. WHEN the current date is on or after a fee's due date, THE Payment_System SHALL not allow partial payment for that fee
3. WHEN the current date is on or after a fee's due date, THE Payment_System SHALL require full payment for that fee
4. IF a guardian attempts partial payment on an overdue fee, THEN THE Payment_System SHALL return a 422 error with message indicating full payment is required
5. WHEN an invoice contains multiple fees with different due dates, THE Payment_System SHALL allow partial payment only for fees that are not yet due
6. WHEN all fees in an invoice are due or overdue, THE Payment_System SHALL require full payment of the entire invoice

### Requirement 17: Authentication and Authorization

**User Story:** As a system administrator, I want all payment endpoints to be authenticated and authorized, so that only valid users can access payment data.

#### Acceptance Criteria

1. WHEN a request is made to any payment endpoint, THE Payment_System SHALL validate the JWT authentication token
2. IF the authentication token is invalid or missing, THEN THE Payment_System SHALL return a 401 Unauthorized error
3. WHEN a guardian requests invoices or payments, THE Payment_System SHALL verify that the student belongs to the authenticated guardian
4. IF a guardian attempts to access data for a student they are not associated with, THEN THE Payment_System SHALL return a 403 Forbidden error
5. WHEN an admin performs payment verification, THE Payment_System SHALL verify that the user has admin privileges

### Requirement 18: Error Handling and Validation

**User Story:** As a developer, I want comprehensive error handling with bilingual messages, so that users receive clear feedback in their preferred language.

#### Acceptance Criteria

1. WHEN a validation error occurs, THE Payment_System SHALL return a 422 Unprocessable Entity status with error details
2. WHEN a resource is not found, THE Payment_System SHALL return a 404 Not Found status with a descriptive message
3. WHEN an authentication error occurs, THE Payment_System SHALL return a 401 Unauthorized status
4. WHEN a server error occurs, THE Payment_System SHALL return a 500 Internal Server Error status
5. THE Payment_System SHALL include both English and Myanmar language messages in all error responses
6. THE Payment_System SHALL include a timestamp and error code in all error responses
7. THE Payment_System SHALL include field-specific validation errors in the errors object

### Requirement 19: Data Integrity and Transactions

**User Story:** As a system administrator, I want all payment operations to maintain data integrity, so that the system remains consistent even during failures.

#### Acceptance Criteria

1. WHEN a payment submission involves multiple database operations, THE Payment_System SHALL execute all operations within a single database transaction
2. IF any operation in a payment submission fails, THEN THE Payment_System SHALL rollback all changes
3. WHEN updating invoice amounts, THE Payment_System SHALL ensure that paid_amount never exceeds total_amount
4. WHEN updating invoice_fee amounts, THE Payment_System SHALL ensure that paid_amount never exceeds the fee amount
5. THE Payment_System SHALL use database constraints to prevent invalid data states
6. THE Payment_System SHALL use database indexes on foreign keys and frequently queried columns

### Requirement 20: File Storage and Management

**User Story:** As a guardian, I want my receipt images to be stored securely and accessible, so that I can reference them later if needed.

#### Acceptance Criteria

1. WHEN a receipt image is uploaded, THE Payment_System SHALL store it in cloud storage
2. WHEN a receipt image is uploaded, THE Payment_System SHALL generate a unique filename to prevent collisions
3. WHEN a receipt image is uploaded, THE Payment_System SHALL return a publicly accessible URL
4. THE Payment_System SHALL validate receipt image format before upload
5. THE Payment_System SHALL validate receipt image size before upload
6. IF image upload fails, THEN THE Payment_System SHALL rollback the entire payment submission transaction

### Requirement 21: Bilingual Support

**User Story:** As a guardian, I want to see all payment information in both English and Myanmar languages, so that I can understand the information in my preferred language.

#### Acceptance Criteria

1. WHEN returning payment methods, THE Payment_System SHALL include both name and name_mm fields
2. WHEN returning payment options, THE Payment_System SHALL include both label and label_mm fields
3. WHEN returning invoice fees, THE Payment_System SHALL include both name and name_mm fields
4. WHEN returning error messages, THE Payment_System SHALL include both message and message_mm fields
5. WHEN returning payment status, THE Payment_System SHALL include both status and status_mm fields

### Requirement 22: Duplicate Payment Prevention

**User Story:** As a system administrator, I want to prevent duplicate payment submissions, so that guardians are not charged multiple times for the same payment.

#### Acceptance Criteria

1. WHEN a payment is submitted, THE Payment_System SHALL check for duplicate submissions within the last 5 minutes
2. IF a duplicate payment is detected, THEN THE Payment_System SHALL return a 422 error with error code DUPLICATE_PAYMENT
3. THE Payment_System SHALL consider payments duplicate if they have the same student_id, invoice_id, payment_amount, and payment_date
4. THE Payment_System SHALL use database unique constraints on payment_number to prevent duplicates

### Requirement 23: Notification System

**User Story:** As an admin, I want to be notified when new payments are submitted, so that I can verify them promptly.

#### Acceptance Criteria

1. WHEN a payment is submitted, THE Payment_System SHALL create a notification for admin users
2. WHEN a payment is verified, THE Payment_System SHALL create a notification for the guardian
3. WHEN a payment is rejected, THE Payment_System SHALL create a notification for the guardian including the rejection reason
4. THE Payment_System SHALL include payment details in all notifications
5. THE Payment_System SHALL support bilingual notification messages
