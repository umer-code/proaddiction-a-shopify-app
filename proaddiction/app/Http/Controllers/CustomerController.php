<?php

namespace App\Http\Controllers;

use App\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function ShopifyCustomers($next = null){
        $shop = Auth::user();
        $customers = $shop->api()->rest('GET', '/admin/customers.json', [
            'limit' => 250,
            'page_info' => $next
        ]);
        $customers = json_decode(json_encode($customers));
        foreach ($customers->body->customers as $customer){
            $this->createShopifyCustomer($customer, $shop);
        }
        if (isset($customers->link->next)) {
            $this->ShopifyCustomers($customers->link->next);
        }
        return redirect()->back()->with('success', 'Customers synced successfully');
    }

    public function createShopifyCustomer($customer, $shop){
        $c = Customer::where('shopify_id', $customer->id)->first();
        if($c === null){
            $c = new Customer();
        }
        $c->shopify_id = $customer->id;
        if($c->email) {
            $c->email = $customer->email;
        }else{
            $c->email = 'none';
        }
        $c->first_name = $customer->first_name;
        $c->last_name = $customer->last_name;
        $c->email = $customer->email;
        $c->phone_no = $customer->phone;
        if(isset($customer->addresses[0])) {
            $c->seller_code = $customer->addresses[0]->zip;
            $c->seller_area = $customer->addresses[0]->address1 . ' ' . $customer->addresses[0]->address2;
        }
        $c->shop_id = $shop->id;
        $c->save();
    }

    /*public function storeCustomersToDB($request)
    {
        $customers = json_decode($request['response']->getBody(), JSON_PRETTY_PRINT);
        if (count($customers) > 0)
        {
            $customerInDb = Customer::query();
            foreach ($customers as $indexs)
            {
                foreach ($indexs as $customer)
                {
                    if (!$customerInDb->where('shopify_customer_id', $customer['id'])->exists()) {
                        $customerInDb->create([
                            'shopify_customer_id' => $customer['id'],
                            'name' => $customer['first_name'] . ' ' . $customer['last_name'],
                            'email' => $customer['email'],
                            'phone_no' => $customer['phone'],
                            'seller_code' => $customer['addresses'][0]['zip'],
                            'seller_area' => $customer['addresses'][0]['address1'] . ' ' . $customer['addresses'][0]['address2'],
                        ]);
                    }
                    else {
                        $customerInDb->where('shopify_customer_id', $customer['id'])->update([
                            'shopify_customer_id' => $customer['id'],
                            'name' => $customer['first_name'] . ' ' . $customer['last_name'],
                            'email' => $customer['email'],
                            'phone_no' => $customer['phone'],
                            'seller_code' => $customer['addresses'][0]['zip'],
                            'seller_area' => $customer['addresses'][0]['address1'] . ' ' . $customer['addresses'][0]['address2'],
                        ]);
                    }
                }
            }
        }
    }*/

    public function addAgent(Request $request)
    {
        $request -> validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|unique:customers',
            'phone_no' => 'required|unique:customers',
        ]);
        $shop = Auth::user();
        if (isset($request->discount)) {
            list($couponCode, $price_rule_id) = $this->createDiscount($request, $shop);
        }
        else
            $couponCode = 'none';
        Customer::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'seller_code' => $request->seller_code,
            'seller_area' => $request->city.' '.$request->state.' '.$request->country,
            'phone_no' => $request->phone_no,
            'is_agent' => 1,
            'shop_id' => $shop->id,
            'coupon_code' => $couponCode,
            'discount' => $request->discount,
            'seller_color' => $request->seller_color,
            'commission' => $request->commission_rate,
            'price_rule_id' => $price_rule_id,
        ]);
         $customers = $shop->api()->rest('post', '/admin/customers.json', [
            'customer' => [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone_no,
                'addresses' =>  [ 0 => [
                    'zip' => $request->seller_code,
                    'address1' => $request->city.' '.$request->state.' '.$request->country,
                ]
                ],
                "metafields" =>
                    array(
                        0 =>
                            array(
                                "key" => 'is_agent',
                                "value"=> 1,
                                "value_type"=> "boolean",
                                "namespace"=> "customers",
                            ),
                        1 =>
                            array(
                                "key" => 'discount',
                                "value"=> $request->discount,
                                "value_type"=> "float",
                                "namespace"=> "customers",
                            ),
                        2 =>
                            array(
                                "key" => 'commission',
                                "value"=> $request->commission_rate,
                                "value_type"=> "float",
                                "namespace"=> "customers",
                            ),
                        3 =>
                            array(
                                "key" => 'seller_code',
                                "value"=> $request->seller_code,
                                "value_type"=> "string",
                                "namespace"=> "customers",
                            ),
                    ),

                ]
            ]);
        return redirect()->back()->with('success', 'Customer Added Successfully');
    //    $customers = json_decode(json_encode($customers));
    //  dd($shop->api()->rest('get', '/admin/customers/'.$customers->body->customer->id.'/metafields.json'));

    }

    public function createDiscount($request, $shop)
    {
        $priceRule = $shop->api()->rest('post', '/admin/price_rules.json', [
            'price_rule' => [
                'title' => "$request->discount"."OFFONEACHITEM".Carbon::now(),
                'customer_selection' => 'all',
                'value' => '-'.$request->discount,
                'value_type' => 'percentage',
                'target_type' => "line_item",
                'target_selection' => "all",
                "allocation_method" => "across",
                'starts_at' => Carbon::now(),
            ]
        ]);
        $priceRule = json_decode(json_encode($priceRule));
        $discountCode = $shop->api()->rest('post', '/admin/price_rules/'. $priceRule->body->price_rule->id .'/discount_codes.json', [
            'discount_code' => [
                'code' => $priceRule->body->price_rule->title,
            ]
        ]);
        $discountCode = json_decode(json_encode($discountCode));
        if(!$discountCode->errors)
        {
            return [$discountCode->body->discount_code->code, $discountCode->body->discount_code->price_rule_id];
        }
        return 'none';
    }
}
