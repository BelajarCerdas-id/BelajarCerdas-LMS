<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class ExtracurricularTemplateExport implements FromArray
{
    public function array(): array
    {
        return [

            [
                'Nama Ekstrakurikuler',
                'Deskripsi',
                'Tipe',
                'Pembina'
            ],

            [
                'Pramuka',
                'Ekstrakurikuler wajib',
                'wajib',
                'Budi Santoso'
            ],

            [
                'Basket',
                'Ekstrakurikuler olahraga',
                'pilihan',
                'Andi Wijaya'
            ]

        ];
    }
}