<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #999;
            padding: 5px;
            vertical-align: top;
        }
        thead tr:first-child th {
            background-color: #2e4d19;
            color: white;
            font-size: 16px;
            text-align: center;
            font-weight: bold;
        }
        thead tr:nth-child(2) th,
        thead tr:nth-child(3) th {
            background-color: #2e4d19;
            color: white;
            text-align: center;
            font-weight: bold;
        }
        thead tr:nth-child(3) th {
            background-color: #2e4d19;
            color: white;
        }
        tbody tr:nth-child(even) {
            background-color: #fdf5e6;
        }
        tfoot td {
            font-weight: bold;
            background-color: #2e4d19;
            color: white;
        }
    </style>
</head>
<body>

<table cellpadding="5" cellspacing="0" style="width:100%; border-collapse: collapse; text-align:center;">
    <thead>
        <tr>
            <th colspan="19">{{ $data['ppmp_application']['year'] }} PROJECT PROCUREMENT MANAGEMENT PLAN</th>
            <th colspan="2">PPMP TOTAL</th>
        </tr>
        <tr>
            <th>GENERAL DESCRIPTION</th>
            <th>CLASSIFICATION</th>
            <th>ITEM CATEGORY</th>
            <th>QTY</th>
            <th>UNIT</th>
            <th>ESTIMATED BUDGET</th>
            <th>TOTAL AMOUNT</th>
            <th colspan="12">SCHEDULE / MILESTONE</th>
            <th>MODE OF PROCUREMENT</th>
            <th>REMARKS</th>
        </tr>
        <tr>
            <th colspan="7"></th>
            <th>JAN</th>
            <th>FEB</th>
            <th>MAR</th>
            <th>APR</th>
            <th>MAY</th>
            <th>JUN</th>
            <th>JUL</th>
            <th>AUG</th>
            <th>SEP</th>
            <th>OCT</th>
            <th>NOV</th>
            <th>DEC</th>
            <th colspan="2"></th>
        </tr>
    </thead>
    <tbody>
        @php $ppmpTotal = 0; @endphp

        @foreach($data['ppmp_items'] as $ppmpitem)
            @php
                $totalAmount = $ppmpitem->item->estimated_budget * $ppmpitem->total_quantity;
                $ppmpTotal += $totalAmount;
            @endphp

            <tr>
                <td> {{ $ppmpitem->item->name ?? '-' }}</td>
                <td> {{ $ppmpitem->item->itemClassification->name ?? '-' }} </td>
                <td> {{ $ppmpitem->item->itemCategory->name ?? '-' }} </td>
                <td style="text-align: center;">{{ $ppmpitem->total_quantity == 0 ? '-' : $ppmpitem->total_quantity }}</td>
                <td> {{ $ppmpitem->item->itemUnit->code ?? '-' }}</td>
                <td style="text-align: right;">{{ number_format($ppmpitem->item->estimated_budget, 2) ?? '-' }}</td>
                <td style="text-align: right;">{{ number_format($ppmpitem->item->total_amount, 2) ?? '-' }}</td>

                @for ($month = 1; $month <= 12; $month++)
                    @php
                        $quantity = 0;
                        foreach ($ppmpitem->ppmpSchedule as $sched) {
                            if ((int)$sched['month'] === $month) {
                                $quantity = $sched->quantity == 0 ? '-' : $sched->quantity;
                                break;
                            } else {
                                $quantity = '-';
                            }
                        }
                    @endphp
                    <td style="text-align: center;">{{ $quantity }}</td>
                @endfor

                <td>{{ $ppmpitem->procurementMode->name ?? '-' }}</td>
                <td>{{ $ppmpitem->remarks ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" style="text-align: right;">Grand Total:</td>
            <td style="text-align: right;"> {{ number_format($ppmpTotal, 2) }} </td>
            <td colspan="14"></td>
        </tr>
    </tfoot>
</table>

</body>
</html>
