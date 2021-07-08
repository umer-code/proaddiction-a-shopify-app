<?php

use Illuminate\Support\Facades\Route;
use App\Customer;
use App\Country;
use App\State;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::group(['middleware' => ['auth.shopify']], function () {
    Route::get('/', function ()
    {
       return view('dashboard');
    })->name('home');
    Route::get('/customers', function ()
    {
        $customers = Customer::latest()->paginate(20);
        $countries = Country::all();
        return view('customers', compact('customers', 'countries'));
    })->name('customers');
    Route::get('/sync-customer', 'CustomerController@ShopifyCustomers')->name('sync-customer');
    Route::post('/add-agent', 'CustomerController@addAgent')->name('add_agent');
    Route::get('/orders', 'OrderController@showAll')->name('orders');
    Route::get('/sync-orders', 'OrderController@syncOrders')->name('sync_orders');
    Route::post('show-states', function (Request $request)
    {
        $input = Request::all();
        if(Request::isMethod('post') && Request::ajax()) {
            if($input['countryName']) {
                $country = Country::where('name', $input['countryName'])->first();

                $states = $country->states;
                $returnHTML = view('customer.state', ['states' => $states])->render();
                return response()->json( array('success' => true, 'html'=>$returnHTML) );
            }
        }
    })->name('show_states');
    Route::post('show-city', function (Request $request)
    {
        $input = Request::all();
        if(Request::isMethod('post') && Request::ajax()) {
            if($input['cityName']) {
                $states = State::where('name', $input['cityName'])->first();
                $cities = $states->cities;
                $returnHTML = view('customer.city', ['cities' => $cities])->render();
                return response()->json( array('success' => true, 'html'=>$returnHTML) );
            }
        }
    })->name('show_cities');

    Route::post('/refund', 'OrderController@makeRefund')->name('make_refund');
    Route::get('/analytics', 'AnalyticsController@showAnalytics')->name('analytics');
});
