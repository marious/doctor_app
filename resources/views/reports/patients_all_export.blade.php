<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a2e; background: #fff; }
  .page { padding: 28px 34px; }

  .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 2px solid #e8d5e8; padding-bottom: 14px; }
  .header-left h1 { font-size: 18px; font-weight: 700; color: #6b2d6b; }
  .header-left p  { font-size: 10px; color: #888; margin-top: 3px; }
  .header-right   { text-align: right; font-size: 10px; color: #888; line-height: 1.8; }
  .header-right strong { color: #6b2d6b; }

  .stats-row { display: flex; gap: 10px; margin-bottom: 20px; }
  .stat-card { flex: 1; background: #fdf4fd; border: 1px solid #e8d5e8; border-radius: 6px; padding: 10px 14px; text-align: center; }
  .stat-card .stat-value { font-size: 20px; font-weight: 700; color: #6b2d6b; }
  .stat-card .stat-label { font-size: 9px; color: #888; margin-top: 2px; text-transform: uppercase; letter-spacing: 0.4px; }

  .filters-bar { background: #f9f3f9; border: 1px solid #e8d5e8; border-radius: 6px; padding: 7px 12px; margin-bottom: 18px; font-size: 9px; color: #666; }
  .filters-bar span { margin-right: 16px; }
  .filters-bar strong { color: #6b2d6b; }

  table { width: 100%; border-collapse: collapse; }
  thead tr { background: #6b2d6b; color: #fff; }
  thead th { padding: 7px 10px; text-align: left; font-size: 10px; font-weight: 600; }
  tbody tr:nth-child(even) { background: #fdf4fd; }
  tbody tr:nth-child(odd)  { background: #fff; }
  tbody td { padding: 6px 10px; border-bottom: 1px solid #ede0ed; vertical-align: middle; }

  .badge { display: inline-block; border-radius: 3px; padding: 1px 6px; font-size: 9px; font-weight: 600; }
  .badge-pregnancy { background: #e8f0fd; color: #2d5fb5; }
  .badge-period    { background: #fde8f4; color: #a0286b; }
  .badge-high_risk { background: #fde8e8; color: #c0392b; }
  .badge-monitor   { background: #fef4e6; color: #d68910; }
  .badge-stable    { background: #e6f9ee; color: #1a7a42; }

  .footer { margin-top: 24px; border-top: 1px solid #e8d5e8; padding-top: 8px; text-align: center; font-size: 9px; color: #bbb; }
</style>
</head>
<body>
<div class="page">

  <div class="header">
    <div class="header-left">
      <h1>{{ $clinic }}</h1>
      <p>All Patients Export</p>
    </div>
    <div class="header-right">
      <strong>{{ $doctor }}</strong><br>
      Generated: {{ $generatedAt }}
    </div>
  </div>

  {{-- Stats --}}
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-value">{{ $stats['total'] }}</div>
      <div class="stat-label">Total Patients</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">{{ $stats['pregnancy'] }}</div>
      <div class="stat-label">Pregnancy</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">{{ $stats['period'] }}</div>
      <div class="stat-label">Period</div>
    </div>
    <div class="stat-card">
      <div class="stat-value" style="color:#c0392b;">{{ $stats['high_risk'] }}</div>
      <div class="stat-label">High Risk</div>
    </div>
    <div class="stat-card">
      <div class="stat-value" style="color:#d68910;">{{ $stats['monitor'] }}</div>
      <div class="stat-label">Monitor</div>
    </div>
    <div class="stat-card">
      <div class="stat-value" style="color:#1a7a42;">{{ $stats['stable'] }}</div>
      <div class="stat-label">Stable</div>
    </div>
  </div>

  {{-- Active filters --}}
  @if(array_filter($filters))
  <div class="filters-bar">
    <strong>Filters:</strong>
    @if(!empty($filters['mode']))    <span>Mode: <strong>{{ ucfirst($filters['mode']) }}</strong></span> @endif
    @if(!empty($filters['risk_status'])) <span>Risk: <strong>{{ ucfirst(str_replace('_', ' ', $filters['risk_status'])) }}</strong></span> @endif
    @if(!empty($filters['search'])) <span>Search: <strong>{{ $filters['search'] }}</strong></span> @endif
  </div>
  @endif

  {{-- Patient table --}}
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Code</th>
        <th>Name</th>
        <th>Age</th>
        <th>Blood Group</th>
        <th>Phone</th>
        <th>Mode</th>
        <th>Risk Status</th>
        <th>Last Visit</th>
      </tr>
    </thead>
    <tbody>
      @forelse($patients as $i => $patient)
      <tr>
        <td>{{ $i + 1 }}</td>
        <td>#P-{{ str_pad($patient->getAttribute('id'), 3, '0', STR_PAD_LEFT) }}</td>
        <td><strong>{{ $patient->getAttribute('name') }}</strong></td>
        <td>{{ $patient->age ?? '—' }}</td>
        <td>{{ $patient->getAttribute('blood_group') ?? '—' }}</td>
        <td>{{ $patient->getAttribute('phone') ?? '—' }}</td>
        <td>
          @php $tracking = $patient->patientTracking; @endphp
          @if($tracking?->tracking_type === 'pregnancy')
            <span class="badge badge-pregnancy">Pregnancy</span>
          @elseif($tracking?->tracking_type === 'menstrual')
            <span class="badge badge-period">Period</span>
          @else
            —
          @endif
        </td>
        <td>
          @php $risk = $patient->getAttribute('risk_status'); @endphp
          @if($risk)
            <span class="badge badge-{{ $risk }}">{{ ucfirst(str_replace('_', ' ', $risk)) }}</span>
          @else
            —
          @endif
        </td>
        <td>{{ $patient->lastCompletedAppointment?->appointment_date?->format('M d, Y') ?? '—' }}</td>
      </tr>
      @empty
      <tr><td colspan="9" style="text-align:center; color:#aaa; padding:20px;">No patients found.</td></tr>
      @endforelse
    </tbody>
  </table>

  <div class="footer">
    Confidential — {{ $clinic }} — {{ $generatedAt }} — {{ $stats['total'] }} patient(s)
  </div>

</div>
</body>
</html>
