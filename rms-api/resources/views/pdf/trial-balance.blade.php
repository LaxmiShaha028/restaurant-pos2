<!DOCTYPE html>

<html>

<head>

<meta charset="utf-8">


<style>


body{

    font-family: DejaVu Sans;

    font-size:14px;

}



.header{

    text-align:center;

    margin-bottom:25px;

}



table{

    width:100%;

    border-collapse:collapse;

}



th,td{

    border:1px solid #333;

    padding:8px;

}



.amount{

    text-align:right;

}



.total{

    font-weight:bold;

}



.status{

    margin-top:20px;

    text-align:center;

    font-weight:bold;

}


</style>


</head>



<body>


<div class="header">


<h2>

{{ $restaurantName }}

</h2>


<h3>

Trial Balance

</h3>


<p>

Period:

{{ $data['period']['from'] }}

To

{{ $data['period']['to'] }}

</p>


</div>






<table>


<thead>


<tr>

<th>
Account
</th>


<th>
Debit
</th>


<th>
Credit
</th>


</tr>


</thead>



<tbody>



@foreach($data['accounts'] as $item)


<tr>


<td>

{{ $item['account_name'] }}

</td>


<td class="amount">

BDT {{ number_format($item['debit'],2) }}

</td>



<td class="amount">

BDT {{ number_format($item['credit'],2) }}

</td>


</tr>



@endforeach




<tr class="total">


<td>

Total

</td>


<td class="amount">

BDT {{ number_format($data['total_debit'],2) }}

</td>



<td class="amount">

BDT {{ number_format($data['total_credit'],2) }}

</td>


</tr>



</tbody>


</table>






<div class="status">


@if($data['is_balanced'])

Balanced

@else

Not Balanced

@endif


</div>




</body>


</html>