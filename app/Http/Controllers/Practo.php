<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Str;
use Session;
use App\test;
use App\lab;
use App\tests_lab;
use App\User;
use App\booking;
use App\admin;
use Crypt;

class Practo extends Controller
{
    //
    function new_booking_page()
    {
        $tests = DB::select("select id, test_name from tests order by id");
        $labs = DB::select("select id, lab_name from labs order by id");
        return view('/new booking page', ['tests' => $tests, 'labs' => $labs]);
    }
    function new_booking(Request $req)
    {
        $req->validate([
            "name" => "required",
            "contact_number" => "required|digits:10",
            "test" => "required",
            "prescription" => "required|image|mimes:jpeg,png,jpg,gif,svg|max:2048",
            "lab" => "required",
        ]);
        $data = tests_lab::where(['test_id' => $req->input('test'), 'lab_id' => $req->input('lab')])->get();
        if ($data->isEmpty()) {
            $lab = lab::find($req->input('lab'))->lab_name;
            $test = test::find($req->input('test'))->test_name;
            Session::flash('status', "$lab does not provide $test");
        } else {
            $data = User::where(['name' => $req->input('name'), 'contact_number' => $req->input('contact_number')])->get();
            if ($data->isEmpty()) {
                $user = new User;
                $user->name = $req->input('name');
                $user->contact_number = $req->input('contact_number');
                $user->save();
            }
            $user = User::where(['name' => $req->input('name'), 'contact_number' => $req->input('contact_number')])->get();
            $booking = new booking;
            $booking->user_id = $user[0]->id;
            $booking->test_id = $req->input('test');
            $booking->lab_id = $req->input('lab');
            $file = $req->file('prescription');
            $booking->prescription = $file;
            $extension = $file->getClientOriginalExtension();
            $filename = $booking->user_id . '_' . time() . '.' . $extension;
            $file->move('uploads', $filename);
            $booking->file_name = $filename;
            $booking->save();
            Session::put('booking_id', $booking->id);
            Session::put('user_id', $booking->user_id);
            Session::put('user_name', $req->input('name'));
            Session::put('contact_number', $req->input('contact_number'));
            return redirect('/booking details');
        }
        return redirect('/new booking page')->withInput();
    }
    function booking_details(Request $req)
    {
        $req->validate([
            "name" => "required",
            "contact_number" => "required|digits:10",
            "age" => "required|numeric|max:100",
            "email" => "required|email",
            "gender" => "required|in:male,female,other",
            "date" => "required|date|after:today",
            "timeslot" => "required",
            "details" => "required|max:209",
        ]);
        $user = User::find(Session::get('user_id'));
        $user->age = $req->input('age');
        $user->email = $req->input('email');
        $user->gender = $req->input('gender');
        $user->address = $req->input('details');
        $user->save();
        $booking = booking::find(Session::get('booking_id'));
        $booking->selected_date = $req->input('date');
        $booking->timeslot = $req->input('timeslot');
        $booking->save();
        Session::flash('status', 'Booking Confirmed!');
        return redirect('/');
    }
    function login(Request $req)
    {
        $req->validate([
            "admin_name" => "required",
            "password" => "required",
        ]);
        $data = admin::where(['admin_name' => $req->input('admin_name')])->get();
        if (!$data->isEmpty() and Crypt::decrypt($data[0]->password) == $req->input('password')) {
            Session::put('admin', $req->input('admin_name'));
            return redirect('/');
        }
        Session::flash('errors', 'Invalid Credentials! Please try again.');
        return redirect('/');
    }
    function bookings_list()
    {
        $items = DB::select("select bookings.id, bookings.file_name, bookings.prescription, bookings.selected_date, bookings.timeslot, users.name, users.email,
                        users.contact_number, users.age, users.gender, tests.test_name, labs.lab_name
                        from users
                        join bookings on users.id = bookings.user_id
                        join tests on bookings.test_id = tests.id
                        join labs on bookings.lab_id = labs.id
                        order by bookings.id");
        $data = $this->paginate($items);
        return view('/bookings list', compact('data'));
    }
    public function paginate($items, $perPage = 7, $page = null)
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
    }
    function logout()
    {
        Session::forget('admin');
        return redirect('/');
    }
    function delete($id)
    {
        $booking = booking::find($id);
        if ($booking) {
            if (is_file(public_path('uploads/' . $booking->file_name))) {
                unlink('uploads/' . $booking->file_name);
            }
            $booking->delete();
            Session::flash('delete', 'Deleted Successfully!');
        }
        return redirect('/bookings list');
    }
    function database()
    {
        $tests = DB::select("select id, test_name from tests order by id");
        $labs = DB::select("select id, lab_name from labs order by id");
        $associations = DB::select("select tests_labs.test_id, tests_labs.lab_id, tests.test_name, labs.lab_name
                                from tests_labs
                                join tests on tests_labs.test_id = tests.id
                                join labs on tests_labs.lab_id = labs.id
                                order by tests_labs.lab_id, tests_labs.test_id");
        return view('/database', compact('tests', 'labs', 'associations'));
    }
    function add_tests(Request $req)
    {
        $req->validate([
            "test_name" => "required"
        ]);
        $test_name = $req->input('test_name');
        $data = test::where(['test_name' => $test_name])->get();
        if ($data->isEmpty()) {
            $test = new test;
            $test->test_name = $test_name;
            $test->save();
            Session::flash('tests_db', 'Test Added Successfully!');
            return redirect('/database');
        }
        Session::flash('test', 'Test already exists!');
        return redirect('/database')->withInput();
    }
    function add_labs(Request $req)
    {
        $req->validate([
            "lab_name" => "required"
        ]);
        $lab_name = $req->input('lab_name');
        $data = lab::where(['lab_name' => $lab_name])->get();
        if ($data->isEmpty()) {
            $lab = new lab;
            $lab->lab_name = $req->input('lab_name');
            $lab->save();
            Session::flash('labs_db', 'Lab Added Successfully!');
            return redirect('/database');
        }
        Session::flash('lab', 'Lab already exists!');
        return redirect('/database')->withInput();
    }
    function add_associations(Request $req)
    {
        $req->validate([
            "test_id" => "required|",
            "lab_id" => "required|numeric"
        ]);
        $test_ids = explode(",", $req->input('test_id'));
        $lab_id = $req->input('lab_id');
        foreach ($test_ids as $i) {
            $test_id = (int)$i;
            $data = tests_lab::where(['test_id' => $test_id, 'lab_id' => $lab_id])->get();
            if (test::find($test_id) and lab::find($lab_id) and $data->isEmpty()) {
                DB::select("insert into tests_labs(test_id, lab_id) values($test_id, $lab_id)");
            } else if (!$data->isEmpty()) {
                Session::flash('association', "Test ID: $test_id and Lab ID: $lab_id already exists!");
                return redirect('/database')->withInput();
            } else {
                Session::flash('association', "Test ID: $test_id and Lab ID: $lab_id does not exist!");
                return redirect('/database')->withInput();
            }
        }
        Session::flash('associations_db', 'Associations Added Successfully!');
        return redirect('/database');
    }
    function delete_test($id)
    {
        $test = test::find($id);
        if ($test) {
            $test->delete();
            Session::flash('tests_db', 'Test Deleted Successfully!');
        }
        return redirect('/database');
    }
    function delete_lab($id)
    {
        $lab = lab::find($id);
        if ($lab) {
            $lab->delete();
            Session::flash('labs_db', 'Lab Deleted Successfully!');
        }
        return redirect('/database');
    }
    function delete_association($test_id, $lab_id)
    {
        $data = DB::select("select * from tests_labs where test_id = $test_id and lab_id = $lab_id");
        if ($data) {
            DB::select("delete from tests_labs where test_id = $test_id and lab_id = $lab_id");
            Session::flash('associations_db', 'Association Deleted Successfully!');
        }
        return redirect('/database');
    }
    function edit($id)
    {
        $tests = DB::select("select id, test_name from tests order by id");
        $labs = DB::select("select id, lab_name from labs order by id");
        $data = DB::select("select B.user_id, U.name, U.email, U.contact_number, U.age, U.gender, U.address, B.id, T.test_name, L.lab_name,
                            B.selected_date, B.timeslot  
                            from bookings B
                            join users U on B.user_id = U.id
                            join tests T on B.test_id = T.id
                            join labs L on B.lab_id = L.id");
        return view('/edit', ['tests' => $tests, 'labs' => $labs, 'data' => $data]);
    }
    function users_details(Request $req)
    {
        $req->validate([
            "name" => "required",
            "contact_number" => "required|digits:10",
            "age" => "required|numeric|max:100",
            "email" => "required|email",
            "gender" => "required|in:male,female,other",
            "details" => "required|max:209",
        ]);
        $user = user::find($req->input('user_id'));
        $user->name = $req->input('name');
        $user->contact_number = $req->input('contact_number');
        $user->age = $req->input('age');
        $user->email = $req->input('email');
        $user->gender = $req->input('gender');
        $user->address = $req->input('details');
        $user->save();
        Session::flash('edit', 'Data Changed Successfully!');
        return redirect('/bookings list');
    }
    function bookings_details(Request $req)
    {
        $req->validate([
            "test" => "required",
            "lab" => "required",
            "prescription" => "max:2048",
            "date" => "required|date|after:today",
            "timeslot" => "required",
        ]);
        $booking = booking::find($req->input('booking_id'));
        $booking->test_id = $req->input('test');
        $booking->lab_id = $req->input('lab');
        if ($req->file('prescription')) {
            $booking->prescription = $req->file('prescription');
            $file = $req->file('prescription');
            $extension = $file->getClientOriginalExtension();
            $filename = $booking->user_id . '_' . time() . '.' . $extension;
            $file->move('uploads', $filename);
            $booking->file_name = $filename;
        }
        $booking->selected_date = $req->input('date');
        $booking->timeslot = $req->input('timeslot');
        $booking->save();
        Session::flash('edit', 'Data Changed Successfully!');
        return redirect('/bookings list');
    }
    function edit_test(Request $req)
    {
        $req->validate([
            "test" => "required",
        ]);
        $test_id = $req->input('test_id');
        $test_name = $req->input('test');
        $data = DB::select("select test_name from tests where id != $test_id and test_name = '$test_name'");
        if ($data) {
            Session::flash('tests_error', 'Test Alredy Exists!');
            return redirect('/database');
        }
        $test = test::find($test_id);
        $test->test_name = $test_name;
        $test->save();
        Session::forget('test_id');
        Session::flash('tests_db', 'Data Changed Successfully!');
        return redirect('/database');
    }
    function edit_lab(Request $req)
    {
        $req->validate([
            "lab" => "required",
        ]);
        $lab_id = $req->input('lab_id');
        $lab_name = $req->input('lab');
        $data = DB::select("select lab_name from labs where id != $lab_id and lab_name = '$lab_name'");
        if ($data) {
            Session::flash('labs_error', 'Lab Alredy Exists!');
            return redirect('/database');
        }
        $lab = lab::find($lab_id);
        $lab->lab_name = $lab_name;
        $lab->save();
        Session::forget('lab_id');
        Session::flash('labs_db', 'Data Changed Successfully!');
        return redirect('/database');
    }
}
