import { Component, inject, signal, OnInit } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { CommonModule } from '@angular/common';
import { PatientsService } from '../../../core/services/patients.service';
import { PatientProfile, PatientStats } from '../../../core/models';
import { SessionsComponent } from '../sessions/sessions.component';
import { LabResultsComponent } from '../lab-results/lab-results.component';
import { UltrasoundComponent } from '../ultrasound/ultrasound.component';
import { PelvicExamsComponent } from '../pelvic-exams/pelvic-exams.component';
import { PrescriptionsComponent } from '../prescriptions/prescriptions.component';

type Tab = 'overview' | 'history' | 'medications' | 'labs' | 'ultrasound' | 'pelvic';
export type View = 'profile' | 'sessions';

@Component({
  selector: 'app-patient-detail',
  standalone: true,
  imports: [
    CommonModule, RouterLink,
    SessionsComponent, LabResultsComponent, UltrasoundComponent,
    PelvicExamsComponent, PrescriptionsComponent,
  ],
  templateUrl: './patient-detail.component.html',
  styleUrls: ['./patient-detail.component.scss'],
})
export class PatientDetailComponent implements OnInit {
  private route = inject(ActivatedRoute);
  private svc = inject(PatientsService);

  patientId = signal(0);
  patient = signal<PatientProfile | null>(null);
  stats = signal<PatientStats | null>(null);
  loading = signal(true);
  activeTab = signal<Tab>('overview');
  activeView = signal<View>('profile');

  profileTabs: { key: Tab; label: string }[] = [
    { key: 'overview',    label: 'Overview' },
    { key: 'history',     label: 'Medical History' },
    { key: 'medications', label: 'Medications' },
    { key: 'labs',        label: 'Lab Results' },
    { key: 'ultrasound',  label: 'Ultra Sound Findings' },
    { key: 'pelvic',      label: 'Pelvic Examination' },
  ];

  ngOnInit() {
    const id = Number(this.route.snapshot.paramMap.get('id'));
    this.patientId.set(id);
    this.loadPatient();
  }

  loadPatient() {
    this.svc.get(this.patientId()).subscribe({
      next: res => {
        this.patient.set(res.data.patient);
        this.stats.set(res.data.stats);
        this.loading.set(false);
      },
      error: () => this.loading.set(false),
    });
  }

  riskClass(r?: string) {
    return { stable: 'badge-stable', monitor: 'badge-monitor', high_risk: 'badge-high-risk' }[r ?? ''] ?? 'badge-default';
  }

  riskLabel(r?: string) {
    return { stable: 'Stable', monitor: 'Monitor', high_risk: 'High Risk' }[r ?? ''] ?? 'Unknown';
  }

}
