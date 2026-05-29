import { Component, inject, signal, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, Validators } from '@angular/forms';
import { StaffService, StaffMember } from '../../core/services/staff.service';

@Component({
  selector: 'app-staff',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './staff.component.html',
  styleUrls: ['./staff.component.scss'],
})
export class StaffComponent implements OnInit {
  private svc = inject(StaffService);
  private fb = inject(FormBuilder);

  staff = signal<StaffMember[]>([]);
  loading = signal(true);
  showForm = signal(false);
  editingMember = signal<StaffMember | null>(null);
  saving = signal(false);
  error = signal('');
  success = signal('');
  deleteTarget = signal<StaffMember | null>(null);
  deleting = signal(false);

  form = this.fb.group({
    name:     ['', [Validators.required, Validators.minLength(2)]],
    email:    ['', [Validators.required, Validators.email]],
    phone:    ['', [Validators.required, Validators.minLength(7)]],
    password: [''],
    type:     ['doctor' as 'doctor' | 'assistant', Validators.required],
  });

  get isEditing() { return !!this.editingMember(); }

  ngOnInit() { this.load(); }

  load() {
    this.loading.set(true);
    this.svc.list().subscribe({
      next: res => { this.staff.set(res.data); this.loading.set(false); },
      error: () => this.loading.set(false),
    });
  }

  openCreate() {
    this.editingMember.set(null);
    this.form.reset({ type: 'doctor' });
    this.form.get('password')!.setValidators([Validators.required, Validators.minLength(8)]);
    this.form.get('password')!.updateValueAndValidity();
    this.error.set('');
    this.showForm.set(true);
  }

  openEdit(member: StaffMember) {
    this.editingMember.set(member);
    this.form.reset({
      name:     member.name,
      email:    member.email,
      phone:    member.phone,
      password: '',
      type:     member.type,
    });
    // Password is optional when editing
    this.form.get('password')!.setValidators([Validators.minLength(8)]);
    this.form.get('password')!.updateValueAndValidity();
    this.error.set('');
    this.showForm.set(true);
  }

  submit() {
    if (this.form.invalid) return;
    this.saving.set(true);
    this.error.set('');

    const v = this.form.value;
    const editing = this.editingMember();

    if (editing) {
      const body: Record<string, string> = {
        name: v.name!, email: v.email!, phone: v.phone!, type: v.type!,
      };
      if (v.password) body['password'] = v.password;

      this.svc.update(editing.id, body).subscribe({
        next: res => this.onSuccess(res.message),
        error: err => this.onError(err, 'Failed to update account.'),
      });
    } else {
      this.svc.create(v as any).subscribe({
        next: res => this.onSuccess(res.message),
        error: err => this.onError(err, 'Failed to create account.'),
      });
    }
  }

  confirmDelete(member: StaffMember) { this.deleteTarget.set(member); }

  doDelete() {
    const m = this.deleteTarget();
    if (!m) return;
    this.deleting.set(true);
    this.svc.remove(m.id).subscribe({
      next: res => {
        this.deleteTarget.set(null);
        this.deleting.set(false);
        this.load();
        this.flash(res.message);
      },
      error: err => {
        this.error.set(err.error?.message ?? 'Failed to remove account.');
        this.deleteTarget.set(null);
        this.deleting.set(false);
      },
    });
  }

  private onSuccess(msg: string) {
    this.showForm.set(false);
    this.editingMember.set(null);
    this.saving.set(false);
    this.load();
    this.flash(msg);
  }

  private onError(err: any, fallback: string) {
    this.error.set(err.error?.message ?? fallback);
    this.saving.set(false);
  }

  private flash(msg: string) {
    this.success.set(msg);
    setTimeout(() => this.success.set(''), 3000);
  }

  typeLabel(type: string) { return type === 'doctor' ? 'Doctor' : 'Assistant'; }
  typeClass(type: string) { return type === 'doctor' ? 'badge-doctor' : 'badge-assistant'; }
}
