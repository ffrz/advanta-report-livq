// import { Inertia } from '@inertiajs/inertia';

export function goBack(/*fallbackUrl = '/dashboard'*/) {
  return () => {
    if (window.history.length > 1) {
      window.history.back();
    } else {
      // Inertia.visit(fallbackUrl);
    }
  };
}
