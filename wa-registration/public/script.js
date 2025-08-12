document.addEventListener('DOMContentLoaded', () => {
  document.body.classList.add('ready');

  // Only run form-related code if a form exists on the page
  const form = document.getElementById('registerForm');
  if (form) {
    const msgEl = document.getElementById('formMsg');
    const submitBtn = form?.querySelector('button[type="submit"]');
    const dlWilayas = document.getElementById('wilayasList');
    const dlCityFR = document.getElementById('cityFRList');

    // Numeric-only enforcement for phone fields
    const numericInputs = ['phoneDZ', 'phoneFR'].map(id => document.getElementById(id));
    numericInputs.forEach(inp => {
      if (!inp) return;
      inp.addEventListener('input', () => {
        const digits = inp.value.replace(/\D+/g, '');
        if (inp.value !== digits) inp.value = digits;
      });

      // Live formatting hint (no spaces) and simple valid state
      inp.addEventListener('blur', () => {
        if (inp.value.trim() === '') return;
        if (!/^\d+$/.test(inp.value)) {
          markInvalid(inp, 'يرجى إدخال أرقام فقط');
        } else {
          clearInvalid(inp);
        }
      });
    });

    // Populate datalists (fallback to embedded static arrays if JSON files are empty)
    const wilayas = [
      'أدرار','الشلف','الأغواط','أم البواقي','باتنة','بجاية','بسكرة','بشار','البليدة','البويرة','تمنراست','تبسة','تلمسان','تيارت','تيزي وزو','الجزائر','الجلفة','جيجل','سطيف','سعيدة','سكيكدة','سيدي بلعباس','عنابة','قالمة','قسنطينة','المدية','مستغانم','المسيلة','معسكر','ورقلة','وهران','البيض','إليزي','برج بوعريريج','بومرداس','الطارف','تندوف','تيسمسيلت','الوادي','خنشلة','سوق أهراس','تيبازة','ميلة','عين الدفلى','النعامة','عين تموشنت','غرداية','غليزان'
    ];
    const citiesFR = ['Annemasse','Lyon','Marseille','Macon','Chambery','Cluses','Annecy','Monso','Grenoble','Montceau'];

    function fillList(el, arr) {
      if (!el) return;
      el.innerHTML = arr.map(v => `<option value="${escapeHtml(v)}"></option>`).join('');
    }
    fillList(dlWilayas, wilayas);
    fillList(dlCityFR, citiesFR);

    // Validate datalist-backed inputs exist in the list
    const wilayaInput = document.getElementById('wilaya');
    const cityFRInput = document.getElementById('cityFR');
    function validateInList(inp, options) {
      if (!inp) return true;
      const v = inp.value.trim();
      if (!v) return false;
      const ok = options.includes(v);
      if (!ok) markInvalid(inp, 'يرجى اختيار قيمة من القائمة'); else clearInvalid(inp);
      return ok;
    }
    wilayaInput?.addEventListener('blur', () => validateInList(wilayaInput, wilayas));
    cityFRInput?.addEventListener('blur', () => validateInList(cityFRInput, citiesFR));

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      msgEl.textContent = '';
      submitBtn?.classList.add('loading');
      submitBtn?.setAttribute('disabled', 'true');

      const data = Object.fromEntries(new FormData(form));
      // All fields required
      const required = ['fullName', 'phoneDZ', 'phoneFR', 'wilaya', 'cityFR'];
      for (const name of required) {
        if (!data[name] || String(data[name]).trim() === '') {
          fail('يرجى ملء جميع الحقول المطلوبة.');
          return;
        }
      }
      // Numeric check
      if (!/^\d+$/.test(data.phoneDZ) || !/^\d+$/.test(data.phoneFR)) {
        fail('أرقام الهاتف يجب أن تحتوي على أرقام فقط.');
        return;
      }

      // List membership check for datalist inputs
      if (!validateInList(wilayaInput, wilayas) || !validateInList(cityFRInput, citiesFR)) {
        fail('يرجى اختيار القيم من القوائم المقترحة.');
        return;
      }

      try {
        // Use relative PHP endpoint to work under XAMPP (http://localhost/wa-registration/)
        const res = await fetch('../api/submit.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            fullName: data.fullName.trim(),
            phoneDZ: data.phoneDZ.trim(),
            phoneFR: data.phoneFR.trim(),
            wilaya: data.wilaya,
            cityFR: data.cityFR
          })
        });

        let payload = null;
        try { payload = await res.json(); } catch {}
        if (!res.ok) {
          const serverMsg = payload && (payload.error || payload.message);
          console.error('Submit failed', { status: res.status, payload });
          fail(serverMsg || 'حدث خطأ أثناء الإرسال. حاول مرة أخرى.');
          return;
        }
        payload = payload || {};
        const code = payload?.clientCode;
        const isExisting = payload?.isExisting;
        const existingName = payload?.existingName;
        
        if (isExisting) {
          success(`مرحباً ${existingName || 'مرة أخرى'}! كودك موجود مسبقاً.`);
        } else {
          success('تم استلام طلب التسجيل!');
        }
        
        if (code) showSuccessModal(code, isExisting, existingName);
  if (code) showSuccessModal(code);
  form.reset();
      } catch (err) {
        console.error(err);
        fail('حدث خطأ أثناء الإرسال. حاول مرة أخرى.');
      } finally {
        submitBtn?.classList.remove('loading');
        submitBtn?.removeAttribute('disabled');
      }
    });

    function success(text) {
      msgEl.classList.remove('error'); msgEl.classList.add('success');
      msgEl.textContent = text;
      submitBtn?.classList.remove('loading');
      submitBtn?.removeAttribute('disabled');
    }

    function fail(text) {
      msgEl.classList.remove('success'); msgEl.classList.add('error');
      msgEl.textContent = text;
      submitBtn?.classList.remove('loading');
      submitBtn?.removeAttribute('disabled');
    }

    function markInvalid(input, message) {
      input.setAttribute('aria-invalid', 'true');
      input.closest('.field')?.classList.add('invalid');
      let hint = input.closest('.field')?.querySelector('.hint');
      if (hint) { hint.textContent = message; hint.style.color = '#b3261e'; }
    }
    function clearInvalid(input) {
      input.removeAttribute('aria-invalid');
      input.closest('.field')?.classList.remove('invalid');
      let hint = input.closest('.field')?.querySelector('.hint');
      if (hint) { hint.style.color = '#64748b'; }
    }

    function escapeHtml(s) {
      return s.replace(/[&<>"]+/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
    }

    // Success modal + confetti
    const modal = document.getElementById('successModal');
    const codeEl = document.getElementById('clientCode');
    const copyBtn = document.getElementById('copyCodeBtn');
    const closeBtn = document.getElementById('closeModalBtn');
    const confettiLayer = document.getElementById('confettiCanvas');

    function showSuccessModal(code, isExisting, existingName) {
      if (codeEl) codeEl.textContent = code;
      
      // Update modal content based on whether this is an existing registration
      const modalTitle = document.getElementById('successTitle');
      const modalText = document.querySelector('.modal-text');
      
      if (isExisting) {
        if (modalTitle) modalTitle.textContent = 'مرحباً بعودتك!';
        if (modalText) modalText.textContent = `أهلاً ${existingName || ''}، هذا هو كودك السابق.`;
      } else {
        if (modalTitle) modalTitle.textContent = 'تم التسجيل بنجاح';
        if (modalText) modalText.textContent = 'تم إنشاء كود العميل الخاص بك.';
      }
      
      if (modal) { modal.style.display = 'grid'; modal.setAttribute('aria-hidden', 'false'); }
      launchConfetti();
    }
    function hideSuccessModal() {
      if (modal) { modal.style.display = 'none'; modal.setAttribute('aria-hidden', 'true'); }
      stopConfetti();
    }
    closeBtn?.addEventListener('click', hideSuccessModal);
    modal?.querySelector('.modal-backdrop')?.addEventListener('click', hideSuccessModal);
    copyBtn?.addEventListener('click', async () => {
      const text = codeEl?.textContent || '';
      try { await navigator.clipboard.writeText(text); copyBtn.textContent = 'تم النسخ'; setTimeout(() => copyBtn.textContent = 'نسخ', 1200); } catch {}
    });

    // Minimal confetti animation (DOM-based for no external deps)
    let confettiTimer;
    function launchConfetti() {
      if (!confettiLayer) return;
      confettiLayer.innerHTML = '';
      const colors = ['#22c55e','#3b82f6','#a855f7','#ef4444','#f59e0b'];
      for (let i = 0; i < 120; i++) {
        const p = document.createElement('div');
        const size = 6 + Math.random() * 6;
        p.style.position = 'absolute';
        p.style.width = size + 'px';
        p.style.height = size + 'px';
        p.style.background = colors[Math.floor(Math.random() * colors.length)];
        p.style.left = (Math.random() * 100) + 'vw';
        p.style.top = (-10 - Math.random() * 30) + 'px';
        p.style.opacity = '0.9';
        p.style.transform = `rotate(${Math.random()*360}deg)`;
        p.style.borderRadius = Math.random() < .4 ? '50%' : '4px';
        confettiLayer.appendChild(p);
      }
      const parts = Array.from(confettiLayer.children);
      const start = performance.now();
      function tick(now){
        const t = (now - start) / 1000;
        parts.forEach((el, idx) => {
          const y = t * (50 + (idx % 40));
          const x = Math.sin((t + idx) * 1.3) * 30;
          el.style.transform = `translate(${x}px, ${y}px) rotate(${(t*120 + idx)%360}deg)`;
          if (parseFloat(el.style.top) + y > window.innerHeight + 40) el.remove();
        });
        if (confettiLayer.children.length > 0) confettiTimer = requestAnimationFrame(tick);
      }
      confettiTimer = requestAnimationFrame(tick);
      setTimeout(stopConfetti, 3000);
    }
    function stopConfetti(){ if (confettiTimer) cancelAnimationFrame(confettiTimer); confettiLayer && (confettiLayer.innerHTML = ''); }
  }
});
