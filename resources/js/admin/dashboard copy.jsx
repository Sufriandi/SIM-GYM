// resources/js/admin/dashboard.jsx
import React, { useEffect, useMemo, useRef, useState } from "react";
import { createRoot } from "react-dom/client";

function clamp(n, min, max) { return Math.max(min, Math.min(max, n)); }
function fmtNumber(n) {
  try { return new Intl.NumberFormat("id-ID").format(Number(n || 0)); }
  catch { return String(n ?? 0); }
}
function easeOutCubic(t) { return 1 - Math.pow(1 - t, 3); }

function AnimatedNumber({ value, duration = 900 }) {
  const [display, setDisplay] = useState(0);
  const rafRef = useRef(null);

  useEffect(() => {
    const target = Number(value || 0);
    const start = performance.now();
    const from = display;

    cancelAnimationFrame(rafRef.current);

    const tick = (now) => {
      const t = clamp((now - start) / duration, 0, 1);
      const p = easeOutCubic(t);
      const cur = Math.round(from + (target - from) * p);
      setDisplay(cur);
      if (t < 1) rafRef.current = requestAnimationFrame(tick);
    };

    rafRef.current = requestAnimationFrame(tick);
    return () => cancelAnimationFrame(rafRef.current);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [value]);

  return <span className="tabular-nums">{fmtNumber(display)}</span>;
}

function StatCard({ label, value, hint }) {
  return (
    <div className="rounded-2xl bg-black/30 border border-white/10 backdrop-blur-sm p-4 hover:-translate-y-[2px] transition">
      <p className="text-[10px] uppercase tracking-[0.22em] font-extrabold text-white/70">
        {label}
      </p>
      <div className="mt-2 text-2xl md:text-3xl font-extrabold text-white leading-none">
        <AnimatedNumber value={value} />
      </div>
      <p className="mt-2 text-[11px] text-white/65 truncate">{hint}</p>
    </div>
  );
}

function BarChart({ title, data = [] }) {
  const max = useMemo(() => Math.max(1, ...data.map(d => Number(d.value || 0))), [data]);

  return (
    <div className="rounded-2xl bg-black/25 border border-white/10 backdrop-blur-sm p-4">
      <div className="flex items-end justify-between gap-4">
        <div>
          <p className="text-xs font-extrabold text-white">{title}</p>
          <p className="text-[11px] text-white/60 mt-1">7 hari terakhir</p>
        </div>
        <span className="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">
          Live
        </span>
      </div>

      <div className="mt-4">
        <svg viewBox="0 0 320 110" className="w-full h-[110px]">
          <path d="M0 100 H320" stroke="rgba(255,255,255,.10)" strokeWidth="1" />
          <path d="M0 65 H320" stroke="rgba(255,255,255,.07)" strokeWidth="1" />
          <path d="M0 30 H320" stroke="rgba(255,255,255,.05)" strokeWidth="1" />

          {data.map((d, i) => {
            const v = Number(d.value || 0);
            const h = (v / max) * 80;
            const x = 14 + i * 44;
            const y = 100 - h;

            return (
              <g key={i}>
                <rect
                  x={x}
                  y={y}
                  width="26"
                  height={h}
                  rx="8"
                  fill="rgba(234,179,8,.35)"
                  stroke="rgba(234,179,8,.45)"
                />
                <text x={x + 13} y="108" textAnchor="middle" fontSize="10" fill="rgba(255,255,255,.55)">
                  {String(d.label || "")}
                </text>
              </g>
            );
          })}
        </svg>
      </div>
    </div>
  );
}

function DashboardStats({ payload }) {
  const cards = payload?.cards || [];
  const checkins = payload?.series?.checkinsLast7Days || [];

  return (
    <div className="space-y-3">
      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
        {cards.map((c) => (
          <StatCard key={c.id} label={c.label} value={c.value} hint={c.hint} />
        ))}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-3">
        <BarChart title="Statistik Check-in" data={checkins} />
      </div>
    </div>
  );
}

/**
 * Mount function (aman: kalau elemen tidak ada, tidak ngapa-ngapain)
 */
export function mountAdminDashboardStats() {
  const el = document.getElementById("admin-dashboard-stats");
  if (!el) return;

  let payload = {};
  try { payload = JSON.parse(el.dataset.props || "{}"); }
  catch { payload = {}; }

  createRoot(el).render(<DashboardStats payload={payload} />);
}
