<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a2e; background: #fff; }

  .page { padding: 36px 40px; }

  /* Header */
  .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; border-bottom: 2px solid #e8d5e8; padding-bottom: 18px; }
  .header-left h1 { font-size: 22px; font-weight: 700; color: #6b2d6b; }
  .header-left p  { font-size: 11px; color: #888; margin-top: 3px; }
  .header-right   { text-align: right; font-size: 10px; color: #888; line-height: 1.7; }
  .header-right strong { color: #6b2d6b; font-size: 11px; }

  /* Section title */
  .section-title { font-size: 13px; font-weight: 700; color: #6b2d6b; margin-bottom: 12px; padding-left: 8px; border-left: 3px solid #e88dc8; }

  /* Summary cards */
  .summary-grid { display: flex; gap: 12px; margin-bottom: 28px; }
  .summary-card { flex: 1; background: #fdf4fd; border: 1px solid #e8d5e8; border-radius: 8px; padding: 14px 16px; text-align: center; }
  .summary-card .card-value { font-size: 26px; font-weight: 700; color: #6b2d6b; }
  .summary-card .card-label { font-size: 10px; color: #888; margin-top: 3px; }

  /* Growth table */
  .section { margin-bottom: 28px; }
  table { width: 100%; border-collapse: collapse; }
  thead tr { background: #6b2d6b; color: #fff; }
  thead th { padding: 8px 12px; text-align: left; font-size: 11px; font-weight: 600; }
  tbody tr:nth-child(even) { background: #fdf4fd; }
  tbody tr:nth-child(odd)  { background: #fff; }
  tbody td { padding: 7px 12px; font-size: 11px; border-bottom: 1px solid #ede0ed; }
  .text-right { text-align: right; }
  .text-center { text-align: center; }

  /* Mode distribution */
  .mode-grid { display: flex; flex-wrap: wrap; gap: 10px; }
  .mode-card { width: 48%; background: #fdf4fd; border: 1px solid #e8d5e8; border-radius: 8px; padding: 12px 14px; display: flex; align-items: center; gap: 10px; }
  .mode-dot  { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; }
  .mode-info { flex: 1; }
  .mode-label { font-size: 11px; font-weight: 600; color: #1a1a2e; }
  .mode-count { font-size: 10px; color: #888; margin-top: 1px; }
  .mode-pct   { font-size: 16px; font-weight: 700; color: #6b2d6b; }

  /* Bar chart for growth */
  .bar-row { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
  .bar-label  { width: 28px; font-size: 10px; color: #555; text-align: right; }
  .bar-track  { flex: 1; background: #ede0ed; border-radius: 4px; height: 10px; position: relative; }
  .bar-fill-p { height: 10px; border-radius: 4px; background: #e88dc8; }
  .bar-fill-a { height: 10px; border-radius: 4px; background: #2ab5a5; margin-top: 3px; }
  .bar-val    { width: 32px; font-size: 10px; color: #555; }
  .bar-legend { display: flex; gap: 16px; margin-bottom: 10px; }
  .legend-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 4px; vertical-align: middle; }

  /* Footer */
  .footer { margin-top: 30px; border-top: 1px solid #e8d5e8; padding-top: 10px; text-align: center; font-size: 9px; color: #bbb; }
</style>
</head>
<body>
<div class="page">

  <!-- Header -->
  <div class="header">
    <div class="header-left">
      <h1>Reports &amp; Analytics</h1>
      <p>Track clinic performance and patient health trends</p>
    </div>
    <div class="header-right">
      <strong>Period: {{ $periodLabel }}</strong><br>
      {{ $filters['date_from'] }} — {{ $filters['date_to'] }}<br>
      Generated: {{ now()->format('M d, Y') }}
    </div>
  </div>

  <!-- Summary -->
  <div class="section">
    <div class="section-title">Summary</div>
    <div class="summary-grid">
      <div class="summary-card">
        <div class="card-value">{{ number_format($summary['total_patients']) }}</div>
        <div class="card-label">Total Patients</div>
      </div>
      <div class="summary-card">
        <div class="card-value">{{ number_format($summary['new_patients_this_period']) }}</div>
        <div class="card-label">New Patients This Period</div>
      </div>
      <div class="summary-card">
        <div class="card-value">{{ number_format($summary['total_appointments']) }}</div>
        <div class="card-label">Appointments This Period</div>
      </div>
    </div>
  </div>

  <!-- Patient Growth -->
  <div class="section">
    <div class="section-title">Patient Growth</div>

    @php
      $maxPatients     = max(array_column($growth, 'patients') ?: [1]);
      $maxAppointments = max(array_column($growth, 'appointments') ?: [1]);
      $maxVal          = max($maxPatients, $maxAppointments, 1);
    @endphp

    <div class="bar-legend">
      <span><span class="legend-dot" style="background:#e88dc8;"></span>Patients</span>
      <span><span class="legend-dot" style="background:#2ab5a5;"></span>Appointments</span>
    </div>

    @foreach($growth as $row)
    <div class="bar-row">
      <div class="bar-label">{{ $row['label'] }}</div>
      <div style="flex:1;">
        <div class="bar-track">
          <div class="bar-fill-p" style="width:{{ $maxVal > 0 ? round(($row['patients'] / $maxVal) * 100) : 0 }}%;"></div>
        </div>
        <div class="bar-track" style="margin-top:3px;">
          <div class="bar-fill-a" style="width:{{ $maxVal > 0 ? round(($row['appointments'] / $maxVal) * 100) : 0 }}%;"></div>
        </div>
      </div>
      <div style="width:70px; font-size:10px; color:#555; text-align:right;">
        {{ $row['patients'] }} / {{ $row['appointments'] }}
      </div>
    </div>
    @endforeach

    <table style="margin-top:14px;">
      <thead>
        <tr>
          <th>Month</th>
          <th class="text-right">New Patients</th>
          <th class="text-right">Appointments</th>
        </tr>
      </thead>
      <tbody>
        @foreach($growth as $row)
        <tr>
          <td>{{ $row['label'] }} {{ $row['year'] }}</td>
          <td class="text-right">{{ number_format($row['patients']) }}</td>
          <td class="text-right">{{ number_format($row['appointments']) }}</td>
        </tr>
        @endforeach
        <tr style="font-weight:700; background:#f0e6f0;">
          <td>Total</td>
          <td class="text-right">{{ number_format(array_sum(array_column($growth, 'patients'))) }}</td>
          <td class="text-right">{{ number_format(array_sum(array_column($growth, 'appointments'))) }}</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Mode Distribution -->
  <div class="section">
    <div class="section-title">Patient Mode Distribution</div>
    @php
      $modeColors = ['pregnancy' => '#2ab5a5', 'menstrual' => '#e88dc8', 'menopause' => '#f5c0d0', 'fertility' => '#b5e8e0'];
    @endphp
    <div class="mode-grid">
      @foreach($distribution as $mode)
      <div class="mode-card">
        <div class="mode-dot" style="background:{{ $modeColors[$mode['mode']] ?? '#ccc' }};"></div>
        <div class="mode-info">
          <div class="mode-label">{{ $mode['label'] }}</div>
          <div class="mode-count">{{ number_format($mode['count']) }} patients</div>
        </div>
        <div class="mode-pct">{{ $mode['percentage'] }}%</div>
      </div>
      @endforeach
    </div>
  </div>

  <div class="footer">This report is auto-generated by the clinic management system &mdash; confidential patient data.</div>

</div>
</body>
</html>
