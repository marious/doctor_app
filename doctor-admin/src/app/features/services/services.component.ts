import { Component, inject, signal, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, Validators } from '@angular/forms';
import { ClinicServicesService } from '../../core/services/clinic-services.service';
import { ClinicService, ServiceCategory } from '../../core/models';
import { ConfirmDialogComponent } from '../../shared/components/confirm-dialog/confirm-dialog.component';

@Component({
  selector: 'app-services',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, ConfirmDialogComponent],
  templateUrl: './services.component.html',
  styleUrls: ['./services.component.scss'],
})
export class ServicesComponent implements OnInit {
  private svc = inject(ClinicServicesService);
  private fb = inject(FormBuilder);

  services = signal<ClinicService[]>([]);
  categories = signal<ServiceCategory[]>([]);
  loading = signal(true);
  showForm = signal(false);
  editingId = signal<number | null>(null);
  saving = signal(false);
  deleteTarget = signal<number | null>(null);

  form = this.fb.group({
    name: ['', Validators.required],
    category: ['', Validators.required],
    price: [0, [Validators.required, Validators.min(0)]],
    description: [''],
    is_active: [true],
  });

  ngOnInit() {
    this.svc.categories().subscribe({ next: res => this.categories.set(res.data) });
    this.load();
  }

  load() {
    this.loading.set(true);
    this.svc.list().subscribe({
      next: res => { this.services.set(res.data); this.loading.set(false); },
      error: () => this.loading.set(false),
    });
  }

  openNew() { this.form.reset({ is_active: true, price: 0 }); this.editingId.set(null); this.showForm.set(true); }

  openEdit(s: ClinicService) {
    this.form.patchValue({ name: s.name, category: s.category, price: s.price, description: s.description ?? '', is_active: s.is_active });
    this.editingId.set(s.id);
    this.showForm.set(true);
  }

  submit() {
    if (this.form.invalid) return;
    this.saving.set(true);
    const body = this.form.value as Partial<ClinicService>;
    const id = this.editingId();
    const req = id ? this.svc.update(id, body) : this.svc.store(body);
    req.subscribe({
      next: () => { this.showForm.set(false); this.load(); this.saving.set(false); },
      error: () => this.saving.set(false),
    });
  }

  confirmDelete(id: number) { this.deleteTarget.set(id); }

  doDelete() {
    const id = this.deleteTarget();
    if (!id) return;
    this.svc.destroy(id).subscribe({ next: () => { this.deleteTarget.set(null); this.load(); } });
  }
}
