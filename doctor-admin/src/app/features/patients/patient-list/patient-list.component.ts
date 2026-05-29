import { Component, inject, signal, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { PatientsService } from '../../../core/services/patients.service';
import { Patient } from '../../../core/models';

type Filter = 'all' | 'pregnancy' | 'period';

@Component({
  selector: 'app-patient-list',
  standalone: true,
  imports: [CommonModule, RouterLink, FormsModule],
  templateUrl: './patient-list.component.html',
  styleUrls: ['./patient-list.component.scss'],
})
export class PatientListComponent implements OnInit {
  private svc = inject(PatientsService);

  patients = signal<Patient[]>([]);
  loading = signal(true);
  error = signal('');
  searchTerm = '';
  page = signal(1);
  lastPage = signal(1);
  total = signal(0);
  activeFilter = signal<Filter>('all');

  ngOnInit() { this.load(); }

  load() {
    this.loading.set(true);
    const params: Record<string, string> = { page: String(this.page()) };
    if (this.searchTerm) params['search'] = this.searchTerm;
    if (this.activeFilter() !== 'all') params['mode'] = this.activeFilter();
    this.svc.list(params).subscribe({
      next: res => {
        this.patients.set(res.data);
        this.lastPage.set(res.meta.last_page);
        this.total.set(res.meta.total);
        this.loading.set(false);
      },
      error: () => { this.error.set('Failed to load patients'); this.loading.set(false); },
    });
  }

  onSearch() { this.page.set(1); this.load(); }

  setFilter(f: Filter) { this.activeFilter.set(f); this.page.set(1); this.load(); }

  prevPage() { if (this.page() > 1) { this.page.update(p => p - 1); this.load(); } }
  nextPage() { if (this.page() < this.lastPage()) { this.page.update(p => p + 1); this.load(); } }

  riskClass(r?: string) {
    return { stable: 'badge-stable', monitor: 'badge-monitor', high_risk: 'badge-high-risk' }[r ?? ''] ?? 'badge-default';
  }

  riskLabel(r?: string) {
    return { stable: 'Stable', monitor: 'Monitor', high_risk: 'High Risk' }[r ?? ''] ?? 'Unknown';
  }

  patientId(id: number) {
    return `#P-${String(id).padStart(3, '0')}`;
  }
}
