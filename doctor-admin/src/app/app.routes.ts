import { Routes } from '@angular/router';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from './core/services/auth.service';
import { LayoutComponent } from './shared/components/layout/layout.component';

function authGuard() {
  const auth = inject(AuthService);
  const router = inject(Router);
  if (auth.isLoggedIn()) return true;
  return router.createUrlTree(['/login']);
}

function guestGuard() {
  const auth = inject(AuthService);
  const router = inject(Router);
  if (!auth.isLoggedIn()) return true;
  return router.createUrlTree(['/patients']);
}

export const routes: Routes = [
  {
    path: 'login',
    canActivate: [guestGuard],
    loadComponent: () => import('./features/auth/login/login.component').then(m => m.LoginComponent),
  },
  {
    path: '',
    component: LayoutComponent,
    canActivate: [authGuard],
    children: [
      { path: '', redirectTo: 'patients', pathMatch: 'full' },
      {
        path: 'patients',
        loadComponent: () => import('./features/patients/patient-list/patient-list.component').then(m => m.PatientListComponent),
      },
      {
        path: 'patients/:id',
        loadComponent: () => import('./features/patients/patient-detail/patient-detail.component').then(m => m.PatientDetailComponent),
      },
      {
        path: 'appointments',
        loadComponent: () => import('./features/appointments/appointments.component').then(m => m.AppointmentsComponent),
      },
      {
        path: 'articles',
        loadComponent: () => import('./features/articles/articles.component').then(m => m.ArticlesComponent),
      },
      {
        path: 'videos',
        loadComponent: () => import('./features/videos/videos.component').then(m => m.VideosComponent),
      },
      {
        path: 'advertisements',
        loadComponent: () => import('./features/advertisements/advertisements.component').then(m => m.AdvertisementsComponent),
      },
      {
        path: 'services',
        loadComponent: () => import('./features/services/services.component').then(m => m.ServicesComponent),
      },
      {
        path: 'availability',
        loadComponent: () => import('./features/availability/availability.component').then(m => m.AvailabilityComponent),
      },
      {
        path: 'staff',
        loadComponent: () => import('./features/staff/staff.component').then(m => m.StaffComponent),
      },
    ],
  },
  { path: '**', redirectTo: '' },
];
