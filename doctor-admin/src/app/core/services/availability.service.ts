import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';
import { DayAvailability } from '../models';

@Injectable({ providedIn: 'root' })
export class AvailabilityService {
  private http = inject(HttpClient);
  private base = `${environment.apiUrl}/doctor/availability`;

  calendar() { return this.http.get<{ data: Record<string, boolean> }>(`${this.base}/calendar`); }

  getDay(date: string) { return this.http.get<{ data: DayAvailability }>(`${this.base}/${date}`); }

  saveDay(date: string, slots: { start_time: string; end_time: string; is_available: boolean }[]) {
    return this.http.post<{ data: DayAvailability }>(`${this.base}/${date}`, { slots });
  }
}
