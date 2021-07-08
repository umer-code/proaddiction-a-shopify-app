@extends('layouts.main')
@section('content')
    @if( Session::has( 'success' ))
        {{ Session::get( 'success' ) }}
        <br>
    @elseif( Session::has( 'errors' ))
        {{ Session::get( 'warning' ) }}
        <br>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <br>
    @endif
    <div>

    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add New Agent</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{route('add_agent')}}" method="POST">
                        @csrf
                        <div class="block-content font-size-sm">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <div class="form-material">
                                        <label for="material-error">First Name</label>
                                        <input  class="form-control" type="text"  name="first_name"
                                                value=""   placeholder="Enter Your First Name">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <div class="form-material">
                                    <label for="material-error">Last Name </label>
                                    <input  class="form-control" type="text"  name="last_name"
                                            value=""   placeholder="Enter Your Last Name">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <div class="form-material">
                                    <label for="material-error">Email </label>
                                    <input  class="form-control" type="text"  name="email"
                                            value=""   placeholder="Enter Your Email Address">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <div class="form-material">
                                    <label for="material-error">Phone Number </label>
                                    <input  class="form-control" type="text"  name="phone_no"
                                            value=""   placeholder="Enter Your Phone Number">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <div class="form-material">
                                    <label for="material-error">Country </label>
                                    <select id="country" name="country" class="form-control" onchange="showStates(this.value);">
                                        @foreach($countries as $country)
                                            <option name="country" value="{{$country->name}}">{{$country->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div id="show_state"></div>
                        <div id="show_cities"></div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <div class="form-material">
                                    <label for="material-error">Zip Code </label>
                                    <input  class="form-control" type="text"  name="seller_code"
                                            value=""   placeholder="Enter Your Seller Code">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <div class="form-material">
                                    <label for="ColorInput" class="form-label">Seller Color</label>
                                    <input type="color" id="" name="seller_color" value="#563d7c" title="Choose your color">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <div class="form-material">
                                    <label for="material-error">Discount in percentage</label>
                                    <input  class="form-control" type="text"  name="discount"
                                            value=""   placeholder="Enter discount">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <div class="form-material">
                                    <label for="material-error">Commission Rate in percentage</label>
                                    <input  class="form-control" type="text"  name="commission_rate"
                                            value=""   placeholder="Enter Commission Rate On Each Sell">
                                </div>
                            </div>
                        </div>

                </div>
                        <div class="modal-footer">
                            <div class="block-content block-content-full text-right border-top">
                                <button type="submit" class="btn btn-sm btn-primary" >Approve</button>
                            </div>
                    </form>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
        <div class="d-flex justify-content-between mb-3">
            <h5>Customers</h5>
            <div>
                <a type="button" class="btn btn-info btn-lg" href="{{route('sync-customer')}}">Sync Customers</a>
                <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal">Add New Agent</button>
            </div>
        </div>
    @if(count($customers)> 0)
        <div class="row">
            <div class="col-lg-12 pl-3">


                <table class="table table-striped table-hover">
                    <thead class="border-0">
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Created At</th>
                        <th scope="col">Phone Number</th>
                        <th scope="col">Email</th>
                        <th scope="col">Seller Area</th>
                        <th scope="col">Zip Code</th>
                        <th scope="col">Seller Color</th>
                        <th scope="col">Discount in percentage</th>
                        <th scope="col">Commission</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($customers as $customer)
                    <tr>
    {{--                    <th scope="row">1</th>--}}
                        <td>{{$customer->first_name}}&nbsp;{{$customer->last_name}}</td>
                        <td>{{date_create($customer->created_at)->format('d m, Y h:i a')}}</td>
                        <td>{{$customer->phone_no}}</td>
                        <td>{{$customer->email}}</td>
                        <td>{{$customer->seller_area}}</td>
                        <td>{{$customer->seller_code}}</td>
                        <td>{{$customer->seller_color}}</td>
                        <td>{{$customer->discount}}</td>
                        <td>{{$customer->commission}}</td>

                    </tr>
                    @endforeach

                    </tbody>
                </table>

                <div style="float: right">
                    {{ $customers->links() }}
                </div>
            </div>

            </div>
        </div>
        @else
        <p class="text-center">No Customer Available.</p>
    @endif
    </div>
@endsection

