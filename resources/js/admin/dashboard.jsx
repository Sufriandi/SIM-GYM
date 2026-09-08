// resources/js/admin/dashboard.jsx
import React, { useEffect, useMemo, useRef, useState } from "react";
import { createRoot } from "react-dom/client";

const fmt = (n) => {
  try {
    return new Intl.NumberFormat("id-ID").format(Number(n || 0));
  } catch {
    return String(n ?? 0);
  }
};

function clamp(n, min, max) {
  return Math.max(min, Math.min(max, n));
}

function pickSeries(payload) {
  return (
    payload?.series?.checkinsLast7Days ||
    payload?.series?.checkins ||
    payload?.checkinsLast7Days ||
    payload?.checkinSeries ||
    []
  );
}

function DashboardChart({ data = [] }) {
  const safeData = Array.isArray(data) ? data : [];

  const stats = useMemo(() => {
    const values = safeData.map((d) => Number(d?.value || 0));
    const total = values.reduce((a, b) => a + b, 0);
    const max = values.length ? Math.max(...values) : 0;
    const avg = values.length ? Math.round(total / values.length) : 0;
    const yMax = max > 0 ? Math.ceil(max * 1.15) : 5;
    return { total, avg, max, yMax };
  }, [safeData]);

  const WIDTH = 720;
  const HEIGHT = 260;
  const M = { top: 26, right: 16, bottom: 34, left: 34 };
  const innerW = WIDTH - M.left - M.right;
  const innerH = HEIGHT - M.top - M.bottom;

  const n = Math.max(safeData.length, 7);
  const step = innerW / n;
  const barW = step * 0.62;

  const [mounted, setMounted] = useState(false);
  const [hover, setHover] = useState(null);
  const wrapRef = useRef(null);

  useEffect(() => {
    requestAnimationFrame(() => setMounted(true));
  }, []);

  const gridLines = useMemo(() => {
    return [0, 0.33, 0.66, 1].map((ratio) => {
      const y = M.top + innerH - ratio * innerH;
      const v = Math.round(ratio * stats.yMax);
      return { y, v };
    });
  }, [innerH, stats.yMax]);

  const onLeave = () => setHover(null);

  const onMove = (e, d, i) => {
    const el = wrapRef.current;
    if (!el) return;

    const r = el.getBoundingClientRect();
    const mx = e.clientX - r.left;
    const my = e.clientY - r.top;

    setHover({
      index: i,
      x: clamp(mx, 8, r.width - 8),
      y: clamp(my, 8, r.height - 8),
      label: String(d?.label ?? "-"),
      value: Number(d?.value ?? 0),
    });
  };

  return (
    <div className="flex flex-col h-full bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden font-sans">
      <div className="px-6 pt-5 pb-3 flex items-start justify-between border-b border-gray-50">
        <div className="min-w-0">
          <h3 className="text-gray-900 font-bold text-lg tracking-tight">
            Aktivitas Check-in
          </h3>
          <p className="text-gray-400 text-xs mt-0.5">
            Statistik kunjungan 7 hari terakhir.
          </p>
        </div>

        <div className="flex gap-6 text-right shrink-0">
          <div>
            <p className="text-[10px] uppercase text-gray-400 font-semibold tracking-wider">
              Rata-rata
            </p>
            <p className="text-sm font-bold text-gray-700">
              {fmt(stats.avg)}{" "}
              <span className="text-[10px] font-normal text-gray-400">/hari</span>
            </p>
          </div>
          <div>
            <p className="text-[10px] uppercase text-gray-400 font-semibold tracking-wider">
              Tertinggi
            </p>
            <p className="text-sm font-bold text-yellow-600">{fmt(stats.max)}</p>
          </div>
        </div>
      </div>

      <div
        ref={wrapRef}
        className="relative flex-1 w-full min-h-[240px] p-4"
        onMouseLeave={onLeave}
      >
        {hover && (
          <div
            className="absolute z-10 pointer-events-none"
            style={{
              left: hover.x,
              top: hover.y,
              transform: "translate(-50%, -110%)",
            }}
          >
            <div className="rounded-xl bg-black/75 text-white px-3 py-2 text-[11px] border border-white/10 shadow-lg">
              <div className="font-bold">{hover.label}</div>
              <div className="opacity-90">
                Check-in: <span className="font-bold">{fmt(hover.value)}</span>
              </div>
            </div>
          </div>
        )}

        <svg
          viewBox={`0 0 ${WIDTH} ${HEIGHT}`}
          className="w-full h-full"
          role="img"
          aria-label="Grafik check-in 7 hari terakhir"
        >
          <defs>
            <linearGradient id="barGradient" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stopColor="#EAB308" />
              <stop offset="100%" stopColor="#FACC15" />
            </linearGradient>
          </defs>

          {gridLines.map((g, idx) => (
            <g key={idx}>
              <line
                x1={M.left}
                x2={WIDTH - M.right}
                y1={g.y}
                y2={g.y}
                stroke="#F3F4F6"
                strokeWidth="1"
              />
              <text
                x={M.left - 8}
                y={g.y + 4}
                textAnchor="end"
                fontSize="10"
                fill="#9CA3AF"
              >
                {g.v}
              </text>
            </g>
          ))}

          <line
            x1={M.left}
            x2={WIDTH - M.right}
            y1={M.top + innerH}
            y2={M.top + innerH}
            stroke="#E5E7EB"
            strokeWidth="1"
          />

          {safeData.map((d, i) => {
            const val = Number(d?.value || 0);
            const label = String(d?.label ?? "");
            const barH = (val / stats.yMax) * innerH;

            const x = M.left + i * step + (step - barW) / 2;
            const y = M.top + (innerH - barH);

            const yAnim = mounted ? y : M.top + innerH;
            const hAnim = mounted ? barH : 0;

            const isLast = i === safeData.length - 1;

            return (
              <g key={i} onMouseMove={(e) => onMove(e, d, i)} style={{ cursor: "pointer" }}>
                <rect
                  x={x}
                  y={yAnim}
                  width={barW}
                  height={hAnim}
                  rx="8"
                  fill={isLast ? "rgba(34,197,94,0.75)" : "url(#barGradient)"}
                  style={{
                    transition:
                      "y 750ms cubic-bezier(0.2,0.8,0.2,1), height 750ms cubic-bezier(0.2,0.8,0.2,1)",
                  }}
                />
                <rect x={x} y={M.top} width={barW} height={innerH} fill="transparent" />

                <text
                  x={x + barW / 2}
                  y={M.top + innerH + 22}
                  textAnchor="middle"
                  fontSize="10"
                  fill={hover?.index === i ? "#EAB308" : "#9CA3AF"}
                  fontWeight={hover?.index === i ? "700" : "400"}
                >
                  {label}
                </text>
              </g>
            );
          })}
        </svg>

        {safeData.length === 0 && (
          <div className="absolute inset-0 flex items-center justify-center text-gray-400 text-sm">
            Belum ada data aktivitas.
          </div>
        )}
      </div>
    </div>
  );
}

function mount() {
  const rootEl = document.getElementById("admin-dashboard-chart");
  if (!rootEl) return;

  let payload = {};
  try {
    payload = JSON.parse(rootEl.dataset.payload || "{}");
  } catch (e) {
    console.error("Dashboard JSON parse error:", e);
  }

  const series = pickSeries(payload);

  createRoot(rootEl).render(
    <React.StrictMode>
      <DashboardChart data={series} />
    </React.StrictMode>
  );
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", mount);
} else {
  mount();
}
window.addEventListener("spa:navigated", mount);
