<x-layouts.admin title="Audit Gabungan">
    <div class="space-y-6">
        @include('admin.laporan.keuangan.partials.tabs')

        <div class="rounded-3xl border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">Data Transaksi Mentah</h3>
                <span class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded">Read-Only Audit</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-white uppercase text-xs font-semibold text-gray-500">
                        <tr>
                            <th class="px-6 py-4">Timestamp</th>
                            <th class="px-6 py-4">Sumber</th>
                            <th class="px-6 py-4">Nota / Ref</th>
                            <th class="px-6 py-4">Keterangan</th>
                            <th class="px-6 py-4 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($rows as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    {{ \Carbon\Carbon::parse($row->tanggal_transaksi)->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="uppercase text-xs font-bold tracking-wider 
                                    {{ $row->sumber == 'produk' ? 'text-emerald-600' : ($row->sumber == 'membership' ? 'text-blue-600' : 'text-amber-600') }}">
                                        {{ $row->sumber }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs">{{ $row->no_nota }}</td>
                                <td class="px-6 py-4 text-gray-400 italic">{{ $row->buyer_name }}</td>
                                <td class="px-6 py-4 text-right font-bold text-gray-900">Rp
                                    {{ number_format($row->total, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $rows->links() }}</div>
        </div>
    </div>
</x-layouts.admin>
