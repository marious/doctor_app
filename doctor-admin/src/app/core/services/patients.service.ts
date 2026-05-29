import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';
import { Patient, PatientProfile, PatientProfileResponse, PatientOverview, Session, LabResult, UltrasoundFinding, PelvicExamination, Prescription, PaginatedResponse } from '../models';

@Injectable({ providedIn: 'root' })
export class PatientsService {
  private http = inject(HttpClient);
  private base = `${environment.apiUrl}/doctor/patients`;

  list(params?: Record<string, string>) {
    return this.http.get<PaginatedResponse<Patient>>(this.base, { params });
  }

  get(id: number) { return this.http.get<{ data: PatientProfileResponse }>(`${this.base}/${id}`); }

  overview(id: number) { return this.http.get<{ data: PatientOverview }>(`${this.base}/${id}/overview`); }

  updateRiskStatus(id: number, risk_status: string) {
    return this.http.patch<{ data: Patient }>(`${this.base}/${id}/risk-status`, { risk_status });
  }

  // Sessions
  sessionsDropdown(patientId: number) {
    return this.http.get<{ data: { id: number; date: string }[] }>(`${this.base}/${patientId}/sessions/dropdown`);
  }

  sessions(patientId: number) {
    return this.http.get<{ data: Session[] }>(`${this.base}/${patientId}/sessions`);
  }

  storeSession(patientId: number, body: Partial<Session>) {
    return this.http.post<{ data: Session }>(`${this.base}/${patientId}/sessions`, body);
  }

  showSession(patientId: number, sessionId: number) {
    return this.http.get<{ data: Session }>(`${this.base}/${patientId}/sessions/${sessionId}`);
  }

  updateSession(patientId: number, sessionId: number, body: Partial<Session>) {
    return this.http.post<{ data: Session }>(`${this.base}/${patientId}/sessions/${sessionId}`, body);
  }

  // Lab Results
  labResults(patientId: number) {
    return this.http.get<{ data: LabResult[] }>(`${this.base}/${patientId}/lab-results`);
  }

  storeLabResult(patientId: number, form: FormData) {
    return this.http.post<{ data: LabResult }>(`${this.base}/${patientId}/lab-results`, form);
  }

  deleteLabResult(patientId: number, id: number) {
    return this.http.delete(`${this.base}/${patientId}/lab-results/${id}`);
  }

  // Ultrasound
  ultrasoundFindings(patientId: number) {
    return this.http.get<{ data: UltrasoundFinding[] }>(`${this.base}/${patientId}/ultrasound-findings`);
  }

  storeUltrasound(patientId: number, form: FormData) {
    return this.http.post<{ data: UltrasoundFinding }>(`${this.base}/${patientId}/ultrasound-findings`, form);
  }

  deleteUltrasound(patientId: number, id: number) {
    return this.http.delete(`${this.base}/${patientId}/ultrasound-findings/${id}`);
  }

  // Pelvic Exams
  pelvicExaminations(patientId: number) {
    return this.http.get<{ data: PelvicExamination[] }>(`${this.base}/${patientId}/pelvic-examinations`);
  }

  storePelvicExam(patientId: number, form: FormData) {
    return this.http.post<{ data: PelvicExamination }>(`${this.base}/${patientId}/pelvic-examinations`, form);
  }

  deletePelvicExam(patientId: number, id: number) {
    return this.http.delete(`${this.base}/${patientId}/pelvic-examinations/${id}`);
  }

  // Prescriptions
  prescriptions(patientId: number) {
    return this.http.get<{ data: Prescription[] }>(`${this.base}/${patientId}/prescriptions`);
  }

  storePrescription(patientId: number, body: Partial<Prescription>) {
    return this.http.post<{ data: Prescription }>(`${this.base}/${patientId}/prescriptions`, body);
  }

  updatePrescription(patientId: number, id: number, body: Partial<Prescription>) {
    return this.http.post<{ data: Prescription }>(`${this.base}/${patientId}/prescriptions/${id}`, body);
  }

  stopPrescription(patientId: number, id: number) {
    return this.http.post(`${this.base}/${patientId}/prescriptions/${id}/stop`, {});
  }

  deletePrescription(patientId: number, id: number) {
    return this.http.delete(`${this.base}/${patientId}/prescriptions/${id}`);
  }
}
