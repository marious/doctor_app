import { Component, Input, inject, signal, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder } from '@angular/forms';
import { PatientsService } from '../../../core/services/patients.service';
import { PelvicExamination } from '../../../core/models';
import { ConfirmDialogComponent } from '../../../shared/components/confirm-dialog/confirm-dialog.component';

@Component({
  selector: 'app-pelvic-exams',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, ConfirmDialogComponent],
  templateUrl: './pelvic-exams.component.html',
  styleUrls: ['./pelvic-exams.component.scss'],
})
export class PelvicExamsComponent implements OnInit {
  @Input({ required: true }) patientId!: number;

  private svc = inject(PatientsService);
  private fb = inject(FormBuilder);

  exams = signal<PelvicExamination[]>([]);
  loading = signal(true);
  showForm = signal(false);
  saving = signal(false);
  deleteTarget = signal<number | null>(null);
  selectedFile: File | null = null;

  form = this.fb.group({ notes: [''] });

  ngOnInit() { this.load(); }

  load() {
    this.svc.pelvicExaminations(this.patientId).subscribe({
      next: res => { this.exams.set(res.data); this.loading.set(false); },
      error: () => this.loading.set(false),
    });
  }

  onFileChange(event: Event) {
    const input = event.target as HTMLInputElement;
    this.selectedFile = input.files?.[0] ?? null;
  }

  submit() {
    if (!this.selectedFile) return;
    this.saving.set(true);
    const fd = new FormData();
    fd.append('file', this.selectedFile);
    if (this.form.value.notes) fd.append('notes', this.form.value.notes);
    this.svc.storePelvicExam(this.patientId, fd).subscribe({
      next: () => { this.showForm.set(false); this.selectedFile = null; this.form.reset(); this.load(); this.saving.set(false); },
      error: () => this.saving.set(false),
    });
  }

  confirmDelete(id: number) { this.deleteTarget.set(id); }

  doDelete() {
    const id = this.deleteTarget();
    if (!id) return;
    this.svc.deletePelvicExam(this.patientId, id).subscribe({ next: () => { this.deleteTarget.set(null); this.load(); } });
  }
}
