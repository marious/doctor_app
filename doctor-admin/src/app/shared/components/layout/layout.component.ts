import { Component, inject, signal } from '@angular/core';
import { RouterOutlet, RouterLink, RouterLinkActive } from '@angular/router';
import { CommonModule } from '@angular/common';
import { AuthService } from '../../../core/services/auth.service';

interface NavItem { label: string; icon: string; route: string; }

@Component({
  selector: 'app-layout',
  standalone: true,
  imports: [RouterOutlet, RouterLink, RouterLinkActive, CommonModule],
  templateUrl: './layout.component.html',
  styleUrls: ['./layout.component.scss'],
})
export class LayoutComponent {
  auth = inject(AuthService);
  sidebarOpen = signal(true);

  navItems: NavItem[] = [
    { label: 'Dashboard',          icon: 'M3 3h7v7H3V3zm11 0h7v7h-7V3zm0 11h7v7h-7v-7zM3 14h7v7H3v-7z',                                          route: '/dashboard' },
    { label: 'Appointments',       icon: 'M8 2v3M16 2v3M3.5 9h17M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z',              route: '/appointments' },
    { label: 'Patients',           icon: 'M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z',                               route: '/patients' },
    { label: 'Messages',           icon: 'M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z',                                             route: '/articles' },
    { label: 'Reports',            icon: 'M18 20V10M12 20V4M6 20v-6',                                                                             route: '/reports' },
    { label: 'Availability',       icon: 'M12 22c5.52 0 10-4.48 10-10S17.52 2 12 2 2 6.48 2 12s4.48 10 10 10zm0-14v4l3 3',                       route: '/availability' },
    { label: 'Services & Pricing', icon: 'M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82zM7 7h.01',                  route: '/services' },
    { label: 'Payments',           icon: 'M3 6h18M3 6a2 2 0 00-2 2v10a2 2 0 002 2h18a2 2 0 002-2V8a2 2 0 00-2-2M3 6V4a2 2 0 012-2h14a2 2 0 012 2v2', route: '/videos' },
    { label: 'Notifications',      icon: 'M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0',                                   route: '/advertisements' },
    { label: 'Staff Accounts',     icon: 'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75', route: '/staff' },
  ];

  toggleSidebar() { this.sidebarOpen.update(v => !v); }
  logout() { this.auth.logout(); }
}
