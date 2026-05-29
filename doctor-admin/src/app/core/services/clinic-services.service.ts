import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';
import { ClinicService, ServiceCategory } from '../models';

@Injectable({ providedIn: 'root' })
export class ClinicServicesService {
  private http = inject(HttpClient);
  private base = `${environment.apiUrl}/doctor/services`;

  categories() { return this.http.get<{ data: ServiceCategory[] }>(`${this.base}/categories`); }

  list() { return this.http.get<{ data: ClinicService[] }>(this.base); }

  store(body: Partial<ClinicService>) { return this.http.post<{ data: ClinicService }>(this.base, body); }

  update(id: number, body: Partial<ClinicService>) {
    return this.http.post<{ data: ClinicService }>(`${this.base}/${id}`, body);
  }

  destroy(id: number) { return this.http.delete(`${this.base}/${id}`); }
}
