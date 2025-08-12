
document.addEventListener('DOMContentLoaded', () => {
  // TODO: Replace with your WhatsApp number (international format, no + or spaces)
  const COMPANY_WA = '213000000000';

  const form = document.getElementById('registerForm');
  const msgEl = document.getElementById('formMsg');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    msgEl.textContent = '';

    const data = Object.fromEntries(new FormData(form));
    // basic required check
    const required = ['fullName', 'customerWa', 'from', 'to', 'city', 'address', 'agree'];
    for (const name of required) {
      if (!data[name] || (name === 'agree' && data[name] !== 'on')) {
        msgEl.textContent = 'يرجى ملء جميع الحقول المطلوبة.';
        return;
      }
    }

    const lines = [
      'طلب تسجيل جديد:',
      `الاسم: ${data.fullName}`,
      `واتساب العميل: ${data.customerWa}`,
      `من: ${data.from} -> إلى: ${data.to}`,
      `المدينة: ${data.city}`,
      `العنوان: ${data.address}`,
      data.details ? `التفاصيل: ${data.details}` : null
    ].filter(Boolean);

    const text = encodeURIComponent(lines.join('\n'));
    const url = `https://wa.me/${COMPANY_WA}?text=${text}`;
    window.open(url, '_blank');
  });
});