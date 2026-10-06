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

    margin-bottom:30px;

}


.restaurant-name{

    font-size:22px;

    font-weight:bold;

    margin-bottom:8px;

}


.title{

    font-size:18px;

    font-weight:bold;

}



table{

    width:100%;

    border-collapse:collapse;

}



th,td{

    border:1px solid #333;

    padding:10px;

}



.amount{

    text-align:right;

}



.total{

    font-weight:bold;

}



</style>


</head>


<body>


<div class="header">


<div class="restaurant-name">

{{ $restaurantName ?? 'Restaurant Name' }}

</div>


<div class="title">

Owner Ledger Report

</div>


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
Particular
</th>


<th>
Amount
</th>


</tr>


</thead>



<tbody>



<tr>

<td>
Revenue
</td>

<td class="amount">

BDT {{ number_format($data['summary']['revenue'],2) }}

</td>

</tr>



<tr>

<td>
Purchase
</td>

<td class="amount">

BDT {{ number_format($data['summary']['purchase'],2) }}

</td>

</tr>



<tr>

<td>
Purchase Return
</td>

<td class="amount">

BDT {{ number_format($data['summary']['purchase_return'],2) }}

</td>

</tr>



<tr>

<td>
Food Cost
</td>

<td class="amount">

BDT {{ number_format($data['summary']['food_cost'],2) }}

</td>

</tr>



<tr>

<td>
Expense
</td>

<td class="amount">

BDT {{ number_format($data['summary']['expense'],2) }}

</td>

</tr>




<tr class="total">

<td>
Net Profit
</td>

<td class="amount">

BDT {{ number_format($data['summary']['profit'],2) }}

</td>

</tr>



</tbody>


</table>



</body>


</html>