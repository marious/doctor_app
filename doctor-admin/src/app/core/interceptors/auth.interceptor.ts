import { HttpInterceptorFn } from '@angular/common/http';

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const token = localStorage.getItem('token');
  if (token) {
    req = req.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
        'X-App-Type': 'doctor',
      },
    });
  } else {
    req = req.clone({ setHeaders: { Accept: 'application/json', 'X-App-Type': 'doctor' } });
  }
  return next(req);
};
