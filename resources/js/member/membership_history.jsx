// resources/js/member/member_history.jsx

(function () {
  const reduceMotion =
    window.matchMedia &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  function safeLucide() {
    try {
      if (window.lucide && typeof window.lucide.createIcons === "function") {
        window.lucide.createIcons();
      }
    } catch {}
  }

  function easeOutCubic(t) {
    return 1 - Math.pow(1 - t, 3);
  }

  function toInt(v, fallback = 0) {
    const n = Number(v);
    return Number.isFinite(n) ? Math.trunc(n) : fallback;
  }

  function animateNumber(el, to, from, duration = 850) {
    if (reduceMotion) {
      el.textContent = String(to);
      return;
    }

    const start = performance.now();
    function frame(now) {
      const p = Math.min(1, (now - start) / duration);
      const v = Math.round(from + (to - from) * easeOutCubic(p));
      el.textContent = String(v);
      if (p < 1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }

  function primeBars(bars) {
    bars.forEach((bar) => {
      // simpan target
      const to = toInt(bar.getAttribute("data-progress-to"), 0);
      bar.dataset.progressTo = String(Math.max(0, Math.min(100, to)));

      // prime: hide + reset width
      bar.style.willChange = "width, opacity";
      bar.style.transition = "none";
      bar.style.opacity = "0";
      bar.style.width = "0%";

      // flush layout supaya browser “mengunci” state awal
      // eslint-disable-next-line no-unused-expressions
      bar.offsetWidth;
    });
  }

  function animateBars(bars, durationMs = 900) {
    bars.forEach((bar) => {
      const clamped = Math.max(0, Math.min(100, Number(bar.dataset.progressTo || 0)));

      if (reduceMotion) {
        bar.style.transition = "none";
        bar.style.opacity = "1";
        bar.style.width = `${clamped}%`;
        return;
      }

      // animate: fade in + grow
      bar.style.transition = `opacity 160ms ease, width ${durationMs}ms cubic-bezier(.2,.9,.2,1)`;
      bar.style.opacity = "1";
      bar.style.width = `${clamped}%`;
    });
  }

  function runAnimations() {
    const scopes = document.querySelectorAll("[data-animate-scope]");
    if (!scopes.length) return;

    // counters
    const counters = document.querySelectorAll("[data-counter][data-count-to]");
    counters.forEach((el) => {
      const to = toInt(el.getAttribute("data-count-to"), 0);
      const from = toInt(el.getAttribute("data-count-from"), 0);
      el.textContent = String(from);
      animateNumber(el, to, from, 850);
    });

    // progress bars
    const bars = Array.from(document.querySelectorAll("[data-progress-bar][data-progress-to]"));

    // prime dulu agar terlihat animasinya
    primeBars(bars);

    // setelah frame berikutnya, animasikan
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        animateBars(bars, 900);
      });
    });

    safeLucide();
  }

  function boot() {
    safeLucide();

    // Delay kecil agar layout table/overflow settle (lebih stabil)
    setTimeout(runAnimations, 60);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();
