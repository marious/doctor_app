import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';
import { Video, PaginatedResponse } from '../models';

@Injectable({ providedIn: 'root' })
export class VideosService {
  private http = inject(HttpClient);
  private listBase = `${environment.apiUrl}/videos`;
  private base = `${environment.apiUrl}/doctor/videos`;

  list(params?: Record<string, string>) {
    return this.http.get<PaginatedResponse<Video>>(this.listBase, { params });
  }

  get(id: number) { return this.http.get<{ data: Video }>(`${this.listBase}/${id}`); }

  store(form: FormData) { return this.http.post<{ data: Video }>(this.base, form); }

  update(id: number, form: FormData) { return this.http.post<{ data: Video }>(`${this.base}/${id}`, form); }

  destroy(id: number) { return this.http.delete(`${this.base}/${id}`); }
}
