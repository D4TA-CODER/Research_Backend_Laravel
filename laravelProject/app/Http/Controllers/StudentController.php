<?php




namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class StudentController extends Controller
{
    public function login()
    {
        if (session('logged_in')) {
            return redirect('/schedule'); 
        }
        return view('login'); 
    }

    public function processLogin(Request $request)
    {
        $request->validate([
            'Email' => 'required|email',
            'Password' => [
                'required',
                'min:8',
                'regex:/^[A-Z]/', 
                'regex:/\d/'     
            ],
        ], [
            'Email.required' => 'Email is required.',
            'Email.email'    => 'Invalid email address format.',
        ]);

        $student = Student::where('emailAddress', $request->Email)->first();

        if (!$student) {
            return back()->withErrors(['Email' => 'No such user found.'])->withInput();
        }

        if ($student->password !== $request->Password) {
            return back()->withErrors(['Password' => 'Incorrect password!'])->withInput();
        }

        session([
            'logged_in' => true,
            'studentID' => $student->studentID,
            'firstName' => $student->firstName,
            'lastName'  => $student->lastName,
            'email'     => $student->emailAddress,
        ]);

        return redirect('/schedule');
    }
















    public function showRegister()
    {
        $genders = DB::table('gender')->get(); 
        $schools = DB::table('school')->get();
        $grades  = DB::table('formgradelevel')->get();
        $subjects = DB::table('subject')->get();

        return view('Registration', [
            'genders'  => $genders,
            'schools'  => $schools,
            'grades'   => $grades,
            'subjects' => $subjects,
            //'errors'   => [],
            'oldData'  => []
        ]);
    }



    public function store(Request $request)
    {
        $request->validate([
            'FirstName'     => ['required','regex:/^[A-Za-z]+(?:\'[A-Za-z]+)*$/'],
            'LastName'      => ['required','regex:/^[A-Za-z]+(?:\'[A-Za-z]+)*$/'],
            'DOB'           => ['required','date','before:tomorrow'],
            'Gender'        => 'required',
            'Email'         => ['required','email'],
            'HomeNumber'    => ['required','regex:/^\d{3}-\d{3}-\d{4}$/'],
            'MobileNumber'  => ['required','regex:/^\d{3}-\d{3}-\d{4}$/'],
            'StreetAdd1'    => ['required','regex:/^(?!.*--)(?!.*\.\.)[A-Za-z0-9.\- ]+$/'],
            'StreetAdd2'    => ['nullable','regex:/^(?!.*--)(?!.*\.\.)[A-Za-z0-9.\- ]+$/'],
            'City'          => ['required','regex:/^(?!.*--)(?!.*\.\.)[A-Za-z0-9.\- ]+$/'],
            'Parish'        => ['required','regex:/^(?!.*--)(?!.*\.\.)[A-Za-z0-9.\- ]+$/'],
            'ZIPcode'       => ['nullable','regex:/^\d{5}$/'],
            'PostalCode'    => ['nullable','regex:/^[A-Za-z][A-Za-z0-9][A-Za-z0-9][A-Za-z0-9]-\d[A-Za-z0-9][A-Za-z0-9]$/'],
            'SchoolID'      => 'required',
            'GradeLevelID'  => 'required',
            'Month'         => 'required',
            'Year'          => 'required',
            'Password'      => [
                'required',
                'min:8',
                'regex:/^[A-Z]/', 
                'regex:/\d/'     
            ],
            'Subjects'      => 'required|array', 
        ], [
            'FirstName.required'    => 'ERROR: Missing Information',
            'FirstName.regex'       => 'ERROR: Invalid First Name!',
            
            'LastName.required'     => 'ERROR: Missing Information',
            'LastName.regex'        => 'ERROR: Invalid Last Name!',
            
            'DOB.required'          => 'ERROR: Missing Date of Birth!',
            'DOB.date'              => 'ERROR: Invalid Date of Birth format!',
            'DOB.before'            => 'ERROR: Date of Birth cannot be in the future!',
            
            'Gender.required'       => 'ERROR: Missing Information',
            
            'Email.required'        => 'ERROR: Missing Information',
            'Email.email'           => 'ERROR: Invalid Email Address!',
            
            'HomeNumber.required'   => 'ERROR: Missing Information',
            'HomeNumber.regex'      => 'ERROR: Invalid Home Number!',
            
            'MobileNumber.required' => 'ERROR: Missing Information',
            'MobileNumber.regex'    => 'ERROR: Invalid Mobile Number!',
            
            'StreetAdd1.required'   => 'ERROR: Missing Information',
            'StreetAdd1.regex'      => 'ERROR: Invalid Street Address!',
            
            'StreetAdd2.regex'      => 'ERROR: Invalid Street Address!',
            
            'City.required'         => 'ERROR: Missing Information',
            'City.regex'            => 'ERROR: Invalid Entry!',
            
            'Parish.required'       => 'ERROR: Missing Information',
            'Parish.regex'          => 'ERROR: Invalid Entry!',
            
            'ZIPcode.regex'         => 'ERROR: Invalid ZIP Code!',
            
            'PostalCode.regex'      => 'ERROR: Invalid Postal Code!',
            
            'SchoolID.required'     => 'ERROR: Missing Information',
            'GradeLevelID.required' => 'ERROR: Missing Information',
            
            'Month.required'        => 'ERROR: Missing Information',
            'Year.required'         => 'ERROR: Missing Information',
            
            'Password.required'     => 'ERROR: Missing Information',
            'Password.min'          => 'ERROR: Invalid Password! (Must be ≥8 chars)',
            'Password.regex'        => 'ERROR: Invalid Password! (Must start capital & contain a digit)',

        ]);

        $subjects = array_filter($request->Subjects ?? []);
        if (count($subjects) < 1) {
            return back()
                ->withErrors(['Subjects' => 'ERROR: Please select at least one Subject!'])
                ->withInput();
        }

        $examRecord = $request->Month . ' ' . $request->Year;
        $studentID = DB::table('student')->insertGetId([
            'firstName'             => $request->FirstName,
            'lastName'              => $request->LastName,
            'dob'                   => $request->DOB,
            'genderID'              => $request->Gender,
            'emailAddress'          => $request->Email,
            'homeNumber'            => $request->HomeNumber,
            'mobileNumber'          => $request->MobileNumber,
            'streetAddress1'        => $request->StreetAdd1,
            'streetAddress2'        => $request->StreetAdd2 ?? '',
            'cityTownVillage'       => $request->City,
            'parishStateProvidence' => $request->Parish,
            'zipCode'               => $request->ZIPcode ?? '',
            'postalCode'            => $request->PostalCode ?? '',
            'schoolID'              => $request->SchoolID,
            'formGradeLevelID'      => $request->GradeLevelID,
            'examSittingRecord'     => $examRecord,
            'password'              => $request->Password,  
        ]);

        foreach ($subjects as $subID) {
            DB::table('studentsubjects')->insert([
                'studentID' => $studentID,
                'subjectID' => $subID
            ]);
        }
        return redirect('/login')->with('success', 'Registration successful! Please log in.');
    }



    public function logout()
    {
        session()->flush();  

        return redirect('/login');
    }






    public function profile()
    {
        if (!session('logged_in')) {
            return redirect('/login');
        }

        $studentID = session('studentID');

        $profile = DB::table('student as s')
            ->leftJoin('gender as g', 's.genderID', '=', 'g.genderID')
            ->leftJoin('school as sch', 's.schoolID', '=', 'sch.schoolID')
            ->leftJoin('formgradelevel as f', 's.formGradeLevelID', '=', 'f.formGradeLevelID')
            ->select(
                's.*',
                'g.gender as genderName',
                'sch.schoolName',
                'f.GradeLevel as gradeName'
            )
            ->where('s.studentID', $studentID)
            ->first();

        $subjects = DB::table('studentsubjects as ss')
            ->join('subject as sub', 'ss.subjectID', '=', 'sub.subjectID')
            ->where('ss.studentID', $studentID)
            ->pluck('sub.subjectName'); 

        if ($profile) {
            $profileArray = (array) $profile;
            $profileArray['subjects'] = $subjects->toArray();
        } else {
            $profileArray = [
                'firstName'  => '',
                'lastName'   => '',
                'dob'        => '',
                'genderName' => '',
                'emailAddress' => '',
                'homeNumber' => '',
                'mobileNumber' => '',
                'streetAddress1' => '',
                'streetAddress2' => '',
                'cityTownVillage' => '',
                'parishStateProvidence' => '',
                'zipCode' => '',
                'postalCode' => '',
                'schoolName' => '',
                'gradeName' => '',
                'examSittingRecord' => '',
                'subjects' => []
            ];
        }

        return view('profile', [
            'student' => $profileArray
        ]);
    }





    public function schedule()
    {
        if (!session('logged_in')) {
            return redirect('/login');
        }

        $user = [
            'firstName' => session('firstName'),
            'lastName'  => session('lastName'),
            'email'     => session('email'),
        ];
        $studentID = session('studentID');

        $schedule = DB::table('studentsubjects as ss')
            ->join('subject as s', 'ss.subjectID', '=', 's.subjectID')
            ->select('s.subjectName', 's.day', 's.time', 's.instructorName')
            ->where('ss.studentID', $studentID)
            ->get();

        return view('schedule', [
            'schedule' => $schedule,
            'user'     => $user
        ]);
    }










    public function updateProfileForm()
    {
        session_start();
        if (!session('logged_in')) {
            return redirect('/login');
        }

        $studentID = session('studentID');

        $currentData = DB::table('student as s')
            ->leftJoin('gender as g', 's.genderID', '=', 'g.genderID')
            ->leftJoin('school as sch', 's.schoolID', '=', 'sch.schoolID')
            ->leftJoin('formgradelevel as f', 's.formGradeLevelID', '=', 'f.formGradeLevelID')
            ->select(
                's.*',
                'g.gender as genderName',
                'sch.schoolName',
                'f.GradeLevel as gradeName'
            )
            ->where('s.studentID', $studentID)
            ->first();

        $genders  = DB::table('gender')->get();
        $schools  = DB::table('school')->get();
        $grades   = DB::table('formgradelevel')->get();
        $subjects = DB::table('subject')->get();

        $currentArray = $currentData ? (array)$currentData : [];

        return view('UpdateProfile', [
            'currentData' => $currentArray,
            'genders'     => $genders,
            'schools'     => $schools,
            'grades'      => $grades,
            'subjects'    => $subjects,
            'errors'      => [],
            'oldData'     => []
        ]);
    }



    public function processUpdateProfile(Request $request)
    {
        if (!session('logged_in')) {
            return redirect('/login');
        }

        $studentID = session('studentID');

        $errors = $this->validateUpdateData($request->all());

        if (!empty($errors)) {
            $currentData = DB::table('student')->where('studentID', $studentID)->first();

            $genders  = DB::table('gender')->get();
            $schools  = DB::table('school')->get();
            $grades   = DB::table('formgradelevel')->get();
            $subjects = DB::table('subject')->get();

            return view('UpdateProfile', [
                'currentData' => (array)$currentData,
                'genders'     => $genders,
                'schools'     => $schools,
                'grades'      => $grades,
                'subjects'    => $subjects,
                'errors'      => $errors,
                'oldData'     => $request->all()
            ]);
        }

        $updateData = [];
        if (!empty($request->FirstName)) {
            $updateData['firstName'] = $request->FirstName;
        }
        if (!empty($request->LastName)) {
            $updateData['lastName'] = $request->LastName;
        }
        if (!empty($request->DOB)) {
            $updateData['dob'] = $request->DOB;
        }
        if (!empty($request->Gender)) {
            $updateData['genderID'] = $request->Gender;
        }
        if (!empty($request->Email)) {
            $updateData['emailAddress'] = $request->Email;
        }
        if (!empty($request->HomeNumber)) {
            $updateData['homeNumber'] = $request->HomeNumber;
        }
        if (!empty($request->MobileNumber)) {
            $updateData['mobileNumber'] = $request->MobileNumber;
        }
        if (!empty($request->StreetAdd1)) {
            $updateData['streetAddress1'] = $request->StreetAdd1;
        }
        if (!empty($request->StreetAdd2)) {
            $updateData['streetAddress2'] = $request->StreetAdd2;
        }
        if (!empty($request->City)) {
            $updateData['cityTownVillage'] = $request->City;
        }
        if (!empty($request->Parish)) {
            $updateData['parishStateProvidence'] = $request->Parish;
        }
        if (!empty($request->ZIPcode)) {
            $updateData['zipCode'] = $request->ZIPcode;
        }
        if (!empty($request->PostalCode)) {
            $updateData['postalCode'] = $request->PostalCode;
        }
        if (!empty($request->SchoolID)) {
            $updateData['schoolID'] = $request->SchoolID;
        }
        if (!empty($request->GradeLevelID)) {
            $updateData['formGradeLevelID'] = $request->GradeLevelID;
        }
        if (!empty($request->Month) || !empty($request->Year)) {
            $updateData['examSittingRecord'] = trim(($request->Month ?? '') . ' ' . ($request->Year ?? ''));
        }
        if (!empty($request->Password)) {
            $updateData['password'] = $request->Password;
        }

        if (!empty($updateData)) {
            DB::table('student')->where('studentID', $studentID)->update($updateData);
        }

        if (!empty($request->Subjects)) {
            DB::table('studentsubjects')->where('studentID', $studentID)->delete();

            $filteredSubs = array_filter($request->Subjects);
            $uniqueSubs   = array_unique($filteredSubs);
            foreach ($uniqueSubs as $subID) {
                DB::table('studentsubjects')->insert([
                    'studentID' => $studentID,
                    'subjectID' => $subID
                ]);
            }
        }

        return redirect('/profile')->with('success','Profile updated successfully!');
    }



    private function validateUpdateData($data)
    {
        $errors = [];

        if (!empty($data['FirstName'])) {
            if (!preg_match("/^[A-Za-z]+(?:'[A-Za-z]+)*$/", $data['FirstName'])) {
                $errors['FirstName'] = "Invalid First Name!";
            }
        }
        if (!empty($data['LastName'])) {
            if (!preg_match("/^[A-Za-z]+(?:'[A-Za-z]+)*$/", $data['LastName'])) {
                $errors['LastName'] = "Invalid Last Name!";
            }
        }

        if (!empty($data['DOB'])) {
            $timestamp = strtotime($data['DOB']);
            if ($timestamp === false) {
                $errors['DOB'] = "Invalid Date of Birth format!";
            } else if ($timestamp > time()) {
                $errors['DOB'] = "Date of Birth cannot be in the future!";
            }
        }

        if (!empty($data['Email'])) {
            if (!filter_var($data['Email'], FILTER_VALIDATE_EMAIL)) {
                $errors['Email'] = "Invalid Email Address!";
            }
        }

        if (!empty($data['HomeNumber'])) {
            if (!preg_match("/^\d{3}-\d{3}-\d{4}$/", $data['HomeNumber'])) {
                $errors['HomeNumber'] = "Invalid Home Number! (Use XXX-XXX-XXXX)";
            }
        }
        if (!empty($data['MobileNumber'])) {
            if (!preg_match("/^\d{3}-\d{3}-\d{4}$/", $data['MobileNumber'])) {
                $errors['MobileNumber'] = "Invalid Mobile Number! (Use XXX-XXX-XXXX)";
            }
        }

        $addrPattern = "/^(?!.*--)(?!.*\.\.)[A-Za-z0-9.\- ]+$/";

        if (!empty($data['StreetAdd1']) && !preg_match($addrPattern, $data['StreetAdd1'])) {
            $errors['StreetAdd1'] = "Invalid Street Address 1!";
        }
        if (!empty($data['StreetAdd2']) && !preg_match($addrPattern, $data['StreetAdd2'])) {
            $errors['StreetAdd2'] = "Invalid Street Address 2!";
        }
        if (!empty($data['City']) && !preg_match($addrPattern, $data['City'])) {
            $errors['City'] = "Invalid City/Town/Village!";
        }
        if (!empty($data['Parish']) && !preg_match($addrPattern, $data['Parish'])) {
            $errors['Parish'] = "Invalid Parish/State/Province!";
        }

        if (!empty($data['ZIPcode'])) {
            if (!preg_match("/^\d{5}$/", $data['ZIPcode'])) {
                $errors['ZIPcode'] = "Invalid ZIP Code! (Must be 5 digits)";
            }
        }

        if (!empty($data['PostalCode'])) {
            if (!preg_match("/^[A-Za-z][A-Za-z0-9][A-Za-z0-9][A-Za-z0-9]-\d[A-Za-z0-9][A-Za-z0-9]$/", $data['PostalCode'])) {
                $errors['PostalCode'] = "Invalid Postal Code! (Must be like ABC1-123)";
            }
        }

        $month = $data['Month'] ?? '';
        $year  = $data['Year']  ?? '';
        if (!empty($month) || !empty($year)) {
            if (empty($month) || empty($year)) {
                $errors['Month'] = "Please select both Month and Year.";
                $errors['Year']  = "Both fields are required.";
            }
        }

        if (!empty($data['Password'])) {
            $pass = $data['Password'];
            if (strlen($pass) < 8 || 
                !preg_match('/^[A-Z]/', $pass) || 
                !preg_match('/\d/', $pass)) {
                $errors['Password'] = "Invalid Password! (≥8 chars, capital first letter, at least 1 digit)";
            }
        }

        if (!empty($data['Subjects'])) {
            $nonEmptySubs = array_filter($data['Subjects']);
            if (count($nonEmptySubs) == 0) {
                $errors['Subjects'] = "Please select at least one Subject";
            }
        }

        return $errors;
    }







    public function confirmDelete()
    {
        if (!session('logged_in')) {
            return redirect('/login');
        }
        return view('ConfirmDelete'); 
    }



    public function deleteProfileStep2()
    {
        if (!session('logged_in')) {
            return redirect('/login');
        }
        return view('DeleteProfileCheck', ['error' => '']);
    }

    

    public function processDeleteProfile(Request $request)
    {
        if (!session('logged_in')) {
            return redirect('/login');
        }

        $inputEmail    = $request->Email    ?? '';
        $inputPassword = $request->Password ?? '';

        $student = DB::table('student')->where('emailAddress', $inputEmail)->first();
        if (!$student || $student->password !== $inputPassword) {
            return view('DeleteProfileCheck', [
                'error' => 'Invalid email or password.'
            ]);
        }

        $studentID = $student->studentID;
        DB::table('studentsubjects')->where('studentID', $studentID)->delete();
        DB::table('student')->where('studentID', $studentID)->delete();

        session()->flush();

        return redirect('/login');
    }








}






















