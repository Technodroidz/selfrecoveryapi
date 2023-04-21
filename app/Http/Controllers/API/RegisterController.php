<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Query;
use App\Models\Question;
use App\Models\Answer;



class RegisterController extends BaseController
{
  
     private function getRandomString($length) {
       $characters = '0123456789';
       $string = '';

       for ($i = 0; $i < $length; $i++) {
         $string .= $characters[mt_rand(0, strlen($characters) - 1)];
     }

      return $string;
   }
   
  
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            'email' => 'required|email',
            'password' => 'required'
           
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $admin = Admin::where('email', $request->email)->first();
        if($admin){
            if($admin->password == $request->password){
                $success['admin_id'] =  $admin->id;
                $success['email'] =  $admin->email;
       
                return $this->sendResponse($success, 'Admin login successfully.');

            }else{
                return $this->sendError('Unauthorised.', ['error'=>'Wrong Password']); 
            }

        }else{
            return $this->sendError('Unauthorised.', ['error'=>'Email does not exist']);  
        }
        
    }

    
    public function addQuiz(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'quiz_name' => 'required',
          //  'quiz_image' => 'required'
           
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $quiz_check = Quiz::where('quiz_name', $request->quiz_name)->first();
        if($quiz_check){
            return $this->sendError('Unauthorised.', ['error'=>'Quiz name already exist']); 
        }

        $input = $request->all();
       if(!empty($request->quiz_image)){ 
        $destinationPath = base_path('images');
        $quiz_image = time().'.'.$request->quiz_image->getClientOriginalExtension();

        $request->quiz_image->move($destinationPath, $quiz_image); 
        $input['quiz_image'] = $quiz_image;
       }
        $success = Quiz::create($input);
        return $this->sendResponse($success, 'Quiz added successfully.');
    }

    public function updateQuiz(Request $request){
        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required',
            'quiz_name' => 'required'
           
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $data = [
            'quiz_name' => $request->quiz_name
        ];
        if(!empty($request->quiz_image)){

            $destinationPath = base_path('images');
            $quiz_image = time().'.'.$request->quiz_image->getClientOriginalExtension();
            
            $request->quiz_image->move($destinationPath, $quiz_image); 
            $data['quiz_image'] = $quiz_image;
        } 
        $res = Quiz::where('id', $request->quiz_id)->update($data);
        $success = quiz::where('id', $request->quiz_id)->first();
        return $this->sendResponse($success, 'Quiz updated successfully.');
    }

    public function deleteQuiz(Request $request){
        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required',
           
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $data = [
            'status' => '0'
        ];
        $res = Quiz::where('id', $request->quiz_id)->update($data);
        $success = quiz::where('id', $request->quiz_id)->first();
        return $this->sendResponse($success, 'Quiz deleted successfully.');  
    }

    public function fetchQuiz(Request $request){
       // $where = ['status' => '1'];
       // $success = Quiz::where($where)->get()->toArray();
        $success = Quiz::orderBy('id','desc')->get()->toArray();
        return $this->sendResponse($success, 'Quiz fetched successfully.');
    }

    public function addUser(Request $request){
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'group_name' => 'required',
            'phone' => 'required',
            'email' => 'required|email|unique:users',
           // 'categories_of_interest' => 'required',
           
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $input = $request->all();
        $input['password'] = $request->first_name.getRandomString(4);
        $success = User::create($input);
        return $this->sendResponse($success, 'User registered successfully.');
    }

    public function userLogin(Request $request){
        $validator = Validator::make($request->all(), [
            
            'email' => 'required|email',
            'password' => 'required'
           
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $user = User::where('email', $request->email)->first();
        if($user){
            if($user->password == $request->password){
                $success['user_id'] =  $user->id;
                $success['email'] =  $user->email;
                $success['first_name'] =  $user->first_name;
                $success['last_name'] =  $user->last_name;
                $success['group_name'] =  $user->group_name;
                $success['phone'] =  $user->phone;
                $success['categories_of_interest'] =  $user->categories_of_interest;
                $success['address'] =  $user->address;
                $success['description'] =  $user->description;
       
                return $this->sendResponse($success, 'User login successfully.');

            }else{
                return $this->sendError('Unauthorised.', ['error'=>'Wrong Password']); 
            }

        }else{
            return $this->sendError('Unauthorised.', ['error'=>'Email does not exist']);  
        }
    }

    public function fetchUserQuiz(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $success = Quiz::where('user_id', $request->user_id)->orderBy('id','desc')->get()->toArray();
        return $this->sendResponse($success, 'Users quiz fetched successfully.');
    }

    public function fetchAllUsers(Request $request){
       // $success = User::orderBy('id','desc')->get()->toArray();
        $success = DB::table('users')
            ->select('users.*', DB::raw('COUNT(quizzes.user_id) AS quiz_count'))
            ->join('quizzes', 'quizzes.user_id', '=', 'users.id')
            ->groupBy('users.id')
            ->get()->toArray();
        return $this->sendResponse($success, 'Users and their total quiz fetched successfully.');
    }

    public function submitQuery(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'phone' => 'required',
            'message' => 'required',
        ]); 

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $input = $request->all();
        $success = Query::create($input);
        return $this->sendResponse($success, 'Query submitted successfully.');
    }

    public function addQuestions(Request $request){
        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required',
            'question' => 'required',
            'answer' => 'required',
            'input_type' => 'required',
        ]); 

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        } 

        $data = [
            'quiz_id' => $request->quiz_id,
            'question' => $request->question,
            'answer' => $request->answer,
            'input_type' => $request->input_type
        ];
        $success = Question::create($data);
        if($success){
            $answersdata = [
                'question_id' => $success->id,
                'answer' => $request->answervalue,
                'score' => $request->answerscore,
            ];
          $result = Answer::create($answersdata);  
        }
        return $this->sendResponse($success, 'Question submitted successfully.');
    }
}