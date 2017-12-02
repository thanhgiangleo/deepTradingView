<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class HomeController extends Controller
{
//    private $db;
    public function __construct(Socialite $socialite)
    {
        $this->socialite = $socialite;
        parent::__construct();
    }

    public function index()
    {
        $users = DB::select('SELECT * from ' . $this->User_Type);
        var_dump($users); die();
//        if (!$this->db) {
//            echo "ERROR : CANNOT OPEN DB\n";
//        }
//        else {
//            $result = pg_query($this->db, "SELECT * from users.user_type");
//            if (!$result) {
//                echo "An error occurred.\n";
//                exit;
//            }
//
//            while ($row = pg_fetch_row($result)) {
//                echo "Type: $row[0]  Name: $row[1]";
//                echo "<br />\n";
//            }
//        }
//        return view('login');
    }

    public function redirectToProvider()
    {

    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return Response
     */
    public function facebook(Request $request)
    {
        if (!$request->has('code')) {
            return Socialite::driver('facebook')->redirect();
        }

        $user = Socialite::driver('facebook')->stateless()->user();

        $email = $user->email;
        $password = $user->token;

        if($this->isExistEmail($email))
        {
            $user = $this->getUserByEmail($email);
            echo "dang nhap thanh cong";
        }
        else
        {
            $this->insertUser($email, $password);
            echo "dang ki thanh cong";
        }
    }

    public function login()
    {
        return view('login');
    }

    public function loginAction(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::where([
            ['email', '=', $email],
            ['password', '=', md5($password)],])->first();

        return isset($user) ? true : false;
    }

    public function registerAction(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');
        $this->insertUser($email, md5($password));

        return 1;
    }

    public function payment()
    {
        return view('payment');
    }

    public function isExistEmail($email)
    {
        $user = $this->getUserByEmail($email);
        return isset($user) ? 1 : 0;
    }

    function get_client_ip() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    function insertUser($email, $password)
    {
        $user = new User();

        $user->email = $email;
        $user->password = $password;
        $user->login_ip = $this->get_client_ip();
        $user->created_at = Carbon::now();
        $user->updated_at = Carbon::now();
        $user->expired_date = Carbon::now()->addDays(7);

        $user->save();
    }

    function getUserByEmail($email)
    {
        return User::where('email', '=', $email)->first();
    }
}
