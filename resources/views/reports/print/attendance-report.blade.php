<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if($reportType === 'pr') Period Register
        @elseif($reportType === 'dar') Daily Attendance Register
        @else Monthly Attendance Register
        @endif
    </title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', serif; font-size: 11pt; line-height: 1.3; padding: 15px; }
        .page { page-break-after: always; max-width: 297mm; margin: 0 auto; }
        .page:last-child { page-break-after: auto; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { font-size: 16pt; margin-bottom: 3px; }
        .header h2 { font-size: 13pt; font-weight: normal; margin-bottom: 5px; }
        .header .meta { font-size: 10pt; color: #333; }
        .class-info { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 10pt; }
        .class-info div { flex: 1; }
        table.register { width: 100%; border-collapse: collapse; font-size: 9pt; }
        table.register th, table.register td { border: 1px solid #000; padding: 4px 6px; text-align: center; }
        table.register th { background: #f0f0f0; font-weight: bold; }
        table.register .name-col { text-align: left; min-width: 150px; }
        table.register .roll-col { width: 40px; }
        .placeholder { background: #f5f5f5; border: 2px dashed #ccc; padding: 60px; text-align: center; color: #999; margin: 20px 0; }
        .footer { margin-top: 30px; font-size: 9pt; }
        .footer .signatures { display: flex; justify-content: space-between; margin-top: 40px; }
        .footer .sig-box { text-align: center; width: 150px; }
        .footer .sig-line { border-top: 1px solid #000; margin-top: 40px; padding-top: 5px; }
        .print-btn { position: fixed; top: 20px; right: 20px; padding: 10px 20px; background: #7C3AED; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; z-index: 100; }
        .print-btn:hover { background: #6D28D9; }
        @media print {
            .print-btn { display: none; }
            body { padding: 0; }
            .page { padding: 10mm; }
        }
        @page { size: A4 landscape; margin: 10mm; }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Print Register</button>

    <div class="page">
        <div class="header">
            <h1>{{ config('app.name', 'Smart Campus') }}</h1>
            <h2>
                @if($reportType === 'pr')
                    Period Register (PR)
                @elseif($reportType === 'dar')
                    Daily Attendance Register (DAR)
                @else
                    Monthly Attendance Register (MAR)
                @endif
            </h2>
            <div class="meta">
                @if($reportType === 'mar')
                    {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}
                @else
                    {{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}
                @endif
            </div>
        </div>

        @if($class)
        <div class="class-info">
            <div><strong>Class:</strong> {{ $class->name }}</div>
            <div><strong>Grade:</strong> {{ $class->grade->level ?? 'N/A' }}</div>
            <div><strong>Class Teacher:</strong> {{ $class->teacher->user->name ?? 'N/A' }}</div>
            <div><strong>Total Students:</strong> {{ $class->students->count() }}</div>
        </div>

        @if($reportType === 'pr')
            <!-- Period Register Template -->
            <div class="placeholder">
                <h3>Period Register (PR)</h3>
                <p>Attendance by period/subject for {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</p>
                <p style="margin-top: 15px; font-size: 10pt;">
                    Class: {{ $class->name }}<br>
                    Students: {{ $class->students->count() }}<br>
                    <br>
                    <em>Add period columns (Period 1-8) with subject names and attendance marks</em>
                </p>
            </div>

        @elseif($reportType === 'dar')
            <!-- Daily Attendance Register Template -->
            <div class="placeholder">
                <h3>Daily Attendance Register (DAR)</h3>
                <p>Daily attendance for {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</p>
                <p style="margin-top: 15px; font-size: 10pt;">
                    Class: {{ $class->name }}<br>
                    Students: {{ $class->students->count() }}<br>
                    <br>
                    <em>Add columns: Roll No, Name, Morning, Afternoon, Remarks</em>
                </p>
            </div>

        @else
            <!-- Monthly Attendance Register Template -->
            <div class="placeholder">
                <h3>Monthly Attendance Register (MAR)</h3>
                <p>{{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}</p>
                <p style="margin-top: 15px; font-size: 10pt;">
                    Class: {{ $class->name }}<br>
                    Students: {{ $class->students->count() }}<br>
                    <br>
                    <em>Add columns: Roll No, Name, Days 1-31, Total Present, Total Absent, Percentage</em>
                </p>
            </div>
        @endif

        <div class="footer">
            <div class="signatures">
                <div class="sig-box">
                    <div class="sig-line">Class Teacher</div>
                </div>
                <div class="sig-box">
                    <div class="sig-line">Head Teacher</div>
                </div>
                <div class="sig-box">
                    <div class="sig-line">Principal</div>
                </div>
            </div>
        </div>

        @else
        <div class="placeholder">
            <h3>No Class Selected</h3>
            <p>Please select a class to generate the attendance register.</p>
        </div>
        @endif
    </div>
</body>
</html>
