<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a2e; background: #fff; }

  .page { padding: 36px 40px; }

  .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; border-bottom: 2px solid #e8d5e8; padding-bottom: 18px; }
  .header-left h1 { font-size: 22px; font-weight: 700; color: #6b2d6b; }
  .header-left p  { font-size: 11px; color: #888; margin-top: 3px; }
  .header-right   { text-align: right; font-size: 10px; color: #888; line-height: 1.7; }
  .header-right strong { color: #6b2d6b; font-size: 11px; }

  .patient-box { background: #fdf4fd; border: 1px solid #e8d5e8; border-radius: 8px; padding: 14px 16px; margin-bottom: 24px; display: flex; justify-content: space-between; }
  .patient-box .label { font-size: 10px; color: #888; margin-bottom: 2px; }
  .patient-box .value { font-size: 12px; font-weight: 600; color: #1a1a2e; }

  .section-title { font-size: 13px; font-weight: 700; color: #6b2d6b; margin-bottom: 12px; padding-left: 8px; border-left: 3px solid #e88dc8; }
  .section { margin-bottom: 24px; }

  table { width: 100%; border-collapse: collapse; }
  thead tr { background: #6b2d6b; color: #fff; }
  thead th { padding: 8px 12px; text-align: left; font-size: 11px; font-weight: 600; }
  tbody tr:nth-child(even) { background: #fdf4fd; }
  tbody tr:nth-child(odd)  { background: #fff; }
  tbody td { padding: 7px 12px; font-size: 11px; border-bottom: 1px solid #ede0ed; vertical-align: top; }

  .instructions-list { padding-left: 0; list-style: none; }
  .instructions-list li { padding: 6px 0; font-size: 11px; border-bottom: 1px solid #ede0ed; }
  .instructions-list li:before { content: "• "; color: #e88dc8; font-weight: 700; }

  .followup-grid { display: flex; gap: 12px; }
  .followup-card { flex: 1; background: #fdf4fd; border: 1px solid #e8d5e8; border-radius: 8px; padding: 14px 16px; }
  .followup-card .card-title { font-size: 10px; color: #888; margin-bottom: 6px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
  .followup-card .card-value { font-size: 12px; font-weight: 600; color: #6b2d6b; }
  .followup-card .card-list  { font-size: 11px; color: #444; margin-top: 4px; }

  .badge { display: inline-block; background: #f0e6f0; color: #6b2d6b; border-radius: 4px; padding: 2px 8px; font-size: 10px; font-weight: 600; margin: 2px 2px 2px 0; }

  .footer { margin-top: 30px; border-top: 1px solid #e8d5e8; padding-top: 10px; text-align: center; font-size: 9px; color: #bbb; }

  .warning { font-size: 10px; color: #c0392b; margin-top: 3px; }
</style>
</head>
<body>
<div class="page">

  <!-- Header -->
  <div class="header">
    <div class="header-left">
      <h1>{{ $clinic }}</h1>
      <p>Medical Prescription</p>
    </div>
    <div class="header-right">
      <strong>{{ $doctor }}</strong><br>
      Date: {{ $appointmentDate }}<br>
      Generated: {{ $generatedAt }}
    </div>
  </div>

  <!-- Patient info -->
  <div class="patient-box">
    <div>
      <div class="label">Patient Name</div>
      <div class="value">{{ $patientName }}</div>
    </div>
    <div>
      <div class="label">Appointment Date</div>
      <div class="value">{{ $appointmentDate }}</div>
    </div>
    {{-- <div>
      <div class="label">Appointment ID</div>
      <div class="value">#{{ $appointmentId }}</div>
    </div> --}}
  </div>

  <!-- Medications -->
  <div class="section">
    <div class="section-title">Prescribed Medications</div>
    @if($medications->isNotEmpty())
    <table>
      <thead>
        <tr>
          <th>Medication</th>
          <th>Dose / Strength</th>
          <th>Frequency</th>
          @if($hasTiming)<th>Timing</th>@endif
          <th>Duration</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
        @foreach($medications as $med)
        <tr>
          <td>{{ $med['medication_name'] }}</td>
          <td>{{ $med['dose_strength'] ?? '—' }}</td>
          <td>{{ $med['frequency'] ?? '—' }}</td>
          @if($hasTiming)<td>{{ $med['timing'] ?? '—' }}</td>@endif
          <td>{{ $med['duration_days'] ?? '—' }}</td>
          <td>
            @if($med['warning_note'])
              <span class="warning">{{ $med['warning_note'] }}</span>
            @else
              —
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @else
      <p style="color:#888; font-size:11px;">No medications prescribed.</p>
    @endif
  </div>

  <!-- Additional Instructions -->
  {{-- @if($additionalInstructions)
  <div class="section">
    <div class="section-title">Additional Instructions</div>
    <ul class="instructions-list">
      @foreach($additionalInstructions as $instruction)
        <li>{{ $instruction }}</li>
      @endforeach
    </ul>
  </div>
  @endif --}}

  <!-- Follow-up -->
  <div class="section">
    <div class="section-title">Follow-up</div>
    <div class="followup-grid">

      <div class="followup-card">
        <div class="card-title">Next Appointment</div>
        @if($followUp['next_appointment'])
          <div class="card-value">{{ $followUp['next_appointment'] }}</div>
          @if($followUp['next_appointment_time'])
            <div class="card-list">at {{ $followUp['next_appointment_time'] }}</div>
          @endif
        @else
          <div class="card-value" style="color:#888;">Not scheduled</div>
        @endif
      </div>

      <div class="followup-card" style="margin-top: 8px;">
        <div class="card-title">Required Lab Tests</div>
        @if($followUp['required_lab_tests'] && count($followUp['required_lab_tests']) > 0)
          @foreach($followUp['required_lab_tests'] as $test)
            <span class="badge">{{ $test }}</span>
          @endforeach
        @else
          <div class="card-value" style="color:#888;">None</div>
        @endif
      </div>

      @if($followUp['required_scans'] && count($followUp['required_scans']) > 0)
      <div class="followup-card" style="margin-top: 8px;">
        <div class="card-title">Required Scans</div>
        @foreach($followUp['required_scans'] as $scan)
          <span class="badge">{{ $scan }}</span>
        @endforeach
      </div>
      @endif

    </div>
  </div>

  <div class="footer">This prescription is issued by {{ $doctor }} &mdash; {{ $clinic }} &mdash; Confidential medical document.</div>

</div>
</body>
</html>
