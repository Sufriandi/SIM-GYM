<?php

namespace Database\Seeders;

use App\Models\Produk;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        // Sesuai migration enum: ['minuman','suplemen','lainnya']
        $allowedKategori = ['minuman', 'suplemen', 'lainnya'];

        // Rapikan deskripsi agar konsisten
        $clean = function (?string $text): ?string {
            $text = trim((string) $text);
            if ($text === '') return null;

            $text = str_replace(["\r\n", "\r"], "\n", $text);
            $text = preg_replace("/[ \t]+/", " ", $text);
            $text = preg_replace("/\n{3,}/", "\n\n", $text);

            // Hapus variation selectors (kadang ikut dari copy-paste)
            $text = preg_replace('/[\x{FE00}-\x{FE0F}]/u', '', $text);

            return trim($text);
        };

        // Bersihkan data lama (dev)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('produks')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $items = [
            [
                'nama' => 'Evolene Isolene 12 Sachets 396 grams Whey Protein Isolate - Chocolate',
                'kategori' => 'suplemen',
                'harga' => 220000,
                'stok' => 5,
                'deskripsi' => $clean(
                    "Whey protein isolate dalam kemasan sachet yang praktis untuk pemenuhan protein harian dan pemulihan setelah latihan.\n\n" .
                        "• Tinggi protein, rendah lemak dan karbohidrat.\n" .
                        "• Cocok untuk program pembentukan otot dan manajemen kalori.\n" .
                        "• Saran konsumsi: 1 sachet setelah latihan atau di antara waktu makan."
                ),
            ],
            [
                'nama' => 'OnSnack Bars - Cemilan Sehat Natural dengan Tempe',
                'kategori' => 'lainnya',
                'harga' => 12000,
                'stok' => 10,
                'deskripsi' => $clean(
                    "Snack bar berbahan tempe yang praktis untuk camilan sehat harian.\n\n" .
                        "• Sumber protein nabati dan serat.\n" .
                        "• Cocok sebagai alternatif camilan tinggi gula.\n" .
                        "• Nikmati kapan saja: sebelum/sesudah latihan atau saat beraktivitas."
                ),
            ],
            [
                'nama' => 'EVOLENE Isolene Sachet 33g',
                'kategori' => 'suplemen',
                'harga' => 26000,
                'stok' => 5,
                'deskripsi' => $clean(
                    "Whey protein isolate sachet (single serving) untuk dukung kebutuhan protein harian secara praktis.\n\n" .
                        "• Mudah dibawa dan mudah disajikan.\n" .
                        "• Cocok untuk after workout atau tambahan protein di sela makan.\n" .
                        "• Campur dengan air suhu ruang sesuai selera."
                ),
            ],
            [
                'nama' => 'L-Men Protein Bar Strongberry 7 Gram',
                'kategori' => 'lainnya',
                'harga' => 11500,
                'stok' => 10,
                'deskripsi' => $clean(
                    "Protein bar rasa Strongberry untuk camilan praktis yang mendukung gaya hidup aktif.\n\n" .
                        "• Cocok untuk snack sebelum/sesudah latihan.\n" .
                        "• Membantu menambah asupan protein harian.\n" .
                        "• Praktis dibawa untuk aktivitas di luar."
                ),
            ],
            [
                'nama' => 'Evolene Prevo',
                'kategori' => 'suplemen',
                'harga' => 56000,
                'stok' => 5,
                'deskripsi' => $clean(
                    "Suplemen pre-workout untuk membantu performa latihan (fokus, tenaga, dan daya tahan).\n\n" .
                        "• Cocok digunakan sebelum sesi latihan intens.\n" .
                        "• Gunakan sesuai anjuran pada label produk.\n" .
                        "• Disarankan tidak dikonsumsi menjelang waktu tidur."
                ),
            ],
            [
                'nama' => 'Susu Kedelai',
                'kategori' => 'minuman',
                'harga' => 6000,
                'stok' => 20,
                'deskripsi' => $clean(
                    "Minuman susu kedelai yang cocok untuk pendamping aktivitas harian.\n\n" .
                        "• Alternatif minuman berbasis nabati.\n" .
                        "• Nikmat diminum dingin.\n" .
                        "• Cocok sebagai teman sarapan atau snack sore."
                ),
            ],
            [
                'nama' => 'BCAA Evolene Ecer 1 Sachet 1 Serving Apel Hijau Carnitine Glutamine',
                'kategori' => 'suplemen',
                'harga' => 12000,
                'stok' => 20,
                'deskripsi' => $clean(
                    "Suplemen BCAA single serving dengan tambahan glutamine untuk mendukung pemulihan dan hidrasi saat latihan.\n\n" .
                        "• Cocok diminum saat latihan atau setelah latihan.\n" .
                        "• Praktis: 1 sachet untuk 1 serving.\n" .
                        "• Ikuti aturan pakai pada label."
                ),
            ],
            [
                'nama' => 'Evomas Eceran',
                'kategori' => 'suplemen',
                'harga' => 10000,
                'stok' => 15,
                'deskripsi' => $clean(
                    "Mass gainer eceran untuk membantu menambah kalori dan asupan nutrisi.\n\n" .
                        "• Cocok untuk program bulking/penambahan berat badan.\n" .
                        "• Konsumsi di antara waktu makan atau setelah latihan.\n" .
                        "• Sesuaikan takaran dengan anjuran produk."
                ),
            ],
            [
                'nama' => 'VECTORLABS MASTER WHEY SACHET',
                'kategori' => 'suplemen',
                'harga' => 10000,
                'stok' => 15,
                'deskripsi' => $clean(
                    "Whey protein sachet (single serving) yang praktis untuk tambahan protein harian.\n\n" .
                        "• Mendukung pemulihan setelah latihan.\n" .
                        "• Mudah disajikan.\n" .
                        "• Campur dengan air atau susu sesuai selera."
                ),
            ],
            [
                'nama' => 'Evowhey Eceran',
                'kategori' => 'suplemen',
                'harga' => 10000,
                'stok' => 10,
                'deskripsi' => $clean(
                    "Whey protein eceran untuk membantu pemenuhan protein harian.\n\n" .
                        "• Cocok untuk program pembentukan otot.\n" .
                        "• Disarankan dikonsumsi setelah latihan.\n" .
                        "• Campur dan kocok hingga larut."
                ),
            ],
            [
                'nama' => 'Amino Mutant 300 Tablet Amino 2000 Amino 2222',
                'kategori' => 'suplemen',
                'harga' => 619000,
                'stok' => 5,
                'deskripsi' => $clean(
                    "Suplemen amino dalam bentuk tablet untuk mendukung kebutuhan asam amino harian.\n\n" .
                        "• Cocok untuk dukung recovery dan nutrisi latihan.\n" .
                        "• Konsumsi sesuai anjuran label.\n" .
                        "• Pastikan hidrasi tercukupi."
                ),
            ],
            [
                'nama' => 'Le Minerale',
                'kategori' => 'minuman',
                'harga' => 4000,
                'stok' => 15,
                'deskripsi' => $clean(
                    "Air mineral untuk membantu menjaga hidrasi.\n\n" .
                        "• Cocok untuk pendamping latihan.\n" .
                        "• Praktis dibawa.\n" .
                        "• Nikmati dingin untuk sensasi lebih segar."
                ),
            ],
            [
                'nama' => 'Aqua',
                'kategori' => 'minuman',
                'harga' => 4000,
                'stok' => 15,
                'deskripsi' => $clean(
                    "Air mineral siap minum untuk membantu menjaga hidrasi tubuh.\n\n" .
                        "• Cocok dikonsumsi sebelum, saat, dan setelah latihan.\n" .
                        "• Praktis untuk aktivitas harian."
                ),
            ],
            [
                'nama' => 'Pocari Sweat',
                'kategori' => 'minuman',
                'harga' => 6000,
                'stok' => 15,
                'deskripsi' => $clean(
                    "Minuman isotonik untuk membantu mengganti cairan dan elektrolit.\n\n" .
                        "• Cocok setelah olahraga atau saat banyak berkeringat.\n" .
                        "• Nikmati dingin."
                ),
            ],
            [
                'nama' => 'Cleo',
                'kategori' => 'minuman',
                'harga' => 3000,
                'stok' => 0,
                'deskripsi' => $clean(
                    "Air mineral untuk membantu menjaga hidrasi tubuh sehari-hari.\n\n" .
                        "• Cocok untuk aktivitas harian maupun latihan.\n" .
                        "• Praktis dibawa."
                ),
            ],
        ];

        foreach ($items as $item) {
            $kategori = $item['kategori'] ?? 'lainnya';
            if (!in_array($kategori, $allowedKategori, true)) {
                $kategori = 'lainnya';
            }

            Produk::create([
                'foto'      => null,              // DISENGAJA KOSONG
                'nama'      => $item['nama'],
                'kategori'  => $kategori,
                'harga'     => (int) $item['harga'],
                'stok'      => (int) ($item['stok'] ?? 0),
                'deskripsi' => $item['deskripsi'] ?? null,
            ]);
        }
    }
}
