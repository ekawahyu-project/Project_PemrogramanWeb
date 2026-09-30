/**
 * animations.js — Dynamic Count-Up & Interactive Animations for Native PHP AlpetBizz
 */

(function () {
  'use strict';

  // 1. Smooth Count-Up Animation untuk Angka & Rupiah
  function animateValue(obj, start, end, duration, prefix = '', suffix = '') {
    let startTimestamp = null;
    const step = (timestamp) => {
      if (!startTimestamp) startTimestamp = timestamp;
      const progress = Math.min((timestamp - startTimestamp) / duration, 1);
      // Easing: easeOutExpo
      const easeOut = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
      const current = Math.floor(start + (end - start) * easeOut);
      
      const formatted = current.toLocaleString('id-ID');
      obj.textContent = `${prefix}${formatted}${suffix}`;
      
      if (progress < 1) {
        window.requestAnimationFrame(step);
      } else {
        // Pastikan exact final value
        obj.textContent = `${prefix}${end.toLocaleString('id-ID')}${suffix}`;
      }
    };
    window.requestAnimationFrame(step);
  }

  function initCounterAnimations() {
    // Cari semua elemen nilai statistik (angka besar dengan class font-bold tracking-tight)
    const statValues = document.querySelectorAll('.grid > div.bg-white p.font-bold, .grid > div.bg-white p.text-xl, .grid > div.bg-white p.text-lg');
    
    statValues.forEach(el => {
      const rawText = el.textContent.trim();
      
      // Deteksi format Rupiah: "Rp 1.995.000" atau "+Rp 225.000"
      if (rawText.includes('Rp')) {
        const numOnly = parseInt(rawText.replace(/[^0-9]/g, ''), 10);
        if (!isNaN(numOnly) && numOnly > 0) {
          const prefix = rawText.startsWith('+') ? '+Rp ' : (rawText.startsWith('-') ? '-Rp ' : 'Rp ');
          el.textContent = `${prefix}0`;
          animateValue(el, 0, numOnly, 750, prefix, '');
        }
      } 
      // Deteksi format item: "5 item"
      else if (rawText.includes('item')) {
        const numOnly = parseInt(rawText.replace(/[^0-9]/g, ''), 10);
        if (!isNaN(numOnly)) {
          el.textContent = '0 item';
          animateValue(el, 0, numOnly, 600, '', ' item');
        }
      }
    });
  }

  // 2. Ripple click effect pada button & link
  function initRippleEffect() {
    document.addEventListener('click', function (e) {
      const btn = e.target.closest('button, a.rounded-xl, aside nav a');
      if (!btn) return;

      const rect = btn.getBoundingClientRect();
      const circle = document.createElement('span');
      const diameter = Math.max(rect.width, rect.height);
      const radius = diameter / 2;

      circle.style.width = circle.style.height = `${diameter}px`;
      circle.style.left = `${e.clientX - rect.left - radius}px`;
      circle.style.top = `${e.clientY - rect.top - radius}px`;
      circle.classList.add('alpet-ripple');

      const rippleStyle = document.getElementById('alpet-ripple-style');
      if (!rippleStyle) {
        const s = document.createElement('style');
        s.id = 'alpet-ripple-style';
        s.textContent = `
          .alpet-ripple {
            position: absolute;
            border-radius: 50%;
            transform: scale(0);
            animation: alpetRippleAnim 0.5s linear;
            background-color: rgba(255, 255, 255, 0.25);
            pointer-events: none;
          }
          @keyframes alpetRippleAnim {
            to {
              transform: scale(4);
              opacity: 0;
            }
          }
        `;
        document.head.appendChild(s);
      }

      btn.style.position = btn.style.position || 'relative';
      btn.style.overflow = 'hidden';

      const existing = btn.querySelector('.alpet-ripple');
      if (existing) existing.remove();

      btn.appendChild(circle);
      setTimeout(() => circle.remove(), 500);
    });
  }

  // 3. Inisialisasi saat DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      initCounterAnimations();
      initRippleEffect();
    });
  } else {
    initCounterAnimations();
    initRippleEffect();
  }

  // Expose global re-init function untuk dipanggil oleh spa.js saat ganti halaman
  window.reinitPageAnimations = function () {
    setTimeout(() => {
      initCounterAnimations();
    }, 50);
  };
})();
