<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
        <script src="https://kit.fontawesome.com/258f31346d.js" crossorigin="anonymous"></script>
        <link rel = "icon" href =  
        "https://pbs.twimg.com/profile_images/849341342224351238/cuaVqp5x_400x400.jpg"
        type="image/x-icon">
        <style>
            body {
                background-color: #ffffff;
                margin: 0;
                padding: 0;
            }

            .navbar-dark {
                background-color: #000000;
                padding: 0;
            }

            .navbar-brand {
                margin-left: 20px;
                font-size: 20px;
            }

            .nav-link {
                font-size: 15px;
            }

            #navigation .active {
                background-color: #ffffff;
                color: #000000;
            }

            #navigation .nav-link:hover {
                background-color: #000080;
                color: #ffffff;
            }

            .container {
                margin-top: 50px;
            }

            table {
                color: #ffffff;
                text-align: center;
            }

            tr:nth-child(odd) {
                background-color: rgba(0, 0, 128, 0.9);
            }

            tr:nth-child(even) {
                background-color: rgba(0, 0, 128, 0.5);
            }

            tr:hover {
                background-color: rgba(255, 255, 0, 0.9);
                color: #000000;
            }

            i:hover {
                box-shadow: 5px 5px 10px #5f5f5f;
                transform:scale(1.2,1.2);
                -webkit-transform:scale(1.2,1.2);
                -moz-transform:scale(1.2,1.2);
            }
        </style>
    </head>
    <header>
        <nav class="navbar fixed-top navbar-expand-md navbar-dark" id="navigation">
            <a class="navbar-brand" href="#">
                <img src="{{ URL::asset('images/logo.png')}}" height=30 width=30>
                Practo
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-content" aria-controls="navbar-content" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbar-content">
                <ul class="navbar-nav ml-auto" align="center">
                    <li class="nav-item">
                        <a class="nav-link" href="/#home" style="padding-left: 10px;">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/#about" style="padding-left: 10px;">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/#contact-us" style="padding-left: 10px;">Contact Us</a>
                    </li>
                    @if(Session::get('admin'))
                    <li class="nav-item">
                        <a class="nav-link active" href="/bookings list" style="padding-left: 10px;">Bookings List</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/database" style="padding-left: 10px;">Database</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/logout" style="padding-left: 10px;">Logout</a>
                    </li> 
                    @else
                    <li class="nav-item">
                        <a class="nav-link" href="/admin login" style="padding-left: 10px;">Admin Login</a>
                    </li>
                    @endif
                </ul>
            </div>
        </nav>
    </header>
    <body>
        <div class="container">
            @if(Session::get('delete'))
                <div class="row justify-content-around">
                    <div class="col-11 alert alert-success alert-dismissible fade show" role="alert">
                        <strong>{{Session::get('delete')}}</strong>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            @endif
            @if(Session::get('edit'))
                <div class="row justify-content-around">
                    <div class="col-11 alert alert-success alert-dismissible fade show" role="alert">
                        <strong>{{Session::get('edit')}}</strong>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            @endif
        </div>
        <div class="container" id="booking-list">
            <table class="table table-responsive">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">Booking ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Contact Number</th>
                        <th scope="col">Email</th>
                        <th scope="col">Age</th>
                        <th scope="col">Gender</th>
                        <th scope="col">Prescription</th>
                        <th scope="col">Test</th>
                        <th scope="col">Lab</th>
                        <th scope="col">Date</th>
                        <th scope="col">Timeslot</th>
                        <th scope="col">Operation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $item)
                    <tr>
                        <td>{{$item->id}}</td>
                        <td>{{$item->name}}</td>
                        <td>{{$item->contact_number}}</td>
                        <td>{{$item->email}}</td>
                        <td>{{$item->age}}</td>
                        <td>{{$item->gender}}</td>
                        <td><a id="link" target="_blank" href="uploads/{{$item->file_name}}">{{$item->file_name}}</a></td>
                        <td>{{$item->test_name}}</td>
                        <td>{{$item->lab_name}}</td>
                        <td>{{$item->selected_date}}</td>
                        <td>{{$item->timeslot}}</td>
                        <td>
                            <a href="/edit/{{$item->id}}"><i class="fa fa-edit" style="color: black;"></i></a>
                            <a href="/delete/{{$item->id}}"><i class="fa fa-trash" style="color: red;"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="row justify-content-around">
                {{$data->links()}}
            </div>
        </div>
    </body>
    <script>
        $('.navbar-nav>li>a').on('click', function(){
            $('.navbar-collapse').collapse('hide');
        });
        $(document).click(function (event) {
            $('.navbar-collapse').collapse('hide');
        });
    </script>
</html>