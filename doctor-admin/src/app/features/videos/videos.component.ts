import { Component, inject, signal, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, Validators } from '@angular/forms';
import { VideosService } from '../../core/services/videos.service';
import { Video } from '../../core/models';
import { ConfirmDialogComponent } from '../../shared/components/confirm-dialog/confirm-dialog.component';

@Component({
  selector: 'app-videos',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, ConfirmDialogComponent],
  templateUrl: './videos.component.html',
  styleUrls: ['./videos.component.scss'],
})
export class VideosComponent implements OnInit {
  private svc = inject(VideosService);
  private fb = inject(FormBuilder);

  videos = signal<Video[]>([]);
  loading = signal(true);
  showForm = signal(false);
  editingId = signal<number | null>(null);
  saving = signal(false);
  deleteTarget = signal<number | null>(null);
  page = signal(1);
  lastPage = signal(1);
  total = signal(0);
  selectedThumb: File | null = null;

  form = this.fb.group({
    title: ['', Validators.required],
    url: ['', Validators.required],
  });

  ngOnInit() { this.load(); }

  load() {
    this.loading.set(true);
    this.svc.list({ page: String(this.page()) }).subscribe({
      next: res => { this.videos.set(res.data); this.lastPage.set(res.meta.last_page); this.total.set(res.meta.total); this.loading.set(false); },
      error: () => this.loading.set(false),
    });
  }

  openNew() { this.form.reset(); this.editingId.set(null); this.selectedThumb = null; this.showForm.set(true); }

  openEdit(v: Video) {
    this.form.patchValue({ title: v.title, url: v.url });
    this.editingId.set(v.id);
    this.selectedThumb = null;
    this.showForm.set(true);
  }

  onThumbChange(event: Event) {
    const input = event.target as HTMLInputElement;
    this.selectedThumb = input.files?.[0] ?? null;
  }

  submit() {
    if (this.form.invalid) return;
    this.saving.set(true);
    const fd = new FormData();
    fd.append('title', this.form.value.title!);
    fd.append('url', this.form.value.url!);
    if (this.selectedThumb) fd.append('thumbnail', this.selectedThumb);
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

  prevPage() { if (this.page() > 1) { this.page.update(p => p - 1); this.load(); } }
  nextPage() { if (this.page() < this.lastPage()) { this.page.update(p => p + 1); this.load(); } }
}
