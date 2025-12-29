<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Events\AfterSheet;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    public function collection()
    {
        return Order::with(['items', 'subOrders.seller'])->orderBy('id')->get();
    }

    public function headings(): array
    {
        return [
            'ID_COMMANDE',
            'REFERENCE',
            'CLIENT',
            'MONTANT',
            'STATUT',
            'PAIEMENT',
            'DATE_CREATION',
        ];
    }

    public function map($order): array
    {
        return [
            $order->id,
            $order->secure_token ?? '',
            str_replace(';', ',', $order->customer_name ?? ''),
            number_format($order->total_amount, 2, '.', ''),
            $order->status ?? '',
            $order->payment_status ?? '',
            $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B71C1C']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(['rgb' => 'BBBBBB']));

                // Header
                $sheet->getStyle("A1:{$highestColumn}1")->getFont()->getColor()->setRGB('FFFFFF');
                $sheet->getStyle("A1:{$highestColumn}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('B71C1C');

                // Zebra rows
                for ($row = 2; $row <= $highestRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('3B3B3B');
                    } else {
                        $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2F2F2F');
                    }
                    $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->getFont()->getColor()->setRGB('EEEEEE');
                    $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->getAlignment()->setVertical('center');
                }

                $sheet->getRowDimension(1)->setRowHeight(26);
            },
        ];
    }
}
