import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';
import { Article, PaginatedResponse } from '../models';

@Injectable({ providedIn: 'root' })
export class ArticlesService {
  private http = inject(HttpClient);
  private listBase = `${environment.apiUrl}/articles`;
  private base = `${environment.apiUrl}/doctor/articles`;

  list(params?: Record<string, string>) {
    return this.http.get<PaginatedResponse<Article>>(this.listBase, { params });
  }

  get(id: number) { return this.http.get<{ data: Article }>(`${this.listBase}/${id}`); }

  store(form: FormData) { return this.http.post<{ data: Article }>(this.base, form); }

  update(id: number, form: FormData) { return this.http.post<{ data: Article }>(`${this.base}/${id}`, form); }

  destroy(id: number) { return this.http.delete(`${this.base}/${id}`); }
}
