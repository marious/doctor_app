import { Component, inject, signal, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, Validators } from '@angular/forms';
import { ArticlesService } from '../../core/services/articles.service';
import { Article } from '../../core/models';
import { ConfirmDialogComponent } from '../../shared/components/confirm-dialog/confirm-dialog.component';

@Component({
  selector: 'app-articles',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, ConfirmDialogComponent],
  templateUrl: './articles.component.html',
  styleUrls: ['./articles.component.scss'],
})
export class ArticlesComponent implements OnInit {
  private svc = inject(ArticlesService);
  private fb = inject(FormBuilder);

  articles = signal<Article[]>([]);
  loading = signal(true);
  showForm = signal(false);
  editingId = signal<number | null>(null);
  saving = signal(false);
  deleteTarget = signal<number | null>(null);
  page = signal(1);
  lastPage = signal(1);
  total = signal(0);
  selectedImage: File | null = null;

  form = this.fb.group({
    title: ['', Validators.required],
    body: ['', Validators.required],
  });

  ngOnInit() { this.load(); }

  load() {
    this.loading.set(true);
    this.svc.list({ page: String(this.page()) }).subscribe({
      next: res => { this.articles.set(res.data); this.lastPage.set(res.meta.last_page); this.total.set(res.meta.total); this.loading.set(false); },
      error: () => this.loading.set(false),
    });
  }

  openNew() { this.form.reset(); this.editingId.set(null); this.selectedImage = null; this.showForm.set(true); }

  openEdit(a: Article) {
    this.form.patchValue({ title: a.title, body: a.body });
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
    fd.append('body', this.form.value.body!);
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

  prevPage() { if (this.page() > 1) { this.page.update(p => p - 1); this.load(); } }
  nextPage() { if (this.page() < this.lastPage()) { this.page.update(p => p + 1); this.load(); } }
}
