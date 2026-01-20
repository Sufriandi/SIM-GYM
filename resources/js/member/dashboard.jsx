// resources/js/member/dashboard.jsx

(function () {
  const TZ = "Asia/Jakarta";
  const LOCALE = "id-ID";

  const reduceMotion =
    window.matchMedia &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  function pad2(n) {
    return String(n).padStart(2, "0");
  }

  function getJakartaParts() {
    const parts = new Intl.DateTimeFormat(LOCALE, {
      timeZone: TZ,
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
      hour12: false,
    }).formatToParts(new Date());

    const map = {};
    for (const p of parts) {
      if (p.type !== "literal") map[p.type] = p.value;
    }

    return {
      hour: Number(map.hour),
      minute: Number(map.minute),
      second: Number(map.second),
    };
  }

  function safeLucide() {
    try {
      if (window.lucide && typeof window.lucide.createIcons === "function") {
        window.lucide.createIcons();
      }
    } catch {}
  }

  function initRealtimeClock() {
    const dateNodes = document.querySelectorAll("[data-live-date]");
    const timeNodes = document.querySelectorAll("[data-live-time]");
    if (!dateNodes.length && !timeNodes.length) return;

    const dateFmt = new Intl.DateTimeFormat(LOCALE, {
      timeZone: TZ,
      weekday: "long",
      day: "2-digit",
      month: "long",
      year: "numeric",
    });

    function tick() {
      const now = new Date();
      const dateText = dateFmt.format(now);

      let timeText = "";
      try {
        const t = getJakartaParts();
        timeText = `${pad2(t.hour)}:${pad2(t.minute)}:${pad2(t.second)} WIB`;
      } catch {
        timeText = `${pad2(now.getHours())}:${pad2(now.getMinutes())}:${pad2(now.getSeconds())}`;
      }

      dateNodes.forEach((el) => {
        if (el && el.textContent !== dateText) el.textContent = dateText;
      });
      timeNodes.forEach((el) => {
        if (el && el.textContent !== timeText) el.textContent = timeText;
      });
    }

    tick();
    setInterval(tick, 1000);
  }

  function easeOutCubic(t) {
    return 1 - Math.pow(1 - t, 3);
  }

  function toInt(v, fallback = 0) {
    const n = Number(v);
    return Number.isFinite(n) ? Math.trunc(n) : fallback;
  }

  function animateNumber(el, to, from, duration = 900) {
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

  function animateTypewriter(el, text, duration = 520) {
    const finalText = (text || "").trim();
    if (!finalText) {
      el.textContent = "-";
      return;
    }
    if (reduceMotion) {
      el.textContent = finalText;
      return;
    }
    el.textContent = "";
    const start = performance.now();
    function frame(now) {
      const p = Math.min(1, (now - start) / duration);
      const n = Math.max(1, Math.floor(finalText.length * easeOutCubic(p)));
      el.textContent = finalText.slice(0, n);
      if (p < 1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }

  function animateProgressBar(barEl, toPercent, durationMs = 900) {
    const clamped = Math.max(0, Math.min(100, Number(toPercent || 0)));
    if (reduceMotion) {
      barEl.style.transition = "none";
      barEl.style.width = `${clamped}%`;
      return;
    }
    barEl.style.transition = "none";
    barEl.style.width = "0%";
    requestAnimationFrame(() => {
      barEl.style.transition = `width ${durationMs}ms cubic-bezier(.2,.9,.2,1)`;
      barEl.style.width = `${clamped}%`;
    });
  }

  function runScopedAnimations(scopeEl) {
    const counters = scopeEl.querySelectorAll("[data-counter][data-count-to]");
    counters.forEach((el) => {
      const to = toInt(el.getAttribute("data-count-to"), 0);
      const from = toInt(el.getAttribute("data-count-from"), 0);
      el.textContent = String(from);
      animateNumber(el, to, from, 900);
    });

    const tw = scopeEl.querySelectorAll("[data-typewriter][data-typewriter-text]");
    tw.forEach((el) => {
      const text = el.getAttribute("data-typewriter-text") || "-";
      animateTypewriter(el, text, 520);
    });

    const bar = scopeEl.querySelector("[data-progress-bar][data-progress-to]");
    if (bar) {
      const to = toInt(bar.getAttribute("data-progress-to"), 0);
      animateProgressBar(bar, to, 900);
    }

    safeLucide();
  }

  function initViewportAnimations() {
    const scopes = document.querySelectorAll("[data-animate-scope]");
    if (!scopes.length) return;

    if (!("IntersectionObserver" in window)) {
      scopes.forEach((s) => runScopedAnimations(s));
      return;
    }

    const seen = new WeakSet();
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((e) => {
          if (!e.isIntersecting) return;
          if (seen.has(e.target)) return;
          seen.add(e.target);
          runScopedAnimations(e.target);
          io.unobserve(e.target);
        });
      },
      { threshold: 0.2 }
    );

    scopes.forEach((el) => io.observe(el));
  }

  function boot() {
    safeLucide();
    initRealtimeClock();
    initViewportAnimations();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();
