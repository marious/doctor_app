import { Component, inject, signal, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormArray, FormGroup, Validators } from '@angular/forms';
import { AvailabilityService } from '../../core/services/availability.service';
import { AvailabilitySlot } from '../../core/models';

@Component({
  selector: 'app-availability',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './availability.component.html',
  styleUrls: ['./availability.component.scss'],
})
export class AvailabilityComponent implements OnInit {
  private svc = inject(AvailabilityService);
  private fb = inject(FormBuilder);

  calendar = signal<Record<string, boolean>>({});
  loadingCalendar = signal(true);
  selectedDate = signal('');
  daySlots = signal<AvailabilitySlot[]>([]);
  loadingDay = signal(false);
  saving = signal(false);
  saved = signal(false);

  slotsForm = this.fb.group({ slots: this.fb.array([]) });

  get slots() { return this.slotsForm.get('slots') as FormArray; }

  ngOnInit() {
    const today = new Date().toISOString().split('T')[0];
    this.selectedDate.set(today);
    this.svc.calendar().subscribe({
      next: res => { this.calendar.set(res.data); this.loadingCalendar.set(false); this.loadDay(today); },
      error: () => this.loadingCalendar.set(false),
    });
  }

  loadDay(date: string) {
    this.selectedDate.set(date);
    this.loadingDay.set(true);
    this.svc.getDay(date).subscribe({
      next: res => {
        this.daySlots.set(res.data.slots);
        this.buildForm(res.data.slots);
        this.loadingDay.set(false);
      },
      error: () => {
        this.buildForm([]);
        this.loadingDay.set(false);
      },
    });
  }

  buildForm(slots: AvailabilitySlot[]) {
    while (this.slots.length) this.slots.removeAt(0);
    if (slots.length === 0) {
      // Default 8 slots: 9am-5pm, 1hr each
      for (let h = 9; h < 17; h++) {
        this.slots.push(this.makeSlot(`${String(h).padStart(2,'0')}:00`, `${String(h+1).padStart(2,'0')}:00`, false));
      }
    } else {
      slots.forEach(s => this.slots.push(this.makeSlot(s.start_time, s.end_time, s.is_available)));
    }
  }

  makeSlot(start: string, end: string, available: boolean): FormGroup {
    return this.fb.group({
      start_time: [start, Validators.required],
      end_time: [end, Validators.required],
      is_available: [available],
    });
  }

  addSlot() {
    this.slots.push(this.makeSlot('', '', false));
  }

  removeSlot(i: number) { this.slots.removeAt(i); }

  save() {
    this.saving.set(true);
    this.saved.set(false);
    const slotsValue = this.slots.value as AvailabilitySlot[];
    this.svc.saveDay(this.selectedDate(), slotsValue).subscribe({
      next: () => { this.saving.set(false); this.saved.set(true); setTimeout(() => this.saved.set(false), 3000); },
      error: () => this.saving.set(false),
    });
  }

  calendarDays() {
    const entries = Object.entries(this.calendar());
    return entries.map(([date, avail]) => ({ date, avail }));
  }
}
