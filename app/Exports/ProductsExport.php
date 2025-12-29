<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Events\AfterSheet;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting, WithEvents
{
    public function collection()
    {
        // Load all products with category and sort by category name then product name
        $products = Product::with('category')->get();
        $sorted = $products->sortBy(function ($p) {
            $cat = $p->category ? $p->category->name : '';
            return [$cat, $p->name];
        });
        return $sorted->values();
    }

    public function headings(): array
    {
        return [
            'ID_PRODUIT',
            'NOM_PRODUIT',
            'CATEGORIE',
            'PRIX',
            'STOCK',
            'STATUT',
            'DATE_CREATION',
            'DATE_MISE_A_JOUR',
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->category ? $product->category->name : '',
            $product->price,
            $product->stock,
            $product->state ?? '',
            $product->created_at ? $product->created_at->format('d/m/Y') : '',
            $product->updated_at ? $product->updated_at->format('d/m/Y') : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header style
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B71C1C']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => '#,##0.00 "DH"', // price
            'G' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'H' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // Apply thin borders to all cells
                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(['rgb' => 'BBBBBB']));

                // Header row: ensure background (red) and white text
                $sheet->getStyle("A1:{$highestColumn}1")->getFont()->getColor()->setRGB('FFFFFF');
                $sheet->getStyle("A1:{$highestColumn}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('B71C1C');
                $sheet->getStyle("A1:{$highestColumn}1")->getAlignment()->setHorizontal('center')->setVertical('center');

                // Dark zebra striping for rows (2..end)
                for ($row = 2; $row <= $highestRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('3B3B3B');
                    } else {
                        $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2F2F2F');
                    }
                    // set data font color to light gray for readability
                    $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->getFont()->getColor()->setRGB('EEEEEE');
                    // vertical align center
                    $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->getAlignment()->setVertical('center');
                }

                // Header row height
                $sheet->getRowDimension(1)->setRowHeight(26);
            },
        ];
    }
}
