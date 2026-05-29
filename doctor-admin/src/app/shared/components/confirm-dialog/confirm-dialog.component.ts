import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-confirm-dialog',
  standalone: true,
  imports: [CommonModule],
  template: `
    @if (open) {
      <div class="overlay" (click)="cancel.emit()">
        <div class="dialog" (click)="$event.stopPropagation()">
          <h3>{{ title }}</h3>
          <p>{{ message }}</p>
          <div class="actions">
            <button class="btn-secondary" (click)="cancel.emit()">Cancel</button>
            <button class="btn-danger" (click)="confirm.emit()">{{ confirmLabel }}</button>
          </div>
        </div>
      </div>
    }
  `,
  styles: [`
    .overlay { position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:1000; }
    .dialog { background:#fff;border-radius:12px;padding:28px;max-width:400px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.3); }
    h3 { margin:0 0 12px;font-size:18px;color:#1a202c; }
    p { margin:0 0 24px;color:#718096;font-size:14px; }
    .actions { display:flex;gap:12px;justify-content:flex-end; }
    .btn-secondary,.btn-danger { padding:8px 20px;border-radius:8px;border:none;font-size:14px;font-weight:600;cursor:pointer; }
    .btn-secondary { background:#edf2f7;color:#4a5568; }
    .btn-danger { background:#e53e3e;color:#fff; }
    .btn-secondary:hover { background:#e2e8f0; }
    .btn-danger:hover { background:#c53030; }
  `],
})
export class ConfirmDialogComponent {
  @Input() open = false;
  @Input() title = 'Confirm';
  @Input() message = 'Are you sure?';
  @Input() confirmLabel = 'Delete';
  @Output() confirm = new EventEmitter<void>();
  @Output() cancel = new EventEmitter<void>();
}
