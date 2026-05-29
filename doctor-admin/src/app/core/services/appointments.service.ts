import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';
import { Appointment, PaginatedResponse } from '../models';

@Injectable({ providedIn: 'root' })
export class AppointmentsService {
  private http = inject(HttpClient);
  private base = `${environment.apiUrl}/doctor/appointments`;

  list(params?: Record<string, string>) {
    return this.http.get<PaginatedResponse<Appointment>>(this.base, { params });
  }

  get(id: number) { return this.http.get<{ data: Appointment }>(`${this.base}/${id}`); }

  approve(id: number) { return this.http.post(`${this.base}/${id}/approve`, {}); }

  reject(id: number, reason?: string) { return this.http.post(`${this.base}/${id}/reject`, { reason }); }

  reschedule(id: number, body: { date: string; time: string }) {
    return this.http.post(`${this.base}/${id}/reschedule`, body);
  }
}
