export interface PaginatedResponse<T> {
  data: T[];
  meta: { current_page: number; last_page: number; per_page: number; total: number; };
}

export interface ApiResponse<T> {
  data: T;
  message?: string;
}

// Auth
export interface LoginRequest { phone_or_email: string; password: string; }
export interface AuthUser { id: number; name: string; phone: string; role: string; avatar?: string; }
export interface LoginResponse { data: { auth_token: string } & AuthUser; }

// Patient directory item (list endpoint)
export interface Patient {
  id: number;
  patient_code?: string;
  name: string;
  phone: string;
  avatar?: string;
  age?: number;
  current_mode?: { key: string; label: string } | null;
  risk_status?: string;
  last_visit?: string;
  date_of_birth?: string;
  created_at: string;
}

// Full patient profile (show endpoint)
export interface PatientProfile {
  id: number;
  patient_code: string;
  name: string;
  phone: string;
  avatar?: string;
  age?: number;
  mode?: string;
  risk_status?: string;
  last_visit?: string;
  created_at?: string;
  blood_group?: string;
  allergies?: string[];
  current_medications?: string[];
  medical_history?: string[];
}

export interface PatientStats {
  total_sessions: number;
  this_month: number;
  completed: number;
  follow_up: number;
}

export interface PatientProfileResponse {
  patient: PatientProfile;
  stats: PatientStats;
}

export interface PatientOverview {
  patient: Patient;
  last_appointment?: Appointment;
  upcoming_appointment?: Appointment;
  active_prescriptions_count: number;
  sessions_count: number;
}

// Session
export interface Session {
  id: number;
  patient_id: number;
  date: string;
  visit_type?: string;
  status?: 'completed' | 'follow_up_required' | 'pending';
  notes?: string;
  diagnosis?: string;
  treatment_plan?: string;
  symptoms?: string;
  follow_up_date?: string;
  bp?: string;
  hr?: string;
  temp?: string;
  weight?: string;
  created_at: string;
  updated_at: string;
}

// Appointment
export interface Appointment {
  id: number;
  patient: Patient;
  date: string;
  time: string;
  status: 'pending' | 'approved' | 'rejected' | 'completed' | 'cancelled';
  notes?: string;
  created_at: string;
}

// Lab Result
export interface LabResult {
  id: number;
  patient_id: number;
  session_id?: number;
  file_url: string;
  notes?: string;
  created_at: string;
}

// Ultrasound Finding
export interface UltrasoundFinding {
  id: number;
  patient_id: number;
  session_id?: number;
  file_url: string;
  notes?: string;
  created_at: string;
}

// Pelvic Examination
export interface PelvicExamination {
  id: number;
  patient_id: number;
  session_id?: number;
  file_url: string;
  notes?: string;
  created_at: string;
}

// Prescription
export interface Prescription {
  id: number;
  patient_id: number;
  medication_name: string;
  dosage: string;
  frequency: string;
  start_date: string;
  end_date?: string;
  status: 'active' | 'stopped';
  notes?: string;
  created_at: string;
}

// Article
export interface Article {
  id: number;
  title: string;
  body: string;
  image?: string;
  created_at: string;
}

// Video
export interface Video {
  id: number;
  title: string;
  url: string;
  thumbnail?: string;
  created_at: string;
}

// Advertisement
export interface Advertisement {
  id: number;
  title: string;
  image: string;
  link?: string;
  active: boolean;
  created_at: string;
}

// Clinic Service
export interface ClinicService {
  id: number;
  name: string;
  category: string;
  price: number;
  description?: string;
  is_active: boolean;
}

export interface ServiceCategory { id: number; name: string; }

// Availability
export interface AvailabilitySlot {
  start_time: string;
  end_time: string;
  is_available: boolean;
}

export interface DayAvailability {
  date: string;
  slots: AvailabilitySlot[];
}
