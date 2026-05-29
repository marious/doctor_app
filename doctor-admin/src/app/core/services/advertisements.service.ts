import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';
import { Advertisement, PaginatedResponse } from '../models';

@Injectable({ providedIn: 'root' })
export class AdvertisementsService {
  private http = inject(HttpClient);
  private listBase = `${environment.apiUrl}/advs`;
  private base = `${environment.apiUrl}/doctor/advs`;

  list(params?: Record<string, string>) {
    return this.http.get<PaginatedResponse<Advertisement>>(this.listBase, { params });
  }

  store(form: FormData) { return this.http.post<{ data: Advertisement }>(this.base, form); }

  update(id: number, form: FormData) { return this.http.post<{ data: Advertisement }>(`${this.base}/${id}`, form); }

  destroy(id: number) { return this.http.delete(`${this.base}/${id}`); }
}
