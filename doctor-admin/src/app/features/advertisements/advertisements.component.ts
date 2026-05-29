import { Component, inject, signal, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, Validators } from '@angular/forms';
import { AdvertisementsService } from '../../core/services/advertisements.service';
import { Advertisement } from '../../core/models';
import { ConfirmDialogComponent } from '../../shared/components/confirm-dialog/confirm-dialog.component';

@Component({
  selector: 'app-advertisements',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, ConfirmDialogComponent],
  templateUrl: './advertisements.component.html',
  styleUrls: ['./advertisements.component.scss'],
})
export class AdvertisementsComponent implements OnInit {
  private svc = inject(AdvertisementsService);
  private fb = inject(FormBuilder);

  ads = signal<Advertisement[]>([]);
  loading = signal(true);
  showForm = signal(false);
  editingId = signal<number | null>(null);
  saving = signal(false);
  deleteTarget = signal<number | null>(null);
  selectedImage: File | null = null;

  form = this.fb.group({
    title: ['', Validators.required],
    link: [''],
    active: [true],
  });

  ngOnInit() { this.load(); }

  load() {
    this.loading.set(true);
    this.svc.list().subscribe({
      next: res => { this.ads.set(res.data); this.loading.set(false); },
      error: () => this.loading.set(false),
    });
  }

  openNew() { this.form.reset({ active: true }); this.editingId.set(null); this.selectedImage = null; this.showForm.set(true); }

  openEdit(a: Advertisement) {
    this.form.patchValue({ title: a.title, link: a.link ?? '', active: a.active });
    this.editingId.set(a.id);
    this.selectedImage = null;
    this.showForm.set(true);
  }

  onImageChange(event: Event) {
    const input = event.target as HTMLInputElement;
    this.selectedImage = input.files?.[0] ?? null;
  }

  submit() {
    if (this.form.invalid) return;
    this.saving.set(true);
    const fd = new FormData();
    fd.append('title', this.form.value.title!);
    if (this.form.value.link) fd.append('link', this.form.value.link);
    fd.append('active', this.form.value.active ? '1' : '0');
    if (this.selectedImage) fd.append('image', this.selectedImage);
    const id = this.editingId();
    const req = id ? this.svc.update(id, fd) : this.svc.store(fd);
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
