import { Injectable, inject, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { tap } from 'rxjs';
import { environment } from '../../../environments/environment';
import { LoginRequest, LoginResponse, AuthUser } from '../models';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private http = inject(HttpClient);
  private router = inject(Router);
  private base = environment.apiUrl;

  currentUser = signal<AuthUser | null>(this.loadUser());

  private loadUser(): AuthUser | null {
    try {
      const u = localStorage.getItem('user');
      return u ? JSON.parse(u) : null;
    } catch {
      localStorage.removeItem('user');
      return null;
    }
  }

  login(body: LoginRequest) {
    return this.http.post<LoginResponse>(`${this.base}/auth/login`, body).pipe(
      tap(res => {
        const { auth_token, ...user } = res.data;
        localStorage.setItem('token', auth_token);
        localStorage.setItem('user', JSON.stringify(user));
        this.currentUser.set(user as AuthUser);
      })
    );
  }

  logout() {
    this.http.post(`${this.base}/auth/logout`, {}).subscribe({ complete: () => this.clearSession() });
  }

  clearSession() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    this.currentUser.set(null);
    this.router.navigate(['/login']);
  }

  isLoggedIn(): boolean { return !!localStorage.getItem('token'); }
}
