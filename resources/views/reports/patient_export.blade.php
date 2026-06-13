<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a2e; background: #fff; }
  .page { padding: 32px 38px; }

  .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; border-bottom: 2px solid #e8d5e8; padding-bottom: 16px; }
  .header-left h1 { font-size: 20px; font-weight: 700; color: #6b2d6b; }
  .header-left p  { font-size: 10px; color: #888; margin-top: 3px; }
  .header-right   { text-align: right; font-size: 10px; color: #888; line-height: 1.8; }
  .header-right strong { color: #6b2d6b; }

  .patient-card { background: #fdf4fd; border: 1px solid #e8d5e8; border-radius: 8px; padding: 14px 18px; margin-bottom: 22px; }
  .patient-card h2 { font-size: 15px; font-weight: 700; color: #6b2d6b; margin-bottom: 10px; }
  .info-grid { display: flex; flex-wrap: wrap; }
  .info-item { width: 33%; margin-bottom: 8px; }
  .info-item .label { font-size: 9px; color: #999; text-transform: uppercase; letter-spacing: 0.4px; }
  .info-item .value { font-size: 11px; font-weight: 600; color: #1a1a2e; margin-top: 1px; }

  .medical-bg-grid { display: flex; gap: 10px; margin-bottom: 22px; }
  .medical-bg-block { flex: 1; background: #fdf4fd; border: 1px solid #e8d5e8; border-radius: 8px; padding: 12px 14px; }
  .medical-bg-block .block-title { font-size: 9px; color: #888; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 6px; }
  .medical-bg-block ul { padding-left: 14px; }
  .medical-bg-block li { font-size: 10px; color: #333; margin-bottom: 3px; }
  .medical-bg-block p  { font-size: 10px; color: #aaa; font-style: italic; }

  .section { margin-bottom: 22px; }
  .section-title { font-size: 11px; font-weight: 700; color: #6b2d6b; padding: 5px 10px; background: #f5e6f5; border-left: 3px solid #6b2d6b; border-radius: 0 4px 4px 0; margin-bottom: 10px; }

  table { width: 100%; border-collapse: collapse; }
  thead tr { background: #6b2d6b; color: #fff; }
  thead th { padding: 7px 10px; text-align: left; font-size: 10px; font-weight: 600; }
  tbody tr:nth-child(even) { background: #fdf4fd; }
  tbody tr:nth-child(odd)  { background: #fff; }
  tbody td { padding: 6px 10px; border-bottom: 1px solid #ede0ed; vertical-align: top; line-height: 1.5; font-size: 10px; }

  .badge { display: inline-block; border-radius: 3px; padding: 1px 6px; font-size: 9px; font-weight: 600; }
  .badge-active   { background: #e6f9ee; color: #1a7a42; }
  .badge-finished { background: #f0e6f0; color: #6b2d6b; }
  .badge-high_risk { background: #fde8e8; color: #c0392b; }
  .badge-monitor   { background: #fef4e6; color: #d68910; }
  .badge-stable    { background: #e6f9ee; color: #1a7a42; }

  .empty { font-size: 10px; color: #aaa; font-style: italic; padding: 6px 0; }
  .footer { margin-top: 28px; border-top: 1px solid #e8d5e8; padding-top: 10px; text-align: center; font-size: 9px; color: #bbb; }
</style>
</head>
<body>
<div class="page">

  <div class="header">
    <div class="header-left">
      <h1>{{ $clinic }}</h1>
      <p>Patient Medical Record</p>
    </div>
    <div class="header-right">
      <strong>{{ $doctor }}</strong><br>
      Generated: {{ $generatedAt }}<br>
      Ref: #P-{{ str_pad($patient->getAttribute('id'), 3, '0', STR_PAD_LEFT) }}
    </div>
  </div>

  {{-- Patient Info --}}
  <div class="patient-card">
    <h2>{{ $patient->getAttribute('name') }}</h2>
    <div class="info-grid">
      <div class="info-item">
        <div class="label">Patient Code</div>
        <div class="value">#P-{{ str_pad($patient->getAttribute('id'), 3, '0', STR_PAD_LEFT) }}</div>
      </div>
      <div class="info-item">
        <div class="label">Date of Birth</div>
        <div class="value">{{ $patient->getAttribute('date_of_birth')?->format('M d, Y') ?? '—' }}</div>
      </div>
      <div class="info-item">
        <div class="label">Age</div>
        <div class="value">{{ $patient->age ?? '—' }} years</div>
      </div>
      <div class="info-item">
        <div class="label">Phone</div>
        <div class="value">{{ $patient->getAttribute('phone') ?? '—' }}</div>
      </div>
      <div class="info-item">
        <div class="label">Blood Group</div>
        <div class="value">{{ $patient->getAttribute('blood_group') ?? '—' }}</div>
      </div>
      <div class="info-item">
        <div class="label">Marital Status</div>
        <div class="value">{{ ucfirst($patient->getAttribute('marital_status') ?? '—') }}</div>
      </div>
      <div class="info-item">
        <div class="label">Address</div>
        <div class="value">{{ $patient->getAttribute('address') ?? '—' }}</div>
      </div>
      <div class="info-item">
        <div class="label">Emergency Contact</div>
        <div class="value">{{ $patient->getAttribute('emergency_number') ?? '—' }}</div>
      </div>
      <div class="info-item">
        <div class="label">Risk Status</div>
        <div class="value">
          @php $risk = $patient->getAttribute('risk_status'); @endphp
          @if($risk)
            <span class="badge badge-{{ $risk }}">{{ ucfirst(str_replace('_', ' ', $risk)) }}</span>
          @else —
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Medical Background --}}
  @php
    $history     = $patient->getAttribute('medical_history') ?? [];
    $allergies   = $patient->getAttribute('allergies') ?? [];
    $medications = $patient->getAttribute('current_medications') ?? [];
  @endphp
  <div class="section">
    <div class="section-title">Medical Background</div>
    <div class="medical-bg-grid">
      <div class="medical-bg-block">
        <div class="block-title">Medical History</div>
        @if(count($history)) <ul>@foreach($history as $h)<li>{{ $h }}</li>@endforeach</ul>
        @else <p>None recorded</p> @endif
      </div>
      <div class="medical-bg-block">
        <div class="block-title">Allergies</div>
        @if(count($allergies)) <ul>@foreach($allergies as $a)<li>{{ $a }}</li>@endforeach</ul>
        @else <p>None recorded</p> @endif
      </div>
      <div class="medical-bg-block">
        <div class="block-title">Current Medications</div>
        @if(count($medications)) <ul>@foreach($medications as $m)<li>{{ $m }}</li>@endforeach</ul>
        @else <p>None recorded</p> @endif
      </div>
    </div>
  </div>

  {{-- Clinical Sessions --}}
  <div class="section">
    <div class="section-title">Clinical Sessions (Last 10)</div>
    @if($sessions->isEmpty()) <p class="empty">No sessions recorded.</p>
    @else
    <table>
      <thead><tr><th>Date</th><th>Type</th><th>BP</th><th>HR</th><th>Weight</th><th>Diagnosis</th><th>Follow-up</th></tr></thead>
      <tbody>
        @foreach($sessions as $s)
        <tr>
          <td>{{ $s->getAttribute('session_date')?->format('M d, Y') ?? '—' }}</td>
          <td>{{ ucfirst(str_replace('_', ' ', $s->getAttribute('service_type') ?? '—')) }}</td>
          <td>{{ $s->getAttribute('bp') ?? '—' }}</td>
          <td>{{ $s->getAttribute('hr') ? $s->getAttribute('hr') . ' bpm' : '—' }}</td>
          <td>{{ $s->getAttribute('weight') ? $s->getAttribute('weight') . ' kg' : '—' }}</td>
          <td>{{ $s->getAttribute('diagnosis') ?? '—' }}</td>
          <td>{{ $s->getAttribute('follow_up_date')?->format('M d, Y') ?? '—' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif
  </div>

  {{-- Prescriptions --}}
  <div class="section">
    <div class="section-title">Prescriptions</div>
    @if($prescriptions->isEmpty()) <p class="empty">No prescriptions recorded.</p>
    @else
    <table>
      <thead><tr><th>Medication</th><th>Dosage</th><th>Frequency</th><th>Start</th><th>End</th><th>Status</th></tr></thead>
      <tbody>
        @foreach($prescriptions as $rx)
        <tr>
          <td>{{ $rx->getAttribute('medication_name') }}</td>
          <td>{{ $rx->getAttribute('dosage') ?? '—' }}</td>
          <td>{{ \Modules\Prescriptions\Models\PatientPrescription::$frequencyLabels[$rx->getAttribute('frequency')] ?? $rx->getAttribute('frequency') ?? '—' }}</td>
          <td>{{ $rx->getAttribute('start_date')?->format('M d, Y') ?? '—' }}</td>
          <td>{{ $rx->getAttribute('end_date')?->format('M d, Y') ?? '—' }}</td>
          <td><span class="badge {{ $rx->resolveStatus() === 'active' ? 'badge-active' : 'badge-finished' }}">{{ ucfirst($rx->resolveStatus()) }}</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif
  </div>

  {{-- Lab Results --}}
  <div class="section">
    <div class="section-title">Lab Results</div>
    @if($labResults->isEmpty()) <p class="empty">No lab results recorded.</p>
    @else
    <table>
      <thead><tr><th>Test Name</th><th>Date</th></tr></thead>
      <tbody>
        @foreach($labResults as $r)
        <tr>
          <td>{{ $r->getAttribute('name') }}</td>
          <td>{{ $r->getAttribute('created_at')?->format('M d, Y') ?? '—' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif
  </div>

  {{-- Ultrasound Findings --}}
  <div class="section">
    <div class="section-title">Ultrasound Findings</div>
    @if($ultrasounds->isEmpty()) <p class="empty">No ultrasound findings recorded.</p>
    @else
    <table>
      <thead><tr><th>Notes</th><th>Date</th></tr></thead>
      <tbody>
        @foreach($ultrasounds as $us)
        <tr>
          <td>{{ $us->getAttribute('notes') ?? $us->getAttribute('title') ?? '—' }}</td>
          <td>{{ $us->getAttribute('created_at')?->format('M d, Y') ?? '—' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif
  </div>

  <div class="footer">
    Confidential — {{ $clinic }} — Generated {{ $generatedAt }}
  </div>

</div>
</body>
</html>
