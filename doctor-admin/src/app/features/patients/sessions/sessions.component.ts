import { Component, Input, inject, signal, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, Validators } from '@angular/forms';
import { PatientsService } from '../../../core/services/patients.service';
import { Session } from '../../../core/models';

@Component({
  selector: 'app-sessions',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './sessions.component.html',
  styleUrls: ['./sessions.component.scss'],
})
export class SessionsComponent implements OnInit {
  @Input({ required: true }) patientId!: number;

  private svc = inject(PatientsService);
  private fb = inject(FormBuilder);

  sessions = signal<Session[]>([]);
  loading = signal(true);
  showForm = signal(false);
  editingId = signal<number | null>(null);
  expandedId = signal<number | null>(null);
  saving = signal(false);
  error = signal('');

  visitTypes = ['Pregnancy Check', 'Follow-up', 'Routine Check', 'Initial Visit', 'Emergency'];

  form = this.fb.group({
    date:           ['', Validators.required],
    visit_type:     ['Follow-up'],
    diagnosis:      ['', Validators.required],
    notes:          [''],
    treatment_plan: [''],
    symptoms:       [''],
    follow_up_date: [''],
    bp:             [''],
    hr:             [''],
    temp:           [''],
    weight:         [''],
  });

  ngOnInit() { this.load(); }

  load() {
    this.svc.sessions(this.patientId).subscribe({
      next: res => { this.sessions.set(res.data); this.loading.set(false); },
      error: () => this.loading.set(false),
    });
  }

  openNew() { this.form.reset({ visit_type: 'Follow-up' }); this.editingId.set(null); this.showForm.set(true); }

  openEdit(s: Session) {
    this.form.patchValue({
      date: s.date, visit_type: s.visit_type ?? 'Follow-up',
      diagnosis: s.diagnosis ?? '', notes: s.notes ?? '',
      treatment_plan: s.treatment_plan ?? '', symptoms: s.symptoms ?? '',
      follow_up_date: s.follow_up_date ?? '',
      bp: s.bp ?? '', hr: s.hr ?? '', temp: s.temp ?? '', weight: s.weight ?? '',
    });
    this.editingId.set(s.id);
    this.showForm.set(true);
  }

  submit() {
    if (this.form.invalid) return;
    this.saving.set(true);
    const body = this.form.value as Partial<Session>;
    const id = this.editingId();
    const req = id
      ? this.svc.updateSession(this.patientId, id, body)
      : this.svc.storeSession(this.patientId, body);

    req.subscribe({
      next: () => { this.showForm.set(false); this.load(); this.saving.set(false); },
      error: () => this.saving.set(false),
    });
  }

  toggleExpand(id: number) {
    this.expandedId.set(this.expandedId() === id ? null : id);
  }

  get totalSessions() { return this.sessions().length; }
  get thisMonth() {
    const now = new Date();
    return this.sessions().filter(s => {
      const d = new Date(s.date);
      return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
    }).length;
  }
  get completed() { return this.sessions().filter(s => s.status === 'completed').length; }
  get followUp()   { return this.sessions().filter(s => s.status === 'follow_up_required' || !!s.follow_up_date).length; }

  statusClass(s?: string) {
    return { completed: 'status-completed', follow_up_required: 'status-followup', pending: 'status-pending' }[s ?? ''] ?? 'status-pending';
  }

  statusLabel(s?: string) {
    return { completed: 'Completed', follow_up_required: 'Follow-Up Required', pending: 'Pending' }[s ?? ''] ?? 'Pending';
  }

  visitTypeClass(t?: string) {
    const map: Record<string, string> = {
      'Pregnancy Check': 'vt-pregnancy',
      'Follow-up':       'vt-followup',
      'Routine Check':   'vt-routine',
      'Initial Visit':   'vt-initial',
      'Emergency':       'vt-emergency',
    };
    return map[t ?? ''] ?? 'vt-routine';
  }
}
