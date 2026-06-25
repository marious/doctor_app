import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { DashboardService } from '../../core/services/dashboard.service';
import { AuthService } from '../../core/services/auth.service';
import { DashboardData } from '../../core/models';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.scss'],
})
export class DashboardComponent implements OnInit {
  private svc = inject(DashboardService);
  auth = inject(AuthService);

  data = signal<DashboardData | null>(null);
  loading = signal(true);
  error = signal('');

  ngOnInit() { this.load(); }

  load() {
    this.loading.set(true);
    this.svc.get().subscribe({
      next: res => { this.data.set(res.data); this.loading.set(false); },
      error: () => { this.error.set('Failed to load dashboard'); this.loading.set(false); },
    });
  }

  statusClass(s: string) {
    return { pending: 'badge-warning', under_review: 'badge-warning', approved: 'badge-success', completed: 'badge-blue' }[s] ?? 'badge-default';
  }

  statusLabel(s: string) {
    return { pending: 'Pending', under_review: 'Pending', approved: 'Approved', completed: 'Completed' }[s] ?? s;
  }
}
