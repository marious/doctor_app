import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';

export interface StaffMember {
  id: number;
  name: string;
  email: string;
  phone: string;
  type: 'doctor' | 'assistant';
  active: boolean;
}

export interface CreateStaffRequest {
  name: string;
  email: string;
  phone: string;
  password: string;
  type: 'doctor' | 'assistant';
}

export interface UpdateStaffRequest {
  name?: string;
  email?: string;
  phone?: string;
  password?: string;
  type?: 'doctor' | 'assistant';
}

@Injectable({ providedIn: 'root' })
export class StaffService {
  private http = inject(HttpClient);
  private base = `${environment.apiUrl}/doctor/staff`;

  list() {
    return this.http.get<{ data: StaffMember[] }>(this.base);
  }

  create(body: CreateStaffRequest) {
    return this.http.post<{ data: StaffMember; message: string }>(this.base, body);
  }

  update(id: number, body: UpdateStaffRequest) {
    return this.http.post<{ data: StaffMember; message: string }>(`${this.base}/${id}`, body);
  }

  remove(id: number) {
    return this.http.delete<{ message: string }>(`${this.base}/${id}`);
  }
}
