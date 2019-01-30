<?php

namespace App\Http\Controllers;

use function GuzzleHttp\Psr7\str;
use Illuminate\Http\Request;
use App\availabilities;
use App\prices;
use Illuminate\Support\Facades\DB;

class LOS_Controller extends Controller
{
    public function index()
    {
        $losData = self::make_LOS_table();
        return view('LOS2', [
            'losPrices' => $losData['losPrices'],
            'maximum_stay' => $losData['maximum_stay'],
        ]);
    }

    public function indextest()
    {
        $losData = self::test();
        return view('LOS2', [
            'losPrices' => $losData['losPrices'],
            'maximum_stay' => $losData['maximum_stay'],
        ]);
    }

    public function test(){}

    /**
     * @return array
     */
    public function make_LOS_table()
    {
        $maximum_stay = 21;
        $prices = DB::table('prices')->get();
        $quantities = DB::table('availabilities')->select('date','quantity')->get()->keyBy('date');

        for ($nights = 1; $nights <= $maximum_stay; $nights++) {

            foreach ($prices as $priceRow) {

                if ($nights >= $priceRow->minimum_stay and $nights <= $priceRow->maximum_stay and $nights % $priceRow->duration == 0){

                    $personsList = explode("|", $priceRow->persons);

                    foreach ($personsList as $factor => $persons){

                        $price = $nights*( $priceRow->amount/$priceRow->duration + $factor*$priceRow->extra_person_price );

                        $from = new \DateTimeImmutable($priceRow->period_from);
                        $till = new \DateTimeImmutable($priceRow->period_till);

                        for($date = $from; $date <= $till; $date = $date->add(new \DateInterval('P1D'))) {

                            $dateFormat = $date->format('Y-m-d');
                            $quantity = $quantities[$dateFormat]->quantity;

                            if($quantity == 0){
                                $losPrices[$dateFormat][$persons][$nights] = 0;
                            }

                            // You are always able to buy a given period of days.

                            elseif ($nights == $priceRow->duration or !isset($refNights)){

                                $losPrices[$dateFormat][$persons][$nights] = round($price);

                                // RefNights and refType remember the last night for which
                                // the price was calculated and makes sure that after e.g.
                                // a pricing duration of x nights is passed or when we reach
                                // the end of the period, we start using data down the table.
                                $refNights[$dateFormat] = $nights;
                                $refType[$dateFormat] = "duration";
                            }

                            elseif( $date->diff($till)->days + 1 >= $nights and
                                    ( $priceRow->minimum_stay > $refNights[$dateFormat] or $refType[$dateFormat] == "end" ) ){

                                $losPrices[$dateFormat][$persons][$nights] = round($price);
                                $refNights[$dateFormat] = $nights;
                                $refType[$dateFormat] = "end";
                            }

                            // Here the price of the reference night is added to the data
                            // further down the table (if it exists and is not equal to 0).
                            else{

                                $newNights = $nights - $refNights[$dateFormat];
                                $newDate = $date->add(new \DateInterval('P'.$refNights[$dateFormat].'D'))->format('Y-m-d');

                                $losPrices[$dateFormat][$persons][$nights] =
                                (array_key_exists($newDate, $losPrices) and $losPrices[$newDate][$persons][$newNights] != 0) ?
                                $losPrices[$dateFormat][$persons][ $refNights[$dateFormat] ] + $losPrices[$newDate][$persons][$newNights] : 0;
                            }
                        }
                    }
                }
            }
        }
        return ['losPrices' => $losPrices, 'maximum_stay' => $maximum_stay];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
