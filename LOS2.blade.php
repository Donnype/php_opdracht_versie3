<table border='1'>
    <tr>
        <td>date</td><td>persons</td>

        @for($i = 1; $i <= $maximum_stay; $i++)
            <td> {{$i}} nights</td>
        @endfor
    </tr>

    @foreach($losPrices as $dateString => $dateList)
        @foreach($dateList as $personsString => $personsList)
        <tr>
            <td>{{@$dateString}}</td>

            <td>{{@$personsString}}</td>

            @foreach($personsList as $cell)
                <td>{{@$cell/100}}</td>
            @endforeach

        </tr>
        @endforeach
    @endforeach

</table>
