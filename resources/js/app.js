import "./bootstrap";
// Pastikan baris ini mengimpor CSS
import Alpine from "alpinejs";
import Swal from "sweetalert2";
import "../css/app.css";
import { mountAdminDashboardStats } from "./admin/dashboard";
mountAdminDashboardStats();

window.Alpine = Alpine;
window.Swal = Swal;

Alpine.start();
