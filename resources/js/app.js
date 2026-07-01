import "./bootstrap";
// Pastikan baris ini mengimpor CSS
import Alpine from "alpinejs";
// import Swal from "sweetalert2"; perbaiki performa
const Swal = () => import("sweetalert2");
import "../css/app.css";
import collapse from "@alpinejs/collapse";

window.Alpine = Alpine;
window.Swal = Swal;
Alpine.plugin(collapse);
Alpine.start();
