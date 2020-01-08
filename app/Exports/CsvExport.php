<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Tryout;
use App\TryoutPlayers;
use DB;
class CsvExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Tryout::all();
    }

    public function headings(): array
    {
        return [
            'id',
            'team_id',
            'street',
            'state',
            'zipcode',
            'timeoftryout',
            'dateoftryout',
            'costoftryout',
            'latitude',
            'longitude',
            'created_at',
            'updated_at'
        ];
    }
}
