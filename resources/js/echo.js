import axios from "axios";

/**
 * Realtime notifikasi admin TANPA Reverb:
 * - Polling /admin/notifikasi/poll
 * - Update badge navbar
 * - Toast + bunyi (WebAudio) saat ada notif baru
 */

function getBadgeEl() {
    return document.getElementById("adminNotifBadge");
}

function setBadgeCount(count) {
    const el = getBadgeEl();
    if (!el) return;

    const c = Number(count || 0);
    el.dataset.count = String(c);

    if (c > 0) {
        el.classList.remove("hidden");
        el.textContent = c > 9 ? "9+" : String(c);
    } else {
        el.classList.add("hidden");
        el.textContent = "";
    }
}

// Bunyi beep tanpa file audio (tidak perlu mp3)
function beep() {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;

        const ctx = new AudioCtx();
        const o = ctx.createOscillator();
        const g = ctx.createGain();

        o.type = "sine";
        o.frequency.value = 880; // nada

        g.gain.value = 0.08; // volume kecil
        o.connect(g);
        g.connect(ctx.destination);

        o.start();

        // fade out cepat
        setTimeout(() => {
            o.stop();
            ctx.close?.();
        }, 160);
    } catch (e) {
        // ignore
    }
}

function toastNotification(title, body) {
    // pakai SweetAlert kalau ada, mengikuti style project Anda
    if (typeof window.Swal !== "undefined") {
        window.Swal.fire({
            toast: true,
            position: "top-end",
            icon: "info",
            title: title || "Notifikasi",
            text: body || "",
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            background: "#21160F",
            color: "#F8F2E7",
        });
        return;
    }

    // fallback: alert biasa
    // eslint-disable-next-line no-alert
    alert(`${title}\n\n${body}`);
}

function isAdminPage() {
    return window.__auth?.role === "admin";
}

let sinceId = 0;
let started = false;

async function pollOnce() {
    if (!isAdminPage()) return;

    const res = await axios.get("/admin/notifikasi/poll", {
        params: { since_id: sinceId },
        headers: { "X-Requested-With": "XMLHttpRequest" },
    });

    if (!res?.data?.success) return;

    const unread = Number(res.data.unreadCount || 0);
    setBadgeCount(unread);

    const newItems = Array.isArray(res.data.newItems) ? res.data.newItems : [];
    const maxId = Number(res.data.maxId || sinceId);

    // Jika ada notif baru, bunyi + toast (ambil satu yang paling terbaru)
    if (newItems.length > 0) {
        // update sinceId dulu (biar tidak double)
        sinceId = maxId;

        // notif baru biasanya beberapa; tampilkan 1 toast untuk yang terbaru
        const newest = newItems[0];
        beep();
        toastNotification(newest.title, newest.body);

        // optional: jika Anda mau auto update dropdown navbar, Anda bisa implement nanti.
        // Untuk sekarang: badge + toast + bunyi sudah realtime.
    } else {
        sinceId = maxId;
    }
}

function bootstrapSinceIdFromDOM() {
    // Ambil notif terbesar dari halaman notifikasi admin bila sedang dibuka.
    // Kalau tidak ada, biarkan 0, polling tetap jalan.
    const el = document.querySelector("[data-notif-max-id]");
    if (el) {
        const v = Number(el.getAttribute("data-notif-max-id") || 0);
        if (!Number.isNaN(v) && v > sinceId) sinceId = v;
    }
}

export function startAdminNotifPolling() {
    if (started) return;
    started = true;

    bootstrapSinceIdFromDOM();

    // poll cepat tapi aman
    pollOnce().catch(() => {});
    setInterval(() => {
        pollOnce().catch(() => {});
    }, 5000);
}

// auto start
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () =>
        startAdminNotifPolling()
    );
} else {
    startAdminNotifPolling();
}
