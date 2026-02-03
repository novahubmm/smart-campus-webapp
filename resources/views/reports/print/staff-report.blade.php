<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ucfirst($reportType) }} - Staff Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.4; padding: 20px; }
        .page { page-break-after: always; padding: 20px; max-width: 210mm; margin: 0 auto; }
        .page:last-child { page-break-after: auto; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 15px; }
        .header h1 { font-size: 18pt; margin-bottom: 5px; }
        .header h2 { font-size: 14pt; font-weight: normal; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 8px 10px; border-bottom: 1px solid #eee; }
        .info-table .label { font-weight: bold; width: 180px; background: #f9f9f9; }
        .content { margin-top: 20px; }
        .placeholder { background: #f5f5f5; border: 2px dashed #ccc; padding: 40px; text-align: center; color: #999; margin: 20px 0; }
        .footer { margin-top: 40px; text-align: center; font-size: 10pt; color: #666; }
        .print-btn { position: fixed; top: 20px; right: 20px; padding: 10px 20px; background: #EA580C; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; }
        .print-btn:hover { background: #C2410C; }
        @media print {
            .print-btn { display: none; }
            body { padding: 0; }
            .page { padding: 15mm; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Print Report</button>

    @forelse($staff as $member)
    <div class="page">
        <div class="header">
            <h1>{{ config('app.name', 'Smart Campus') }}</h1>
            <h2>Staff {{ ucfirst($reportType) }} Report</h2>
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Name:</td>
                <td>{{ $member->user->name ?? $member->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Employee ID:</td>
                <td>{{ $member->employee_id ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Position:</td>
                <td>{{ $member->position ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Department:</td>
                <td>{{ $member->department->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Email:</td>
                <td>{{ $member->user->email ?? 'N/A' }}</td>
            </tr>
        </table>

        <div class="content">
            <div class="placeholder">
                <h3>{{ ucfirst($reportType) }} Report Content</h3>
                <p>Report data and format will be added here.</p>
                <p style="margin-top: 10px; font-size: 10pt;">
                    Staff ID: {{ $member->id }}<br>
                    Report Type: {{ $reportType }}
                </p>
            </div>
        </div>

        <div class="footer">
            <p>Generated on {{ now()->format('F d, Y h:i A') }}</p>
        </div>
    </div>
    @empty
    <div class="page">
        <div class="placeholder">
            <h3>No Staff Found</h3>
            <p>No staff members match the selected criteria.</p>
        </div>
    </div>
    @endforelse
</body>
</html>
