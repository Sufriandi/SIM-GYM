<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AbsensiReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private readonly Collection $rows,
        private readonly array $filters
    ) {}

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Jam Masuk',
            'ID Member',
            'Nama Member',
            'Keterangan',
            'Dibuat Pada',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        $tanggal = $row->tanggal ?? null;
        $jam     = $row->jam_masuk ?? null;

        $nama = $row->user_name ?? $row->member_nama ?? '-';

        return [
            $no,
            $tanggal ? (string) $tanggal : '-',
            $jam ? (string) $jam : '-',
            $row->member_id ?? '-',
            $nama,
            $row->keterangan ?? '-',
            $row->created_at ? (string) $row->created_at : '-',
        ];
    }
}
