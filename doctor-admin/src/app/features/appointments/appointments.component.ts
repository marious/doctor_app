import { Component, inject, signal, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, Validators } from '@angular/forms';
import { AppointmentsService } from '../../core/services/appointments.service';
import { Appointment } from '../../core/models';

type ActionType = 'approve' | 'reject' | 'reschedule' | null;

@Component({
  selector: 'app-appointments',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './appointments.component.html',
  styleUrls: ['./appointments.component.scss'],
})
export class AppointmentsComponent implements OnInit {
  private svc = inject(AppointmentsService);
  private fb = inject(FormBuilder);

  appointments = signal<Appointment[]>([]);
  loading = signal(true);
  page = signal(1);
  lastPage = signal(1);
  total = signal(0);

  selectedAppointment = signal<Appointment | null>(null);
  action = signal<ActionType>(null);
  saving = signal(false);

  rescheduleForm = this.fb.group({
    date: ['', Validators.required],
    time: ['', Validators.required],
  });

  rejectForm = this.fb.group({ reason: [''] });

  ngOnInit() { this.load(); }

  load() {
    this.loading.set(true);
    this.svc.list({ page: String(this.page()) }).subscribe({
      next: res => {
        this.appointments.set(res.data);
        this.lastPage.set(res.meta.last_page);
        this.total.set(res.meta.total);
        this.loading.set(false);
      },
      error: () => this.loading.set(false),
    });
  }

  openAction(appt: Appointment, type: ActionType) {
    this.selectedAppointment.set(appt);
    this.action.set(type);
    this.rescheduleForm.reset();
    this.rejectForm.reset();
  }

  closeAction() { this.action.set(null); this.selectedAppointment.set(null); }

  doApprove() {
    const appt = this.selectedAppointment();
    if (!appt) return;
    this.saving.set(true);
    this.svc.approve(appt.id).subscribe({ next: () => { this.closeAction(); this.load(); this.saving.set(false); }, error: () => this.saving.set(false) });
  }

  doReject() {
    const appt = this.selectedAppointment();
    if (!appt) return;
    this.saving.set(true);
    this.svc.reject(appt.id, this.rejectForm.value.reason ?? undefined).subscribe({
      next: () => { this.closeAction(); this.load(); this.saving.set(false); },
      error: () => this.saving.set(false),
    });
  }

  doReschedule() {
    const appt = this.selectedAppointment();
    if (!appt || this.rescheduleForm.invalid) return;
    this.saving.set(true);
    this.svc.reschedule(appt.id, this.rescheduleForm.value as { date: string; time: string }).subscribe({
      next: () => { this.closeAction(); this.load(); this.saving.set(false); },
      error: () => this.saving.set(false),
    });
  }

  prevPage() { if (this.page() > 1) { this.page.update(p => p - 1); this.load(); } }
  nextPage() { if (this.page() < this.lastPage()) { this.page.update(p => p + 1); this.load(); } }

  statusClass(s: string) {
    return { pending: 'badge-warning', approved: 'badge-success', rejected: 'badge-danger', completed: 'badge-blue', cancelled: 'badge-default' }[s] ?? 'badge-default';
  }
}
