<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ucfirst(str_replace('_', ' ', $reportType)) }} - Student Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.4; padding: 20px; }
        .page { page-break-after: always; padding: 20px; max-width: 210mm; margin: 0 auto; }
        .page:last-child { page-break-after: auto; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 15px; }
        .header h1 { font-size: 18pt; margin-bottom: 5px; }
        .header h2 { font-size: 14pt; font-weight: normal; }
        .header p { font-size: 10pt; color: #666; }
        .student-info { margin-bottom: 20px; }
        .student-info table { width: 100%; border-collapse: collapse; }
        .student-info td { padding: 5px 10px; }
        .student-info .label { font-weight: bold; width: 150px; }
        .content { margin-top: 20px; }
        .content h3 { font-size: 14pt; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        .placeholder { background: #f5f5f5; border: 2px dashed #ccc; padding: 40px; text-align: center; color: #999; margin: 20px 0; }
        .footer { margin-top: 40px; text-align: center; font-size: 10pt; color: #666; }
        .print-btn { position: fixed; top: 20px; right: 20px; padding: 10px 20px; background: #4F46E5; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; }
        .print-btn:hover { background: #4338CA; }
        @media print {
            .print-btn { display: none; }
            body { padding: 0; }
            .page { padding: 15mm; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">
        <i class="fas fa-print"></i> Print Report
    </button>

    @forelse($students as $student)
    <div class="page">
        <div class="header">
            <h1>{{ config('app.name', 'Smart Campus') }}</h1>
            <h2>
                @if($reportType === 'report_card')
                    Student Report Card
                @elseif($reportType === 'qcpr')
                    Quarterly Cumulative Progress Report (QCPR)
                @elseif($reportType === 'ccpr')
                    Continuous Cumulative Progress Report (CCPR)
                @endif
            </h2>
            @if($term)
                <p>{{ ucfirst(str_replace('_', ' ', $term)) }}</p>
            @endif
        </div>

        <div class="student-info">
            <table>
                <tr>
                    <td class="label">Student Name:</td>
                    <td>{{ $student->user->name ?? $student->name ?? 'N/A' }}</td>
                    <td class="label">Student ID:</td>
                    <td>{{ $student->student_id ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Class:</td>
                    <td>{{ $student->class->name ?? 'N/A' }}</td>
                    <td class="label">Grade:</td>
                    <td>{{ $student->class->grade->level ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>

        <div class="content">
            <div class="placeholder">
                <h3>{{ ucfirst(str_replace('_', ' ', $reportType)) }} Content</h3>
                <p>Report data and format will be added here.</p>
                <p style="margin-top: 10px; font-size: 10pt;">
                    Student ID: {{ $student->id }}<br>
                    Report Type: {{ $reportType }}<br>
                    @if($term) Term: {{ $term }} @endif
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
            <h3>No Students Found</h3>
            <p>No students match the selected criteria.</p>
        </div>
    </div>
    @endforelse
</body>
</html>
