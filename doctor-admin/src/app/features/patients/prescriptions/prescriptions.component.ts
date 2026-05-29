import { Component, Input, inject, signal, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, Validators } from '@angular/forms';
import { PatientsService } from '../../../core/services/patients.service';
import { Prescription } from '../../../core/models';
import { ConfirmDialogComponent } from '../../../shared/components/confirm-dialog/confirm-dialog.component';

@Component({
  selector: 'app-prescriptions',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, ConfirmDialogComponent],
  templateUrl: './prescriptions.component.html',
  styleUrls: ['./prescriptions.component.scss'],
})
export class PrescriptionsComponent implements OnInit {
  @Input({ required: true }) patientId!: number;

  private svc = inject(PatientsService);
  private fb = inject(FormBuilder);

  prescriptions = signal<Prescription[]>([]);
  loading = signal(true);
  showForm = signal(false);
  editingId = signal<number | null>(null);
  saving = signal(false);
  deleteTarget = signal<number | null>(null);

  form = this.fb.group({
    medication_name: ['', Validators.required],
    dosage: ['', Validators.required],
    frequency: ['', Validators.required],
    start_date: ['', Validators.required],
    end_date: [''],
    notes: [''],
  });

  ngOnInit() { this.load(); }

  load() {
    this.svc.prescriptions(this.patientId).subscribe({
      next: res => { this.prescriptions.set(res.data); this.loading.set(false); },
      error: () => this.loading.set(false),
    });
  }

  openNew() { this.form.reset(); this.editingId.set(null); this.showForm.set(true); }

  openEdit(p: Prescription) {
    this.form.patchValue({
      medication_name: p.medication_name,
      dosage: p.dosage,
      frequency: p.frequency,
      start_date: p.start_date,
      end_date: p.end_date ?? '',
      notes: p.notes ?? '',
    });
    this.editingId.set(p.id);
    this.showForm.set(true);
  }

  submit() {
    if (this.form.invalid) return;
    this.saving.set(true);
    const body = this.form.value as Partial<Prescription>;
    const id = this.editingId();
    const req = id
      ? this.svc.updatePrescription(this.patientId, id, body)
      : this.svc.storePrescription(this.patientId, body);
    req.subscribe({
      next: () => { this.showForm.set(false); this.load(); this.saving.set(false); },
      error: () => this.saving.set(false),
    });
  }

  stopPrescription(id: number) {
    this.svc.stopPrescription(this.patientId, id).subscribe({ next: () => this.load() });
  }

  confirmDelete(id: number) { this.deleteTarget.set(id); }

  doDelete() {
    const id = this.deleteTarget();
    if (!id) return;
    this.svc.deletePrescription(this.patientId, id).subscribe({ next: () => { this.deleteTarget.set(null); this.load(); } });
  }
}
