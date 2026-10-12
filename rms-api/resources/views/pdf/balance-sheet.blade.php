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



.section{

    background:#eeeeee;

    font-weight:bold;

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

Balance Sheet

</h3>


</div>





<h4>

Assets

</h4>



<table>


<tr>

<th>
Account
</th>


<th>
Amount
</th>


</tr>



@foreach($data['assets']['details'] as $item)


<tr>


<td>

{{ $item['account_name'] }}

</td>


<td class="amount">

BDT {{ number_format($item['balance'],2) }}

</td>


</tr>


@endforeach




<tr class="total">

<td>

Total Assets

</td>


<td class="amount">

BDT {{ number_format($data['assets']['total'],2) }}

</td>

</tr>



</table>







<br>



<h4>

Liabilities

</h4>



<table>


<tr>

<th>

Account

</th>


<th>

Amount

</th>


</tr>




@foreach($data['liabilities']['details'] as $item)


<tr>


<td>

{{ $item['account_name'] }}

</td>


<td class="amount">

BDT {{ number_format($item['balance'],2) }}

</td>


</tr>


@endforeach





<tr class="total">

<td>

Total Liabilities

</td>


<td class="amount">

BDT {{ number_format($data['liabilities']['total'],2) }}

</td>


</tr>


</table>







<br>




<h4>

Equity

</h4>



<table>


<tr>

<th>

Account

</th>


<th>

Amount

</th>


</tr>





@foreach($data['equity']['details'] as $item)


<tr>


<td>

{{ $item['account_name'] }}

</td>


<td class="amount">

BDT {{ number_format($item['balance'],2) }}

</td>


</tr>


@endforeach






<tr class="total">

<td>

Total Equity

</td>


<td class="amount">

BDT {{ number_format($data['equity']['total'],2) }}

</td>


</tr>



</table>





</body>

</html>