<!DOCTYPE html>

<html>

<head>

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


</style>

</head>


<body>


<div class="header">


<h2>

{{ $restaurantName }}

</h2>


<h3>

Profit & Loss Statement

</h3>


<p>

Period:

{{ $data['period']['from'] }}

To

{{ $data['period']['to'] }}

</p>


</div>





<table>


<tr>

<th>
Particular
</th>


<th>
Amount
</th>

</tr>



<tr>

<td>
Total Revenue
</td>


<td class="amount">

BDT {{ number_format($data['revenue']['total'],2) }}

</td>


</tr>




<tr>

<td>
Total Expense
</td>


<td class="amount">

BDT {{ number_format($data['expenses']['total'],2) }}

</td>


</tr>




<tr class="total">

<td>
Net Profit
</td>


<td class="amount">

BDT {{ number_format($data['net_profit'],2) }}

</td>


</tr>



</table>



</body>

</html>